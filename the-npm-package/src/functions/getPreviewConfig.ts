import { url } from '@andersundsehr/storybook-typo3';

function getGlobalsFromUrl(): Record<string, string> {
  // TODO maybe use useGlobals instead of this?
  const url = new URL(window.location.href);
  const globalsString = url.searchParams.get('globals') || '';
  const globalsArray = globalsString.split(';').filter(Boolean);
  const entries = globalsArray.map(global => global.split(':'));
  return Object.fromEntries(entries);
}

export async function getPreviewConfig(currentGlobals?: Record<string, string>) {
  const apiEndpoint = url + '/_storybook/preview';

  const globals = currentGlobals || getGlobalsFromUrl();

  console.log('getGlobalsFromUrl', globals);

  const response = await fetch(apiEndpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      globals: globals,
    }),
  });
  return response.json();
}
