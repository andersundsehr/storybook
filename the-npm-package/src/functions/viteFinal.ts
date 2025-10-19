import type { InlineConfig } from 'vite';
import type { Options } from 'storybook/internal/types';
import { glob } from 'node:fs/promises';
import { basename } from 'node:path';
import { vite404Plugin } from './vite404Plugin.ts';

const colorYellow = '\x1b[33m';
const colorRed = '\x1b[31m';
const colorReset = '\x1b[0m';
const logPrefix = colorYellow + '@andersundsehr/storybook-typo3:' + colorReset;

async function isFeatureEnabled(name: string, envs: Record<string, string>): Promise<boolean> {
  return ['true', '1'].includes(envs[name] || '0');
}

function addProxyPlugin(config: InlineConfig, url: string) {
  config.plugins = [...(config.plugins || []), vite404Plugin(url)];
  return config;
}

export async function viteFinal(config: InlineConfig, options: Options): Promise<InlineConfig> {
  const envs = await options.presets.apply('env');
  if (await isFeatureEnabled('STORYBOOK_TYPO3_WATCH_ONLY_STORIES', envs)) {
    config = await watchOnlyStoriesConfig(config, options);
  }
  config = addAllowedHosts(config);
  config = addProxyPlugin(config, envs.STORYBOOK_TYPO3_ENDPOINT);
  config = ensureTopLevelAwaitSupport(config);
  return config;
}

async function watchOnlyStoriesConfig(config: InlineConfig, options: Options): Promise<InlineConfig> {
  console.log(logPrefix + ' STORYBOOK_TYPO3_WATCH_ONLY_STORIES enabled, only watching stories files');

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

function ensureTopLevelAwaitSupport(config: InlineConfig): InlineConfig {
  const minTargetsForTopLevelAwait = ['es2022', 'edge89', 'firefox89', 'chrome89', 'safari15'];
  const incompatibleVersions = ['es2020', 'es2021', 'chrome87', 'edge88', 'firefox78', 'safari14'];

  config.build = config.build || {};

  if (config.build.target) {
    const targets = Array.isArray(config.build.target) ? config.build.target : [config.build.target];
    
    const incompatibleTargets = targets.filter((target) => {
      return incompatibleVersions.includes(target);
    });

    if (incompatibleTargets.length > 0) {
      console.warn(
        colorRed + logPrefix + ' WARNING: Your build target contains environments that do not support top-level await: ' + 
        incompatibleTargets.join(', ') + colorReset
      );
      console.warn(
        logPrefix + ' To support top-level await, consider using: ' +
        minTargetsForTopLevelAwait.join(', ')
      );
    }
  } else {
    config.build.target = minTargetsForTopLevelAwait;
  }

  return config;
}
