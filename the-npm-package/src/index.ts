/// <reference types="vite/client" />

export * from './functions/fetchRenderAction';
export * from './functions/fetchPreviewConfig';
export * from './functions/fetchComponent';
export * from './functions/fetchWithUserRetry';
export * from './types/types';

const url = (() => {
  let url = import.meta.env.STORYBOOK_TYPO3_ENDPOINT;

  if (typeof url !== 'string' || !url) {
    throw new Error('env STORYBOOK_TYPO3_ENDPOINT is not set or is not a string');
  }

  url = url.replace(/_storybook\/?$/, '');
  url = url.replace(/\/$/, '');

  if (typeof url !== 'string' || !url) {
    throw new Error('env STORYBOOK_TYPO3_ENDPOINT is not set or is not a string');
  }
  return url;
})();

export { url };
