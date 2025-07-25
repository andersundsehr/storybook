import { type Meta, type StoryObj, fetchComponent } from '@andersundsehr/storybook-typo3';

/**
 * This is a simple card component that can be used to display title, text and add a link to that card.
 */
export default {
  component: await fetchComponent('de:card'),
} satisfies Meta;

export const Pirate: StoryObj = {
  args: {
    title: 'Yar Pirate Ipsum',
    text: 'Prow scuttle parrel provost Sail ho shrouds spirits boom mizzenmast yardarm. Pinnace holystone mizzenmast quarter crow\'s nest nipperkin grog yardarm hempen halter furl. Swab barque interloper chantey doubloon starboard grog black jack gangway rutters.',
    link: 'https://www.andersundsehr.com',
  },
};

export const Hipster: StoryObj = {
  args: {
    title: 'Dummy text? More like dummy thicc text, amirite?',
    text: 'I\'m baby ramps voluptate ex af small batch asymmetrical. Raw denim tacos artisan aliquip authentic, qui praxis celiac banh mi listicle bushwick before they sold out bodega boys vegan pug. Pop-up mixtape ramps meditation. Lyft disrupt cray solarpunk tofu labore veniam banh mi bruh culpa. Viral pinterest bicycle rights id hella swag jawn neutra proident aesthetic ad aute synth chambray.',
    link: 'https://github.com/andersundsehr/storybook',
  },
};

export const Zombie: StoryObj = {
  args: {
    title: 'Zombie ipsum',
    text: 'Zombie ipsum brains reversus ab cerebellum viral inferno, brein nam rick mend grimes malum cerveau cerebro. De carne cerebro lumbering animata cervello corpora quaeritis. Summus thalamus brains sit​​, morbo basal ganglia vel maleficia? De braaaiiiins apocalypsi gorger omero prefrontal cortex undead survivor fornix dictum mauris. Hi brains mindless mortuis limbic cortex soulless creaturas optic nerve, imo evil braaiinns stalking monstra hypothalamus adventus resi hippocampus dentevil vultus brain comedat cerebella pitiutary gland viventium. Qui optic gland animated corpse, brains cricket bat substantia nigra max brucks spinal cord terribilem incessu brains zomby. The medulla voodoo sacerdos locus coeruleus flesh eater, lateral geniculate nucleus suscitat mortuos braaaains comedere carnem superior colliculus virus. Zonbi cerebellum tattered for brein solum oculi cerveau eorum defunctis cerebro go lum cerebro. Nescio brains an Undead cervello zombies. Sicut thalamus malus putrid brains voodoo horror. Nigh basal ganglia tofth eliv ingdead.',
    link: 'https://github.com/andersundsehr/storybook',
  },
};

export const Cheese: StoryObj = {
  args: {
    title: 'I love cheese',
    text: 'I love cheese, especially cheesy grin rubber cheese. Mozzarella danish fontina blue castello feta halloumi parmesan cheesy feet port-salut. When the cheese comes out everybody\'s happy cheese on toast cheese on toast edam cheesy grin bocconcini cheddar edam. Cream cheese cut the cheese smelly cheese queso cheese and wine cheesy grin cottage cheese red leicester. Manchego babybel cut the cheese.',
    link: 'https://github.com/andersundsehr/storybook',
  },
};
