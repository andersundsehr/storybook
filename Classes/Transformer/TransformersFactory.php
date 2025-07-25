<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use Andersundsehr\Storybook\Dto\ViewHelperName;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinition;
use TYPO3Fluid\Fluid\Core\Component\ComponentDefinitionProviderInterface;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;

use function array_keys;
use function enum_exists;
use function file_exists;
use function in_array;
use function preg_replace;

final readonly class TransformersFactory
{
    public function __construct(private TypeTransformers $typeTransformers)
    {
    }

    public function get(
        ComponentTemplateResolverInterface&ComponentDefinitionProviderInterface $collection,
        ViewHelperName $viewHelperName
    ): Transformers {
        $templateName = $collection->resolveTemplateName($viewHelperName->name);
        $fileName = $collection->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $templateName);
        $pdaFileName = preg_replace('/\.html$/', '.transformer.php', (string) $fileName) ?:
            throw new RuntimeException(
                'Could not resolve the transformer file for the view helper "' . $viewHelperName->fullName . '"',
                5992417357,
            );

        if (!file_exists($pdaFileName)) {
            return new Transformers([], $pdaFileName . ' (no transformers defined)');
        }

        $argumentTransformers = require $pdaFileName;
        if (!$argumentTransformers instanceof ArgumentTransformers) {
            throw new RuntimeException(
                'The PDA file ' . $pdaFileName . ' for the component "' . $viewHelperName->fullName . '" did not return an instance of ' . ArgumentTransformers::class,
                3130845333,
            );
        }

        $argumentDefinitions = $collection->getComponentDefinition($viewHelperName->name)->getArgumentDefinitions();
        foreach (array_keys($argumentTransformers->arguments) as $name) {
            if (!isset($argumentDefinitions[$name])) {
                throw new RuntimeException(
                    'The transformer for argument "' . $name . '" is not present in the component. remove it from ' . $pdaFileName . ' or add it to the component.',
                    1153552856
                );
            }
        }

        $transformers = [];

        foreach ($argumentDefinitions as $argumentName => $argumentDefinition) {
            if (isset($argumentTransformers->arguments[$argumentName])) {
                $transformers[$argumentName] = Transformer::fromCallable($argumentTransformers->arguments[$argumentName], $pdaFileName . ':' . $argumentName);
                continue;
            }

            $type = $argumentDefinition->getType();
            if (in_array($type, ['bool', 'int', 'float', 'string', DateTime::class, DateTimeImmutable::class, DateTimeInterface::class], true)) {
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
                // TODO use better type comparison
                // instanceof, is_a, etc.
                // make this work with Type[] arrays
                // and Union types
                throw new RuntimeException(
                    '🥺🙏 please report this!!! https://github.com/andersundsehr/storybook/issues The transformer for argument "' . $argumentName . '" returns a value of type "' . $resultType . '" but the component expects a value of type "' . $targetType . '". ' .
                    'Please adjust the transformer or the component definition.',
                    4128088840
                );
            }
        }
    }
}
