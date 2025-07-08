import { url, type StoryContext } from '@andersundsehr/storybook-typo3';

import { fetchWithUserRetry } from './fetchWithUserRetry.ts';

export async function fetchRenderAction(urlA: string, id: string, params: any, storyContext: StoryContext): Promise<string> {
  const viewHelper = (typeof storyContext.component === 'string') ? storyContext.component : storyContext.component.fullName;

  const body = {
    viewHelper: viewHelper,
    arguments: params,
    site: storyContext.globals?.site || 'default',
    siteLanguage: storyContext.globals?.language || 'default',
  };

  const response = await fetchWithUserRetry(url + '/_storybook/render', {
    method: 'POST',
    body: JSON.stringify(body),
  }, 'rendering component in TYPO3');
  return response.text();
};
