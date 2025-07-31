import { type Meta, type StoryObj, fetchComponent } from '@andersundsehr/storybook-typo3';

/**
 * Displays multiple slots
 */
export default {
  component: await fetchComponent('de:slotExample'),
} satisfies Meta;

export const Empty: StoryObj = {
  args: {},
};

export const DefaultOnly: StoryObj = {
  args: {
    slot____default: 'default filled',
  },
};

export const AllButDefault: StoryObj = {
  args: {
    slot____default: '',
    slot____button: 'Banjamin',
    slot____third: 'time’s the charm.',
  },
};

export const All: StoryObj = {
  args: {
    slot____default: 'still possible',
    slot____button: 'Banjamin',
    slot____third: 'time’s the charm.',
  },
};
