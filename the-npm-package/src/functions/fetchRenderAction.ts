import { type StoryContext, url } from '@andersundsehr/storybook-typo3';

import { fetchWithUserRetry } from './fetchWithUserRetry';
import { error } from './error';

export async function fetchRenderAction(urlA: string, id: string, params: unknown, storyContext: StoryContext): Promise<string> {
  if (!storyContext.component) {
    error('No component found in story context. This function requires a Fluid component to render.', 4123764578913);
  }
  const viewHelper = (typeof storyContext.component === 'string') ? storyContext.component : storyContext.component.fullName;

  const body = {
    viewHelper: viewHelper,
    arguments: params,
    site: storyContext.globals?.site || 'default',
    siteLanguage: storyContext.globals?.language || 'default',
  };

  return await fetchWithUserRetry<string>(url + '/_storybook/render', {
    method: 'POST',
    body: JSON.stringify(body),
  }, 'rendering component', 'text');
};
