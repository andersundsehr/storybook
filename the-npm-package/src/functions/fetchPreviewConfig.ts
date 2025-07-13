import { url } from '@andersundsehr/storybook-typo3';
import { fetchWithUserRetry } from './fetchWithUserRetry.ts';
import type { GlobalTypes, InputType } from 'storybook/internal/types';
import { GLOBALS_UPDATED, SET_GLOBALS } from 'storybook/internal/core-events';
import { addons } from 'storybook/preview-api';

function getLocation(): Location {
  let location = window.location;
  if (window.parent?.location && window.parent !== window) {
    location = window.parent.location;
  }
  return location;
}

function getGlobalsFromUrl(): Record<string, string> {
  const url = new URL(getLocation().href);
  const globalsString = url.searchParams.get('globals') || '';
  const globalsArray = globalsString.split(';').filter(Boolean);
  const entries = globalsArray.map(global => global.split(':'));
  return Object.fromEntries(entries);
}

function setGlobalsToUrl(globals: Record<string, string>) {
  const location = getLocation();
  const url = new URL(location.href);
  const globalString = Object.entries(globals).map(([key, value]) => `${key}:${value}`).join(';');
  url.searchParams.set('globals', globalString);
  location.href = url.toString();
}

interface PreviewConfig {
  globalTypes: GlobalTypes;
  initialGlobals: Record<string, string>;
}

export async function fetchPreviewConfig(currentGlobals?: Record<string, string>): Promise<PreviewConfig> {
  const apiEndpoint = url + '/_storybook/preview';

  const globals = currentGlobals || getGlobalsFromUrl();

  return await fetchWithUserRetry<PreviewConfig>(
    apiEndpoint,
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ globals }),
    },
    'preview config from TYPO3',
  );
}

export function initGlobalsHandling(initalGlobalTypes: GlobalTypes) {
  const channel = addons.getChannel();
  let oldGlobalTypes = initalGlobalTypes;
  channel.on(GLOBALS_UPDATED, async ({ globals }: { globals: Record<string, string> }) => {
    const previewConfig = await fetchPreviewConfig(globals);

    oldGlobalTypes = previewConfig.globalTypes;

    const newGlobals: Record<string, string> = getGlobalsFromUrl();

    let changed = false;
    for (const key in previewConfig.globalTypes) {
      const inputType: InputType = previewConfig.globalTypes[key];
      if (!inputType.toolbar?.items?.some(item => item.value === globals[key])) {
        newGlobals[key] = inputType.toolbar?.items?.[0]?.value || '';
        changed = true;
      }
    }

    if (changed) {
      // change URL search params and reload page (this is the only way to set the globals, that worked)!!
      setGlobalsToUrl(newGlobals);
      return;
    }

    if (JSON.stringify(oldGlobalTypes) !== JSON.stringify(previewConfig.globalTypes)) {
      // this only sets the globalTypes and not the globals :(
      // see https://github.com/storybookjs/storybook/blob/3ac2fece4c41955e7349f6f825b7d21dd18ff9a9/code/core/src/manager-api/modules/globals.ts#L141-L149
      channel.emit(SET_GLOBALS, { globals, globalTypes: previewConfig.globalTypes });
      return;
    }
  });
}
