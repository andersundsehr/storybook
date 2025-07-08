import { fetchPreviewConfig, fetchRenderAction, type StoryContext } from '@andersundsehr/storybook-typo3';
import type { ArgTypesEnhancer, GlobalTypes } from 'storybook/internal/types';
import { initGlobalsHandling } from './functions/fetchPreviewConfig.ts';

const previewConfig = await fetchPreviewConfig();

initGlobalsHandling(previewConfig.globalTypes);

export const globalTypes: GlobalTypes = previewConfig.globalTypes;

export const initialGlobals: Record<string, string> = previewConfig.initialGlobals;

export const parameters = {
  server: {
    // url: url + '/_storybook/',
    fetchStoryHtml: fetchRenderAction,
  },
};

const enhanceArgTypes: ArgTypesEnhancer = (storyContext: StoryContext) => storyContext.component?.argTypes || {};
export const argTypesEnhancers: ArgTypesEnhancer[] = [enhanceArgTypes];
