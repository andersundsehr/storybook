<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use RuntimeException;
use Andersundsehr\Storybook\Dto\ViewHelperName;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;

use function file_exists;
use function preg_replace;

final readonly class ArgumentTransformerFactory
{
    public function get(ComponentTemplateResolverInterface $collection, ViewHelperName $viewHelperName): ArgumentTransformers
    {
        $templateName = $collection->resolveTemplateName($viewHelperName->name);
        $fileName = $collection->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $templateName);
        $pdaFileName = preg_replace('/\.html$/', '.transformer.php', (string) $fileName) ?:
            throw new RuntimeException(
                'Could not resolve the transformer file for the view helper "' . $viewHelperName->fullName . '"',
                5992417357,
            );
        ;
        if (!file_exists($pdaFileName)) {
            return new ArgumentTransformers();
        }

        $result = require $pdaFileName;
        if (!$result instanceof ArgumentTransformers) {
            throw new RuntimeException(
                'The PDA file ' . $pdaFileName . ' for the component "' . $viewHelperName->fullName . '" did not return an instance of ' . ArgumentTransformers::class,
                3130845333,
            );
        }

        return $result;
    }
}
