:navigation-title: Vite Integration

..  _viteIntegration:

================================
Integrate your vite config with Storybook
================================

.. note::
   if you do not use vite in your project setup (eg. you do not have a `vite.config.js/ts` file in your project root),
   you can skip this section. `next section <firstStorybookStory>`_

Storybook uses Vite under the hood to build and serve your stories.
By default, Storybook will use your local Vite configuration if it exists.
However, some configurations may be overridden or not compatible with Storybook's setup.
This document provides a guide on how to integrate your Vite configuration with Storybook effectively.

Usage with Vite AssetCollector
###

If you are using `Vite AssetCollector <https://extensions.typo3.org/extension/vite_asset_collector>`_ make sure you set the `aliases` option to `EXT`.

.. code-block:: js
   :caption: vite.config.js|ts

    import { defineConfig } from 'vite';
    import typo3 from 'vite-plugin-typo3';
    
    export default defineConfig({
      plugins: [typo3({ aliases: 'EXT' })],
    });


Ignore your local Vite config
###

Some times it is necessary to ignore your local Vite config in Storybook.
You can disable the usage of your local Vite config in Storybook by setting viteConfigPath in the `.storybook/main.ts`/`.js`:

https://storybook.js.org/docs/builders/vite#override-the-default-configuration

..  code-block:: bash
   :caption: .storybook/main.ts|js
   :emphasize-lines: 6

    export default {
      core: {
        builder: {
          name: '@storybook/builder-vite',
          options: {
            // the file needs to exist, and export a (empty) valid vite config
            viteConfigPath: './customVite.config.js',
          },
        },
      },
    };


Override Storybook's Vite configuration
###

Some configurations in your local Vite config may not be compatible with Storybook's setup. Or maybe be overridden by Storybook.
You can create a `finalVite` function in your `.storybook/main.ts`/`.js` file to override Storybook's Vite configuration.

..  code-block:: bash
   :caption: .storybook/main.ts|js
   :emphasize-lines: 5-14

    import type { StorybookConfig } from '@andersundsehr/storybook-typo3';
    import { mergeConfig, type InlineConfig } from 'vite';

    export default {
      viteFinal: async (config, option) => {
        return mergeConfig(config, {
          server: {
            allowedHosts: ['.ddev.site'],
            hmr: {
              clientPort: 8080,
              protocol: 'wss',
            },
          },
        } satisfies Partial<InlineConfig>);
      },
    };


How to handle Assets (JS/CSS) in Storybook
###

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
