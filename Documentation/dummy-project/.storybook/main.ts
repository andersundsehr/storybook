import type { StorybookConfig } from '@andersundsehr/storybook-typo3';

const config: StorybookConfig = {
  framework: '@andersundsehr/storybook-typo3',

  stories: [
    "../src/**/*.@(mdx|stories.@(mdx|js|jsx|mjs|ts|tsx))",
  ],

  core: {
    disableTelemetry: true,
  },

  env: (envs) => {
    return {
      STORYBOOK_TYPO3_ENDPOINT: 'http://localhost:8011/_storybook/', // if you use DDEV: process.env.DDEV_PRIMARY_URL
      STORYBOOK_TYPO3_WATCH_ONLY_STORIES: '0', // set to '1' If you already use vite in your TYPO3 with HMR
      // do not set your api key here! https://www.deployhq.com/blog/protecting-your-api-keys-a-quick-guide
      ...envs, // envs given to storybook have precedence
    };
  },
};
export default config;
