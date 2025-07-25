:navigation-title: Why and Why Not?

..  _whyAndWhyNot:

=======================
Why and Why Not?
=======================

.. _whatIsComponentBasedDevelopment:

What is Component based development
===========

.. _advantagesOfComponentBasedDevelopment:

Advantages of component based development 🌼
-----------

* allows for reusability of components (e.g., buttons, forms, modals)
* promotes separation of concerns (e.g., UI components, business logic, data handling)
* enables easier testing and maintenance (e.g., testing individual components in isolation)
* facilitates collaboration among developers (e.g., different teams can work on different components)
* enhances scalability of applications (e.g., adding new features without affecting existing ones)
* improves performance by loading only necessary components (if js and css are split)

.. _challengesOfComponentBasedDevelopment:

Challenges of component based development ⚠️
-----------

* requires a shift in mindset from traditional monolithic development
* may introduce complexity in managing dependencies between components
* can lead to over-engineering if not done carefully (e.g., creating too many small components)
* requires careful planning and design to ensure components are reusable and maintainable
* may require additional tooling or frameworks to manage components effectively (e.g., Storybook, component libraries)

additional Information can be found here: `TYPO3 Components <https://docs.typo3.org/permalink/fluid:components>`_

.. _whatIsStorybook:

Storybook as a tool for component based development
===========

.. _advantagesOfStorybook:

Advantages of using Storybook 🌼
-----------

* provides a visual interface for developing and testing components in isolation
* allows for easy documentation of components (e.g., usage examples, arguments, slots)
* enables collaboration among developers and designers (e.g., sharing components, feedback)
* integrates with various testing tools (e.g., playwright)
* supports addons for additional functionality (e.g., accessibility checks, theming)

.. _challengesOfStorybook:

Challenges of using Storybook ⚠️
-----------

* requires additional setup and configuration (e.g., installing dependencies, configuring addons)
* may introduce a learning curve for developers unfamiliar with the tool
* can become complex if not organized properly (e.g., managing multiple stories, addons)
* requires maintenance to keep stories up-to-date with component changes
* may not be suitable for all types of components (e.g., complex components with heavy business logic)

additional Information can be found here: `Why Storybook <https://storybook.js.org/docs/get-started/why-storybook>`_
