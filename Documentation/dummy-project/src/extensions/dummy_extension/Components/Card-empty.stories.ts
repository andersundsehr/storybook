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
