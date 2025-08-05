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

       npm install @andersundsehr/storybook-typo3 --save-dev
       # or
       yarn add @andersundsehr/storybook-typo3 --dev

   ..  caution::
       make sure you install the same version for both:

       * composer `andersundsehr/storybook`
       * npm/yarn `@andersundsehr/storybook-typo3`

3. create `/.storybook/` directory

   create a `.storybook` directory besides your `package.json` file.

   you need to update your `STORYBOOK_TYPO3_ENDPOINT` to your TYPO3 instance URL:

   ..  literalinclude:: /dummy-project/.storybook/main.ts
       :caption: .storybook/main.ts
       :language: ts
       :emphasize-lines: 16
       :linenos:

   ..  literalinclude:: /dummy-project/.storybook/preview.ts
       :caption: .storybook/preview.ts
       :language: ts
       :linenos:

4. configure package.json

   add the scripts to your `package.json` file:

   ..  literalinclude:: /dummy-project/package.json
       :caption: package.json
       :language: json
       :emphasize-lines: 4-6

5. DDEV configuration

   **if** you are using `DDEV` you need to add the following to your `.ddev/config.yaml` file:

   ..  code-block:: yaml
       :caption: .ddev/config.yaml

        web_extra_exposed_ports:
        - name: storybook
          container_port: 8080
          http_port: 8080
          https_port: 8081

6. # Now you have a working Storybook setup!

   You can now run Storybook with the following command:

   ..  code-block:: bash
       :caption: start storybook

       npm run storybook
       # or
       yarn storybook

   This will start the Storybook server. You can than access it in your browser at your configured URL.

   You can now start creating stories for your TYPO3 Fluid components!
