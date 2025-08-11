:navigation-title: Asset Handling

..  _assetHandling:

================================
How to handle Assets (JS/CSS) in Storybook
================================

Option 1: Import JS/CSS in your template
-----------------------------

The best option is to use the AssetCollector eg. f:asset.* in your components HTML.
This allows you to integrate your JavaScript and CSS files directly into your components without needing to import them in your stories file.

.. code-block:: html
   :caption: Component/Card/Card.js

    <f:asset.css identifier="EXT:my_extension/Component/Card/Card.css" href="EXT:my_extension/Component/Card/Card.css" inline="{true}"/>
    <f:asset.script type="module" identifier="EXT:my_extension/Component/Card/Card.js" src="EXT:my_extension/Component/Card/Card.js" inline="{true}"/>

Option 2: Integrate JS/CSS in your template
-----------------------------
Alternative is to import your JavaScript and CSS files directly in your stories file.

.. code-block:: js
   :caption: Component/Card/Card.js

    <f:asset.css identifier="EXT:my_extension/Component/Card/Card.css">
      .your-css-class {
        color: red;
      }
    </f:asset.css>
    <f:asset.script type="module" identifier="EXT:my_extension/Component/Card/Card.js">
      console.log('This is a script for the Card component an is only once in the HTML');
    </f:asset.script>

Option 3: Auto import JS/CSS in your ComponentCollection
-----------------------------

Alternatively, you can also auto import your JavaScript and CSS files inside your ComponentCollection class:

.. code-block:: php
   :caption: Classes/ComponentCollection.php
   :emphasize-lines: 4,11-27


    #[Autoconfigure(public: true)]
    final class ComponentCollection extends AbstractComponentCollection
    {
        public function __construct(private readonly AssetCollector $assetCollector)
        {
        }

        #[Override]
        public function getAdditionalVariables(string $viewHelperName): array
        {
            $templateName = $this->resolveTemplateName($viewHelperName);
            $fileName = $this->getTemplatePaths()->resolveTemplateFileForControllerAndActionAndFormat('Default', $templateName);
            $jsFile = str_replace('.html', '.js', $fileName);
            if (file_exists($jsFile)) {
                $this->assetCollector->addInlineJavaScript(
                    self::class . ':' . $viewHelperName,
                    file_get_contents($jsFile),
                    ['type' => 'module'],
                );
            }
            $cssFile = str_replace('.html', '.css', $fileName);
            if (file_exists($cssFile)) {
                $this->assetCollector->addInlineStyleSheet(
                    self::class . ':' . $viewHelperName,
                    file_get_contents($cssFile),
                );
            }

            return [];
        }
    }
