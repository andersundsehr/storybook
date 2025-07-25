import { type Meta, type StoryObj, fetchComponent } from '@andersundsehr/storybook-typo3';

/**
 * Displays the full range of normal arguments and slots.
 *
 * For Complex PHP types see the `TransformerExample` component.
 */
export default {
  component: await fetchComponent('de:fullExample'),
} satisfies Meta;

export const OnlyRequired: StoryObj = {
  args: {
    requiredArgument: 'this is required',
  },
};

export const AllArguments: StoryObj = {
  args: {
    requiredArgument: 'this is required',
    optionalBool: true,
    optionalInt: 42,
    optionalFloat: 3.14159,
    optionalString: 'this is optional',
    optionalWithDefaultBool: false,
    optionalWithDefaultInt: -42,
    optionalWithDefaultFloat: -3.14159,
    optionalWithDefaultString: 'this is also optional',
  },
};

export const WithSlot: StoryObj = {
  args: {
    requiredArgument: 'this is required',
    slot____default: '<h1>This is the default slot content</h1>\n<p>And this is some additional content in the default slot.</p>',
  },
};
