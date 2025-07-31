import type { Args, StrictArgs } from '@storybook/server';
import type {
  AnnotatedStoryFn,
  CompatibleString,
  ComponentAnnotations,
  DecoratorFunction,
  LoaderFunction,
  ProjectAnnotations,
  StoryAnnotations,
  StorybookConfig as StorybookConfigBase,
  StoryContext as StoryContext$1,
  WebRenderer,
} from 'storybook/internal/types';

import type { BuilderOptions, StorybookConfigVite } from '@storybook/builder-vite';

type FrameworkName = CompatibleString<'@andersundsehr/storybook-typo3'>;
type BuilderName = CompatibleString<'@storybook/builder-vite'>;

export interface FrameworkOptions {
  builder?: BuilderOptions;
}

interface StorybookConfigFramework {
  framework:
    | FrameworkName
    | {
      name: FrameworkName;
      options: FrameworkOptions;
    };
  core?: StorybookConfigBase['core'] & {
    builder?:
      | BuilderName
      | {
        name: BuilderName;
        options: BuilderOptions;
      };
  };
}

/** The interface for Storybook configuration in `main.ts` files. */
export type StorybookConfig = Omit<
  StorybookConfigBase,
  keyof StorybookConfigVite | keyof StorybookConfigFramework
> &
StorybookConfigVite &
StorybookConfigFramework;

type StoryFnServerReturnType = any;

export interface FluidComponent {
  fullName: string;
  name: string;
  namespace: string;
  collection: string;
  argTypes: Record<string, any>;
}

interface FluidRenderer extends WebRenderer {
  component: FluidComponent;
  storyResult: StoryFnServerReturnType;
}

/**
 * Metadata to configure the stories for a component.
 *
 * @see [Default export](https://storybook.js.org/docs/api/csf#default-export)
 */
type Meta<TArgs = Args> = ComponentAnnotations<FluidRenderer, TArgs>;
/**
 * Story function that represents a CSFv2 component example.
 *
 * @see [Named Story exports](https://storybook.js.org/docs/api/csf#named-story-exports)
 */
type StoryFn<TArgs = Args> = AnnotatedStoryFn<FluidRenderer, TArgs>;
/**
 * Story object that represents a CSFv3 component example.
 *
 * @see [Named Story exports](https://storybook.js.org/docs/api/csf#named-story-exports)
 */
type StoryObj<TArgs = Args> = StoryAnnotations<FluidRenderer, TArgs>;

type Decorator<TArgs = StrictArgs> = DecoratorFunction<FluidRenderer, TArgs>;
type Loader<TArgs = StrictArgs> = LoaderFunction<FluidRenderer, TArgs>;
type StoryContext<TArgs = StrictArgs> = StoryContext$1<FluidRenderer, TArgs>;
type Preview = ProjectAnnotations<FluidRenderer>;

export type { Decorator, Loader, Meta, Preview, FluidRenderer, StoryContext, StoryFn, StoryObj };
