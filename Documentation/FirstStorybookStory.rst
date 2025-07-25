:navigation-title: First Storybook Story

..  _firstStorybookStory:

========================
Write your First Storybook Story
========================

A storybook stories file includes a `Meta` object and one or more `StoryObj` objects.

the `Meta` object defines the component that is used in the story.

the `StoryObj` object defines the story itself, including the arguments that are passed to the component.

to create your first story you only need to create a file with the `Meta` and one empty `StoryObj`.

this is a minimal example of a storybook story file:

.. code-block:: ts
  :caption: Components/Card/Card.stories.ts
  :linenos:
  :emphasize-lines: 7

  import { type Meta, type StoryObj, fetchComponent } from '@andersundsehr/storybook-typo3';

  /**
   * This is a simple card component that can be used to display title, text and add a link to that card.
   */
  export default {
    component: await fetchComponent('de:card'),
  } satisfies Meta;

  export const Story1: StoryObj = {
    args: {},
  };


you only need to change the `component` name to your Fluid component name, in this case `de:card`.

..  attention::
  make sure you have registered your Fluid `ComponentCollection` as global Fluid namespace in your `ext_localconf.php` file. `See FirstFluidComponent <registerComponentCollection>`_ for

Now you can go into your Storybook UI, you will see an error if you have required arguments.

..  figure:: /dummy-project/tests/screenshot.spec.ts-snapshots/empty-card-story-1-chromium-linux.png
    :alt: The error is shown in the Storybook UI

You than can go to the `Story 1` view and set all required arguments.

After that you can save the updated arguments with the button: `✔️ Update story` at the bottom of the page.

..  figure:: /dummy-project/tests/screenshot.spec.ts-snapshots/empty-card-story-2-chromium-linux.png
    :alt: The error is shown in the Storybook UI


..  seealso::
   if you have more complex arguments than the basic `string`, `bool` etc. you need to use `Transformers <transformers>`_
