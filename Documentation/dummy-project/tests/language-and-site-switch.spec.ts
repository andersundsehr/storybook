import { expect, test } from '@playwright/test';

test('test language and site switch', async ({ page }) => {
  await page.goto('/?path=/docs/extensions-dummy-extension-components-fullexample--docs');
  const frame = page
    .locator('iframe[title="storybook-preview-iframe"]')
    .contentFrame()
    .locator('#story--extensions-dummy-extension-components-fullexample--only-required--primary-inner');

  const toolbar = page.locator('[data-test-id="sb-preview-toolbar"]');

  await expect(frame, 'can render component').toContainText('Translation: The default Header Comment. EN', { timeout: 20_000 }); // storybook is not the fastest

  await test.step('switch to 🇩🇪 German', async () => {
    await expect(frame, 'translation to use correct language').toContainText('Translation: The default Header Comment. EN');
    await page.getByRole('button', { name: '🇺🇸 English' }).click();
    await page.getByRole('button', { name: '🇩🇪 German' }).click();
    await expect(toolbar, 'language switched Successfully').toContainText('🇩🇪 German');
    await expect(frame, 'translation to use correct language').toContainText('Translation: Der Standard-Header-Kommentar. DE');
  });

  await test.step('switch to 🇦🇹 German - Austria', async () => {
    await page.getByRole('button', { name: '🇩🇪 German' }).click();
    await page.getByRole('button', { name: '🇦🇹 German - Austria' }).click();
    await expect(toolbar, 'language switched Successfully').toContainText('🇦🇹 German - Austria');
    await expect(frame, 'translation to use correct language').toContainText('Translation: Der Standard-Header-Kommentar. DE-AT');
  });

  await test.step('switch to 🌐 second-page and than to 🇫🇷 French', async () => {
    await page.getByRole('button', { name: '🇦🇹 German - Austria' }).click();
    await expect(page.getByRole('button', { name: '🇫🇷 French' })).not.toBeVisible();

    await page.getByRole('button', { name: '🌐 main' }).click();
    await page.getByRole('button', { name: '🌐 The Second is always the best' }).click();
    await expect(toolbar, 'site switched Successfully').toContainText('🌐 The Second is always the best');
    await expect(frame, 'translation to use correct language').toContainText('Translation: The default Header Comment. EN');

    await page.getByRole('button', { name: '🇺🇸 English' }).click(); // expect it switched back to English because German - Austria is not available on second-page
    await page.getByRole('button', { name: '🇫🇷 French' }).click();
    await expect(frame, 'translation to use correct language').toContainText('Translation: Le commentaire d\'en-tête par défaut. FR');
  });
});
