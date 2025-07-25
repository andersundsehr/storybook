import { SNIPPET_RENDERED, SourceType } from 'storybook/internal/docs-tools';
import { type FluidRenderer } from '@andersundsehr/storybook-typo3';
import { DecoratorFunction } from 'storybook/internal/types';
import { addons, useEffect } from 'storybook/preview-api';
import { convertComponentToSource } from './functions/convertComponentToSource';

const sourceDecorator: DecoratorFunction<FluidRenderer> = (storyFn, storyContext) => {
  useEffect(() => {
    const { id, args, component } = storyContext;
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
