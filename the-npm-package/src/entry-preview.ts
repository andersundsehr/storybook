import { getPreviewConfig, renderInTypo3, type StoryContext } from '@andersundsehr/storybook-typo3';
import type { ArgTypesEnhancer, GlobalTypes, InputType } from 'storybook/internal/types';
import { GLOBALS_UPDATED, SET_GLOBALS } from 'storybook/internal/core-events';
import { addons } from 'storybook/preview-api';

const channel = addons.getChannel();
let oldGlobalTypes: Record<string, InputType>;
channel.on(GLOBALS_UPDATED, async ({ globals }) => {
  console.log('current globals', globals);
  const previwConfig = await getPreviewConfig(globals);

  if (oldGlobalTypes) {
    const oldLanguage = oldGlobalTypes.language.toolbar.items[0].value;
    if (!previwConfig.globalTypes.language.toolbar.items.some(item => item.value === oldLanguage)) {
      // if the old language is not available anymore, we need to reset it
      globals.language = previwConfig.globalTypes.language.toolbar.items[0].value;
    }
  }

  if (JSON.stringify(previwConfig.globalTypes) !== JSON.stringify(oldGlobalTypes)) {
    channel.emit(SET_GLOBALS, { globals, globalTypes: previwConfig.globalTypes });
  }
});

const previewConfig = await getPreviewConfig();

// console.log('previewConfig: entry-preview.ts', previewConfig);

export const globalTypes = previewConfig.globalTypes;

export const initialGlobals: GlobalTypes = previewConfig.initialGlobals;

export const parameters = {
  server: {
    // url: url + '/_storybook/',
    fetchStoryHtml: renderInTypo3,
  },
};

const enhanceArgTypes: ArgTypesEnhancer = (storyContext: StoryContext) => storyContext.component?.argTypes || {};
export const argTypesEnhancers: ArgTypesEnhancer[] = [enhanceArgTypes];
