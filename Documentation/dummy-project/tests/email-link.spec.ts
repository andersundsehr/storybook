import { expect, test } from '@playwright/test';

test('renders f:link.email without TYPO3 request errors', async ({ page }) => {
  await page.goto('/?path=/story/extensions-dummy-extension-components-emaillink--default');

  const frame = page.locator('iframe[title="storybook-preview-iframe"]').contentFrame();
  const story = frame.locator('#storybook-root');

  await expect(
    story.getByRole('link', { name: 'Write us' }),
    'email link should render in the preview iframe'
  ).toHaveAttribute('href', 'mailto:test@example.com', { timeout: 10_000 });
});
