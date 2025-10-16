<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use Andersundsehr\Storybook\Dto\ViewHelperName;
use Andersundsehr\Storybook\Service\ConfigService;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;

use function array_keys;
use function class_exists;
use function enum_exists;
use function file_exists;
use function in_array;
use function preg_replace;
use function str_ends_with;

final readonly class TransformersFactory
{
    public const array DEFAULT_SUPPORTED_TYPES = [
        'bool',
        'boolean',
        'int',
        'integer',
        'float',
        'double',
        'string',
        DateTime::class,
        DateTimeImmutable::class,
        DateTimeInterface::class,
    ];

    public function __construct(private TypeTransformers $typeTransformers, private TransformerFactory $transformerFactory, private ConfigService $configService) {}

    public function get(
        ComponentTemplateResolverInterface&ComponentDefinitionProviderInterface $collection,
        ViewHelperName $viewHelperName
    ): Transformers {
        $templateName = $collection->resolveTemplateName($viewHelperName->name);
        $fileName = $collection->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $templateName);
        $pdaFileName = preg_replace('/\.html$/', '.transformer.php', (string)$fileName) ?:
            throw new RuntimeException(
                'Could not resolve the transformer file for the view helper "' . $viewHelperName->fullName . '"',
                5992417357,
            );

        $argumentTransformers = $this->loadArgumentTransformer($pdaFileName, $viewHelperName);

        $argumentDefinitions = $collection->getComponentDefinition($viewHelperName->name)->getArgumentDefinitions();
        foreach (array_keys($argumentTransformers->arguments) as $name) {
            if (!isset($argumentDefinitions[$name])) {
                throw new RuntimeException(
                    'The transformer for argument "' . $name . '" is not present in the component. remove it from ' . $pdaFileName . ' or add it to the component.',
                    1153552856
                );
            }
        }

        $excludedArguments = $this->configService->getExcludedArguments();
        $transformers = [];

        foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
            if (in_array($argumentName, $excludedArguments, true)) {
                continue;
            }

            if (isset($argumentTransformers->arguments[$argumentName])) {
                $transformers[$argumentName] = $this->transformerFactory->fromCallable(
                    $argumentTransformers->arguments[$argumentName],
                    $pdaFileName . ':' . $argumentName
                );
                continue;
            }

            $type = $argumentDefinition->getType();
            if (in_array($type, self::DEFAULT_SUPPORTED_TYPES, true)) {
                continue;
            }

            if (enum_exists($type)) {
                continue;
            }

            if (!$this->typeTransformers->has($type)) {
                throw new RuntimeException(
                    'component requires a transformer for argument "' . $argumentName . '" of type "' . $type . '", but no transformer is defined in the file ' . $pdaFileName . ". \n"
                        . "Please add a transformer for this argument, \n"
                        . "or add one for that type, \n"
                        . "or remove the argument from the component definition.",
                    6790927084
                );
            }

            $transformers[$argumentName] = $this->typeTransformers->get($type);
        }

        $result = new Transformers(arguments: $transformers, fromFile: $pdaFileName);

        $this->validateReturnType($collection->getComponentDefinition($viewHelperName->name), $result);

        return $result;
    }

    private function validateReturnType(ComponentDefinition $componentDefinition, Transformers $transformers): void
    {
        $argumentDefinitions = $componentDefinition->getArgumentDefinitions();
        foreach (array_keys($transformers->arguments) as $argumentName) {
            $resultType = $transformers->arguments[$argumentName]->returnType;
            $targetType = $argumentDefinitions[$argumentName]->getType();
            if ($targetType === 'mixed') {
                // If the target type is mixed, we don't need to validate the result type
                continue;
            }

            if ($resultType !== $targetType) {
                if (str_ends_with($targetType, '[]') && $resultType === 'array') {
                    continue;
                }

                $targetIsClass = class_exists($targetType) || interface_exists($targetType);
                $resultIsClass = class_exists($resultType) || interface_exists($resultType);
                // If the target type is a class, interface or enum, we can check if the result type is a subclass or implementation
                if ($targetIsClass && $resultIsClass && is_a($resultType, $targetType, true)) {
                    continue;
                }

                // TODO use better type comparison
                throw new RuntimeException(
                    '🥺🙏 please report this!!! https://github.com/andersundsehr/storybook/issues The transformer for argument "' . $argumentName . '" returns a value of type "' . $resultType . '" but the component expects a value of type "' . $targetType . '". ' .
                        'Please adjust the transformer or the component definition.',
                    4128088840
                );
            }
        }
    }

    private function loadArgumentTransformer(mixed $pdaFileName, ViewHelperName $viewHelperName): mixed
    {
        if (!file_exists($pdaFileName)) {
            return new ArgumentTransformers();
        }

        $argumentTransformers = require $pdaFileName;
        if (!$argumentTransformers instanceof ArgumentTransformers) {
            throw new RuntimeException(
                'The PDA file ' . $pdaFileName . ' for the component "' . $viewHelperName->fullName . '" did not return an instance of ' . ArgumentTransformers::class,
                3130845333,
            );
        }

        return $argumentTransformers;
    }
}
