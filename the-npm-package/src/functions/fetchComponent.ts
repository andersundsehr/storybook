import { type FluidComponent, url } from '@andersundsehr/storybook-typo3';

export async function fetchComponent(component: string): Promise<FluidComponent> {
  if (!component.includes(':')) {
    throw new Error('Component name must be in the format "namespace:name"');
  }
  const response = await fetch(url + '/_storybook/componentMeta?viewHelper=' + component);
  const data = await response.json();

  return {
    fullName: component,
    name: component.split(':')[1],
    namespace: component.split(':')[0],
    collection: data.collectionClassName,
    argTypes: data.argTypes,
    code: data.code,
  };
}
