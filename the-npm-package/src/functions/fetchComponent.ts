import { type FluidComponent, url } from '@andersundsehr/storybook-typo3';

import { fetchWithUserRetry } from './fetchWithUserRetry';

interface ComponentMetaData {
  collectionClassName: string;
  argTypes: Record<string, any>;
}

export async function fetchComponent(component: string): Promise<FluidComponent> {
  if (!component.includes(':')) {
    const message = 'Component name must be in the format "namespace:name"';
    alert(message);
    throw new Error(message);
  }

  const data = await fetchWithUserRetry<ComponentMetaData>(
    url + '/_storybook/componentMeta?viewHelper=' + component,
    {},
    'metadata for component `' + component + '`',
  );

  return {
    fullName: component,
    name: component.split(':')[1],
    namespace: component.split(':')[0],
    collection: data.collectionClassName,
    argTypes: data.argTypes,
  };
}
