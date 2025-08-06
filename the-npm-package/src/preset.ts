import { dirname, join } from 'node:path';
import type { Entry, PresetProperty } from 'storybook/internal/types';
import { createRequire } from 'module';
import type { ViteFinal } from '@storybook/builder-vite';
import { glob } from 'node:fs/promises';
import { basename } from 'node:path';
import { viteFinal } from './functions/viteFinal.ts';

const require = createRequire(import.meta.url);

function getAbsolutePath<I extends string>(value: I): I {
  return dirname(require.resolve(join(value, 'package.json'))) as any;
}

export const addons = ['@storybook/addon-docs', '@storybook/addon-a11y'];

/**
 * We want storybook to not use your local vite config.
 * As that is not really needed, and can cause issues or break storybook.
 */
export { viteFinal };

export const core: PresetProperty<'core'> = {
  builder: {
    name: '@storybook/builder-vite',
    options: {
      // to overwrite the user's vite.config.js
      viteConfigPath: require.resolve('./empty-vite.config'),
    },
  },
  renderer: getAbsolutePath('@storybook/server'),
  disableTelemetry: true,
};

export const previewAnnotations: PresetProperty<'previewAnnotations'> = async (entry: Entry[] = [], options) => {
  const docsEnabled = Object.keys(await options.presets.apply('docs', {}, options)).length > 0;

  return entry
    .concat(require.resolve('./entry-preview'))
    .concat(docsEnabled ? [require.resolve('./entry-preview-docs')] : []);
};

export const tags = ['autodocs'];

/**
 * BUGFIX for chromium based browsers on windows
 * @see https://github.com/talkjs/country-flag-emoji-polyfill?tab=readme-ov-file
 */
export const managerHead = `<style>
  body {
    font-family: "Twemoji Country Flags", "Nunito Sans", -apple-system, ".SFNSText-Regular", "San Francisco", BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Helvetica, Arial, sans-serif !important;
  }
  @font-face {
    font-family: "Twemoji Country Flags";
    unicode-range: U+1F1E6-1F1FF, U+1F3F4, U+E0062-E0063, U+E0065, U+E0067, U+E006C, U+E006E, U+E0073-E0074, U+E0077, U+E007F;
    src: url('https://cdn.jsdelivr.net/npm/country-flag-emoji-polyfill@0.1/dist/TwemojiCountryFlags.woff2') format('woff2');
    font-display: swap;
  }
</style>`;
