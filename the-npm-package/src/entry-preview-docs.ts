import { SNIPPET_RENDERED, SourceType } from 'storybook/internal/docs-tools';
import { type FluidRenderer } from '@andersundsehr/storybook-typo3';
import { type DecoratorFunction } from 'storybook/internal/types';
import { addons, useEffect } from 'storybook/preview-api';
import { convertComponentToSource } from './functions/convertComponentToSource';
import { error } from './functions/error';

const sourceDecorator: DecoratorFunction<FluidRenderer> = (storyFn, storyContext): unknown => {
  useEffect(() => {
    const { id, args, component } = storyContext;
    if (!component) {
      error('No component found in story context. This decorator requires a Fluid component to render.', 4123764578913);
    }
    const source = convertComponentToSource(component, args);
    addons.getChannel().emit(SNIPPET_RENDERED, { id, args, source });
  });

  return storyFn();
};

export const tags = ['autodocs'];

export const decorators: DecoratorFunction<FluidRenderer>[] = [sourceDecorator];
export const parameters = {
  docs: {
    story: {
      inline: true,
    },
    codePanel: true,
    source: {
      type: SourceType.DYNAMIC,
    },
    toc: true,
  },
};
