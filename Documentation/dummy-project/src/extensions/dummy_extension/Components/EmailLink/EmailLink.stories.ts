import { type Meta, type StoryObj, fetchComponent } from '@andersundsehr/storybook-typo3';

/**
 * Regression fixture for email link rendering in Storybook previews.
 */
export default {
  component: await fetchComponent('de:emailLink'),
} satisfies Meta;

export const Default: StoryObj = {
  args: {
    email: 'test@example.com',
  },
};
