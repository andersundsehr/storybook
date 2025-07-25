import { expect, test } from '@playwright/test';
import { expandAllDebug } from './expand-all.debug';

test('test argTypes/controlls', async ({ page }) => {
  await page.goto('/?path=/docs/extensions-dummy-extension-components-transformerexample--docs');
  const contentFrame = page.locator('iframe[title="storybook-preview-iframe"]').contentFrame();

  const output = contentFrame.locator('#anchor--extensions-dummy-extension-components-transformerexample--transform').locator('.innerZoomElementWrapper > div');
  await expect(output).toContainText('applicationType', { timeout: 10_000 }); // sometimes it is slow :/
  await expandAllDebug(contentFrame);
  await expect(output).toMatchAriaSnapshot({ name: 'inital.aria.yml' });

  await contentFrame.locator('#control-applicationType').selectOption('::BACKEND=backend');
  await contentFrame.locator('#control-dateTimeInterface-date').fill('2025-02-17');
  await contentFrame.locator('#control-dateTimeInterface-time').click();
  await contentFrame.locator('#control-uri__url').fill('https://storybook.example.com/_storybook/preview?ncccc');
  await contentFrame.locator('#control-websocket__url').fill('github.com');
  await contentFrame.locator('#control-contextIcon__severity').selectOption('::OK=0');
  await contentFrame.locator('#control-combineUri__host').fill('andersundsehr.com');
  await contentFrame.locator('#control-combineUri__path').fill('index.php');
  await contentFrame.locator('#set-combineUri__query').click();
  await contentFrame.locator('#control-combineUri__query').fill('Q');
  await contentFrame.locator('#set-combineUri__fragment').click();
  await contentFrame.locator('#control-combineUri__fragment').fill('F');
  await contentFrame.locator('#set-combineUri__scheme').click();
  await contentFrame.locator('#control-combineUri__scheme').fill('http');

  await expandAllDebug(contentFrame);
  await expect(output).toMatchAriaSnapshot({ name: 'after-changes.aria.yml' });
});
