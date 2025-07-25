:navigation-title: First Fluid Component

..  _firstFluidComponent:

=======================
Write your First Fluid Component
=======================

..  rst-class:: bignums

1. create a ComponentCollection

   create a Class in your extension eg. `Classes/ComponentCollection.php`:

   ..  literalinclude:: /dummy-project/src/extensions/dummy_extension/Classes/ComponentCollection.php
       :caption: Classes/ComponentCollection.php

2. create the Fluid Component

   create a Fluid component in your extension eg. `Components/Card/Card.html`:

   ..  literalinclude:: /dummy-project/src/extensions/dummy_extension/Components/Card/Card.html
       :caption: Components/Card/Card.html

3. you now have created your first Fluid component

   you can use this component in any Fluid template like this:

   .. code-block:: html

      <html
        xmlns:de="http://typo3.org/ns/Andersundsehr/DummyExtension/ComponentCollection"
        data-namespace-typo3-fluid="true"
      >

      <de:card
        title="Yar Pirate Ipsum"
        text="Prow scuttle parrel provost Sail ho shrouds spirits boom mizzenmast yardarm. Pinnace holystone mizzenmast quarter crow's nest nipperkin grog yardarm hempen halter furl. Swab barque interloper chantey doubloon starboard grog black jack gangway rutters."
        link="https://www.andersundsehr.com"
      />

4. ..  _registerComponentCollection:
   register the ComponentCollection as global Fluid namespace

   in your `ext_localconf.php` file, register the ComponentCollection as global Fluid namespace:

   ..  attention::
      this is optional for nomral usage. But required for EXT:storybook

   ..  literalinclude:: /dummy-project/src/extensions/dummy_extension/ext_localconf.php
       :caption: ext_localconf.php
