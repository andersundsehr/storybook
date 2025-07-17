import { type Meta, type StoryObj, fetchComponent } from '@andersundsehr/storybook-typo3';

export default {
  component: await fetchComponent('de:transformerExample'),
} satisfies Meta;

export const transform: StoryObj = {
  args: {
    applicationType: "{f:constant(name: 'TYPO3\\CMS\\Core\\Http\\ApplicationType::FRONTEND')}",
    uri__url: 'https://storybook.andersundsehr.com/_storybook/preview?nc',
    websocket__url: 'andersundsehr.com',
    contextIcon__severity: "{f:constant(name: 'TYPO3\\CMS\\Core\\Type\\ContextualFeedbackSeverity::NOTICE')}",
    combineUri__host: 'example.com',
    combineUri__path: 'index.html',
    dateTimeInterface: 1752739200000,
  },
};
