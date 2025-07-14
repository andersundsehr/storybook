import { expect, test, type Page } from '@playwright/test';

test.use({
  colorScheme: 'dark', // or 'light'
});

async function removeUpgradeNotification(page:Page){
  await page.addLocatorHandler(page.getByRole('button', { name: 'Dismiss notification' }), async () => {
    await page.getByRole('button', { name: 'Dismiss notification' }).click();
  });
}

test('card-example', async ({page}) => {
  await page.setViewportSize({width: 1280, height: 1400});

  await page.goto('http://localhost:8080/?path=/docs/extensions-dummy-extension-components-card--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');
  await page.locator('iframe[title="storybook-preview-iframe"]').contentFrame().getByRole('button', { name: 'Show code' }).first().click();
  await page.waitForTimeout(300); // wait for the code to be shown

  await expect(page).toHaveScreenshot({maxDiffPixelRatio: 0.02});
});

test('screenshot-overview', async ({page}) => {
  await page.setViewportSize({width: 1280, height: 1750});

  await page.goto('http://localhost:8080/?path=/docs/extensions-dummy-extension-components-fullexample--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');
  await page.locator('iframe[title="storybook-preview-iframe"]').contentFrame().getByRole('button', { name: 'Show code' }).first().click();
  await page.waitForTimeout(300); // wait for the code to be shown

  await expect(page).toHaveScreenshot({maxDiffPixelRatio: 0.02});
});

test('screenshot-site-and-language', async ({page}) => {
  await page.setViewportSize({width: 400, height: 200});

  await page.goto('http://localhost:8080/?path=/docs/extensions-dummy-extension-components-fullexample--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');

  await test.step('select site', async () => {
    await page.getByRole('button', {name: '🌐 main http://web'}).click();
    await expect(page).toHaveScreenshot( 'screenshot-select-site.png', {maxDiffPixelRatio: 0.02});
  });
  await test.step('select language', async () => {
    await page.getByRole('button', {name: '🇺🇸 English'}).click();
    await expect(page).toHaveScreenshot( 'screenshot-select-language.png', {maxDiffPixelRatio: 0.02});
  });
});
