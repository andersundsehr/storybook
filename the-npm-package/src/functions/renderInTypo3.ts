import  { url, type StoryContext } from '@andersundsehr/storybook-typo3';

export async function renderInTypo3(urlA: string, id: string, params: any, storyContext: StoryContext): Promise<string> {
  const viewHelper = (typeof storyContext.component === 'string') ? storyContext.component : storyContext.component.fullName;

  const body = {
    viewHelper: viewHelper,
    arguments: params,
    site: storyContext.globals?.site || 'default',
    siteLanguage: storyContext.globals?.language || 'default',
  };

  // console.log('renderInTypo3', { url, id, params, body, storyContext });

  const response = await fetch(url + '/_storybook/render', {
    method: 'POST',
    body: JSON.stringify(body),
  });

  return response.text();
};
