import { type FluidComponent, url } from '@andersundsehr/storybook-typo3';

import { fetchWithUserRetry } from './fetchWithUserRetry.ts';

export async function fetchComponent(component: string): Promise<FluidComponent> {
  if (!component.includes(':')) {
    const message = 'Component name must be in the format "namespace:name"';
    alert(message);
    throw new Error(message);
  }

  const response = await fetchWithUserRetry(
    url + '/_storybook/componentMeta?viewHelper=' + component,
    {},
    'metadata for component `' + component + '` from TYPO3',
  );
  const data = await response.json();

  return {
    fullName: component,
    name: component.split(':')[1],
    namespace: component.split(':')[0],
    collection: data.collectionClassName as string,
    argTypes: data.argTypes as Record<string, any>,
  };
}
