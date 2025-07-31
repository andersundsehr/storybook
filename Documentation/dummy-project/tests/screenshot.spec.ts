import { expect, type Page, test } from '@playwright/test';
import { execSync } from 'node:child_process';

test.use({
  colorScheme: 'dark', // or 'light'
});

async function removeUpgradeNotification(page: Page) {
  await page.addLocatorHandler(page.getByRole('button', { name: 'Dismiss notification' }), async () => {
    await page.getByRole('button', { name: 'Dismiss notification' }).click();
  });
}

test('card-example', async ({ page }) => {
  await page.setViewportSize({ width: 1280, height: 1500 });

  await page.goto('/?path=/docs/extensions-dummy-extension-components-card--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');
  await page.locator('iframe[title="storybook-preview-iframe"]').contentFrame().getByRole('button', { name: 'Show code' }).first().click();
  await page.waitForTimeout(300); // wait for the code to be shown

  await expect(page).toHaveScreenshot({ maxDiffPixelRatio: 0.01 });
});
test('empty-card-story', async ({ page }) => {
  await page.setViewportSize({ width: 1280, height: 800 });

  await page.goto('/?path=/story/extensions-dummy-extension-components-card-empty--story-1');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');

  const frame = page.locator('iframe[title="storybook-preview-iframe"]').contentFrame();
  const output = frame.locator('#storybook-root');
  await expect(output).toContainText('The argument "title" for template "Default_action_Card_Card_');
  await expect(page.locator('#set-title')).toBeVisible();

  await test.step('make paths relative for screenshot', async () => {
    await output.locator('pre').evaluate((el: HTMLElement) => {
      // find out the absolute path
      const absolutePath = el.textContent?.match(/#0\s+(.*)dummy-project\//);
      if (absolutePath) {
        // replace the absolute path with a relative path
        const newText = el.textContent?.replaceAll(absolutePath[1], '');
        el.textContent = newText ?? '';
      }
    });
  });

  await expect(page).toHaveScreenshot({ maxDiffPixelRatio: 0.01 });

  await test.step('set required arguments', async () => {
    await page.locator('#set-title').click();
    await page.locator('#control-title').type('This is a new title');
    await page.locator('#set-text').click();
    await page.locator('#control-text').type('This is some new text, a description for the card.');
    await page.locator('#set-link').click();
    await page.locator('#control-link').type('https://www.andersundsehr.com');
  });

  await page.waitForTimeout(500); // wait for the iframe to update loading
  await page.waitForLoadState('networkidle');

  await page.getByRole('button', { name: 'Save changes to story' }).hover();

  await expect(page).toHaveScreenshot({ maxDiffPixelRatio: 0.01 });
});

test('screenshot-overview', async ({ page }) => {
  await page.setViewportSize({ width: 1280, height: 1900 });

  await page.goto('/?path=/docs/extensions-dummy-extension-components-fullexample--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');
  await page.locator('iframe[title="storybook-preview-iframe"]').contentFrame().getByRole('button', { name: 'Show code' }).first().click();
  await page.waitForTimeout(300); // wait for the code to be shown

  await expect(page).toHaveScreenshot({ maxDiffPixelRatio: 0.01 });
});

test('transformer-example', async ({ page }) => {
  await page.setViewportSize({ width: 1850, height: 2800 });

  await page.goto('/?path=/docs/extensions-dummy-extension-components-transformerexample--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');
  await page.locator('iframe[title="storybook-preview-iframe"]').contentFrame().getByRole('button', { name: 'Show code' }).first().click();
  await page.waitForTimeout(300); // wait for the code to be shown

  await expect(page).toHaveScreenshot({ maxDiffPixelRatio: 0.01 });
});

test('screenshot-site-and-language', async ({ page }) => {
  await page.setViewportSize({ width: 400, height: 200 });

  await page.goto('/?path=/docs/extensions-dummy-extension-components-fullexample--docs');
  await removeUpgradeNotification(page);
  await page.waitForTimeout(1_000); // wait for the iframe to start loading
  await page.waitForLoadState('networkidle');

  await test.step('select site', async () => {
    await page.getByRole('button', { name: '🌐 main http://web' }).click();
    await expect(page).toHaveScreenshot('screenshot-select-site.png', { maxDiffPixelRatio: 0.01 });
  });
  await test.step('select language', async () => {
    await page.getByRole('button', { name: '🇺🇸 English' }).click();
    await expect(page).toHaveScreenshot('screenshot-select-language.png', { maxDiffPixelRatio: 0.01 });
  });
});
