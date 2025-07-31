import { expect, test } from '@playwright/test';

test('test argTypes/controls', async ({ page }) => {
  await page.goto('/?path=/story/extensions-dummy-extension-components-slotexample--docs');
  const frame = page.locator('iframe[title="storybook-preview-iframe"]').contentFrame();

  const docs = frame.locator('#storybook-docs');

  await test.step('shows multiple slots in the documentation', async () => {
    const argControls = docs.locator('.docblock-argstable');
    await expect(argControls).toContainText('Slot content for default');
    await expect(argControls).toContainText('Slot content for button');
    await expect(argControls).toContainText('Slot content for third');
    await expect(argControls).toHaveScreenshot();
  });
});
