import { expect, test } from '@playwright/test';

test('test argTypes/controls', async ({ page }) => {
  await page.goto('/?path=/docs/extensions-dummy-extension-components-transformerexample--docs');
  const contentFrame = page.locator('iframe[title="storybook-preview-iframe"]').contentFrame();
  // Storybook 10 prefixes the ids of the controls in the docs with a generated id and the story id
  const control = (name: string) => contentFrame.locator(`[id^="control-"][id$="--transform-${name}"]`);
  const set = (name: string) => contentFrame.locator(`[id^="set-"][id$="--transform-${name}"]`);

  const output = contentFrame.locator('#anchor--primary--extensions-dummy-extension-components-transformerexample--transform').locator('.innerZoomElementWrapper > div');
  await expect(output).toContainText('applicationType', { timeout: 10_000 }); // sometimes it is slow :/
  await expect(output).toMatchAriaSnapshot({ name: 'inital.aria.yml' });

  await test.step('change controls', async () => {
    await contentFrame.getByRole('radio', { name: '::BACKEND=backend' }).check();
    await control('dateTimeInterface-date').fill('2025-02-17');
    await control('dateTimeInterface-time').click();
    await control('uri__url').fill('https://storybook.example.com/_storybook/preview?ncccc');
    await control('websocket__url').fill('github.com');
    await contentFrame.getByRole('radio', { name: '::OK=0' }).check();
    await control('combineUri__host').fill('andersundsehr.com');
    await control('combineUri__path').fill('index.php');
    await set('combineUri__query').click();
    await control('combineUri__query').fill('Q');
    await set('combineUri__fragment').click();
    await control('combineUri__fragment').fill('F');
    await set('combineUri__scheme').click();
    await control('combineUri__scheme').fill('http');
    await set('typolink__url').click();
    await control('typolink__url').fill('github.com/andersundsehr/storybook-typo3');
    await set('file__extPath').click();
    await control('file__extPath').fill('EXT:storybook/Resources/Public/Icons/Extension.svg');
    await set('fileInterface__extPath').click();
    await control('fileInterface__extPath').fill('EXT:storybook/Resources/Public/Icons/Extension.svg');
    await set('fileWithDefault__extPath').click();
    await control('fileWithDefault__extPath').fill('EXT:storybook/Resources/Public/Icons/Extension.svg');
  });

  await expect(output).toMatchAriaSnapshot({ name: 'after-changes.aria.yml' });
});
