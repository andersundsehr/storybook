import type { StorybookConfig } from '@andersundsehr/storybook-typo3';

const config: StorybookConfig = {
  framework: '@andersundsehr/storybook-typo3',

  stories: [
    "../src/**/*.mdx",
    '../src/**/*.stories.@(js|jsx|mjs|ts|tsx)',
  ],

  core: {
    disableTelemetry: true,
  },

  env: (envs) => ({
    STORYBOOK_TYPO3_ENDPOINT: 'http://172.23.110.84:9999/_storybook/',
    ...envs, // envs given to storybook have precedence
  }),
};
export default config;
