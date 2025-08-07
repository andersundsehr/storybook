import type { InlineConfig } from 'vite';
import type { Options } from 'storybook/internal/types';
import { glob } from 'node:fs/promises';
import { basename } from 'node:path';

async function isFeatureEnabled(name: string, options: Options): Promise<boolean> {
  const envs = await options.presets.apply('env');
  return ['true', '1'].includes(envs[name] || process.env[name] || '0');
}
export async function viteFinal(config: InlineConfig, options: Options): Promise<InlineConfig> {
  if (await isFeatureEnabled('STORYBOOK_TYPO3_WATCH_ONLY_STORIES', options)) {
    config = await watchOnlyStoriesConfig(config, options);
  }
  config = addAllowedHosts(config);
  return config;
}

async function watchOnlyStoriesConfig(config: InlineConfig, options: Options): Promise<InlineConfig> {
  const colorYellow = '\x1b[33m';
  const colorReset = '\x1b[0m';
  console.log(colorYellow + '@andersundsehr/storybook-typo3:' + colorReset + ' STORYBOOK_TYPO3_WATCH_ONLY_STORIES enabled, only watching stories files');

  const defaultGlobPattern = '/**/*.@(mdx|stories.@(mdx|js|jsx|mjs|ts|tsx))';
  const storiesGlob = await options.presets.apply('stories');
  const storiesFiles: string[] = [];
  for (let globs of storiesGlob) {
    if (typeof globs !== 'string') {
      globs = globs.directory + (globs.files || defaultGlobPattern);
    }
    for await (const entry of glob(globs)) {
      storiesFiles.push(entry);
    }
  }

  const alwaysWatch = ['.env'];

  config.server = config.server || {};
  config.server.watch = config.server.watch || {};
  config.server.watch.ignored = (file: string): boolean => {
    const filename = basename(file);
    if (alwaysWatch.includes(filename)) {
      return false; // always watch these files
    }

    if (!filename.includes('.')) {
      return false; // ignore directories
    }

    if (!storiesFiles.includes(file)) {
      return true;
    }
    return false;
  };
  return config;
}

function addAllowedHosts(config: InlineConfig): InlineConfig {
  config.server = config.server || {};
  if (process.env.IS_DDEV_PROJECT && config.server.allowedHosts !== true) {
    config.server.allowedHosts ??= [];
    config.server.allowedHosts.push('.ddev.site');
  }
  return config;
}
