import { type StoryContext, url } from '@andersundsehr/storybook-typo3';

import { fetchWithUserRetry } from './fetchWithUserRetry';
import { error } from './error';
import type { ViewMode } from 'storybook/internal/csf';
import { addons } from 'storybook/preview-api';
import { UPDATE_STORY_ARGS } from 'storybook/internal/core-events';

let iframeContextId = 'sb-story-id-' + Math.random().toString(36).substring(2, 15);

/**
 * the IframeContextId is used to cache bust JS and CSS files,
 * but on docs mode we want the same context ID as all the stories are rendered in the same iframe
 */
function getIframeContextId(viewMode: ViewMode, forceNew = false): string {
  if (viewMode === 'story') {
    // for story render we always create a new context ID
    return (iframeContextId = 'sb-story-id-' + Math.random().toString(36).substring(2, 15));
  }
  // for docs we only want to create a new context ID if we were in story mode before
  if (iframeContextId.startsWith('sb-story-id-') || forceNew) {
    return (iframeContextId = 'sb-docs-id-' + Math.random().toString(36).substring(2, 15));
  }
  return iframeContextId;
}
addons.getChannel().on(UPDATE_STORY_ARGS, () => {
  // if args change we want to make sure that the next render also gets a new context ID
  getIframeContextId('docs', true);
});

export async function fetchRenderAction(urlA: string, id: string, params: unknown, storyContext: StoryContext): Promise<string> {
  if (!storyContext.component) {
    error('No component found in story context. This function requires a Fluid component to render.', 4123764578913);
  }
  const body = {
    viewHelper: storyContext.component.fullName,
    arguments: params,
    site: storyContext.globals?.site || 'default',
    siteLanguage: storyContext.globals?.language || 'default',
    iframeContextId: getIframeContextId(storyContext.viewMode),
  };

  return await fetchWithUserRetry<string>(
    url + '/_storybook/render',
    {
      method: 'POST',
      body: JSON.stringify(body),
      signal: storyContext.abortSignal,
    },
    'rendering component',
    'text',
  );
}
