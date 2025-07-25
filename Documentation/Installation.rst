:navigation-title: Installation

..  _installation:

================================
Installation of EXT:storybook and storybook
================================

.. rst-class:: bignums


1. install EXT:storybook

   installation of EXT:storybook is done via composer:

   ..  code-block:: bash
       :caption: install via composer

       composer req andersundsehr/storybook --dev

   It is also possible to install EXT:storybook in legacy mode, but this is not recommended.
   `https://extensions.typo3.org/package/storybook <https://extensions.typo3.org/package/storybook>`_

   ..  tip::
       You still need to use `npm` so you should also use `composer`.

2. install storybook

   ..  code-block:: bash
       :caption: install via npm

       npm install --save-dev file:./vendor/andersundsehr/storybook/the-npm-package
       # TODO @kanti publish to npm so it can be installed from there as well
       npm install --save-dev @andersundsehr/storybook-typo3

3. create `/.storybook/` directory

   create a `.storybook` directory besides your `package.json` file.

   you need to update your `STORYBOOK_TYPO3_ENDPOINT` to your TYPO3 instance URL:

   ..  literalinclude:: /dummy-project/.storybook/main.ts
       :caption: /.storybook/main.ts
       :language: ts
       :emphasize-lines: 17
       :linenos:

   ..  literalinclude:: /dummy-project/.storybook/preview.ts
       :caption: /.storybook/preview.ts
       :language: ts
       :linenos:

4. configure package.json

   add the scripts to your `package.json` file:

   ..  literalinclude:: /dummy-project/package.json
       :caption: /package.json
       :language: json
       :emphasize-lines: 4-6

5. # Now you have a working Storybook setup!

   You can now run Storybook with the following command:

   ..  code-block:: bash
       :caption: start storybook

       npm run storybook

   This will start the Storybook server and open it in your default browser.

   You can now start creating stories for your TYPO3 Fluid components!
