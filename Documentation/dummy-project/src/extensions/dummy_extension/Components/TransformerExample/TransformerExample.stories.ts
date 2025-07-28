import { fetchComponent, type Meta, type StoryObj } from '@andersundsehr/storybook-typo3';

/**
 * if your component dose not only use basic types like string, number, boolean, etc.
 * you need Transformers to convert the values to the correct types.
 * in this component you can see different transformers in action.
 * - `applicationType`: displays a select of all EnumCases of the ApplicationType and converts the selected value to the EnumCase
 * - `uri`: converts a string to a Uri object
 * - `websocket`: converts a string to a Uri object there schema is always `wss`
 * - `contextIcon`: uses a argument transformer to create a select for the `ContextualFeedbackSeverity->getIconIdentifier()`
 * - `combineUri`: combines different values to a Uri object
 * - `dateTimeInterface`: displays a date time picker and converts the value to a `DateTimeInterface` object
 */
export default {
  component: await fetchComponent('de:transformerExample'),
} satisfies Meta;

export const transform: StoryObj = {
  args: {
    applicationType: '{f:constant(name: \'TYPO3\\CMS\\Core\\Http\\ApplicationType::FRONTEND\')}',
    uri__url: 'https://storybook.andersundsehr.com/_storybook/preview?nc',
    websocket__url: 'andersundsehr.com',
    contextIcon__severity: '{f:constant(name: \'TYPO3\\CMS\\Core\\Type\\ContextualFeedbackSeverity::NOTICE\')}',
    combineUri__host: 'example.com',
    combineUri__path: 'index.html',
    dateTimeInterface: 1752739200000,
  },
};
