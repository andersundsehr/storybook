import { test } from '@playwright/test';

test.use({
  colorScheme: 'dark', // or 'light'
});

test('screenshot-overview', async ({page}) => {
  await page.setViewportSize({width: 1280, height: 1750});

  await page.goto('http://localhost:8080/?path=/docs/extensions-dummy-extension-components-simpleexample--docs');
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');
  await page.locator('iframe[title="storybook-preview-iframe"]').contentFrame().getByRole('button', { name: 'Show code' }).first().click();
  await page.waitForTimeout(300); // wait for the code to be shown
  await page.screenshot({path: '../../Documentation/assets/screenshot-overview.png'});
});

test('screenshot-site-and-language', async ({page}) => {
  await page.setViewportSize({width: 400, height: 200});

  await page.goto('http://localhost:8080/?path=/docs/extensions-dummy-extension-components-simpleexample--docs');
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');

  await test.step('select site', async () => {
    await page.getByRole('button', {name: '🌐 main http://web'}).click();
    await page.screenshot({path: '../../Documentation/assets/screenshot-select-site.png'});
  });
  await test.step('select language', async () => {
    await page.getByRole('button', {name: '🇺🇸 English'}).click();
    await page.screenshot({path: '../../Documentation/assets/screenshot-select-language.png'});
  });
});
