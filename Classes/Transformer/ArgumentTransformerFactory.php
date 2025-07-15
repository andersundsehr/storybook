<?php

declare(strict_types=1);

namespace Andersundsehr\Storybook\Transformer;

use Andersundsehr\Storybook\Dto\ViewHelperName;
use TYPO3Fluid\Fluid\Core\Component\ComponentTemplateResolverInterface;
use function file_exists;

final readonly class ArgumentTransformerFactory
{
    public function get(ComponentTemplateResolverInterface $collection, ViewHelperName $viewHelperName): ArgumentTransformers
    {
        $templateName = $collection->resolveTemplateName($viewHelperName->name);
        $fileName = $collection->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $templateName);
        $pdaFileName = \Safe\preg_replace('/\.html$/', '.pda.php', $fileName);
        if (!file_exists($pdaFileName)) {
            return new ArgumentTransformers();
        }
        $result = require $pdaFileName;
        if (!$result instanceof ArgumentTransformers) {
            throw new \RuntimeException(
                'The PDA file ' . $pdaFileName . ' for the component "' . $viewHelperName->fullName . '" did not return an instance of ' . ArgumentTransformers::class,
                1666666667
            );
        }
        return $result;
    }
}
