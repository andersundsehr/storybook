import { expect, test } from '@playwright/test';

test('test argTypes/controlls', async ({page}) => {
  await page.goto('/?path=/docs/extensions-dummy-extension-components-fullexample--docs');
  const frame = page.locator('iframe[title="storybook-preview-iframe"]').contentFrame();

  const docs = frame.locator('#storybook-docs');
  await test.step('check if the component is rendered', async () => {
    await expect(docs).toContainText('Translation: The default Header Comment. EN', {timeout: 10_000}); // storybook is not the fastest
    await frame.getByRole('button', {name: 'Show code'}).first().click();
    await expect(docs.getByText('<de:fullexample').describe('Code Block')).toBeVisible();
    await expect(frame.locator('#anchor--extensions-dummy-extension-components-fullexample--only-required').first()).toMatchAriaSnapshot({name: 'story-with-only-required-arguments.aria.yml'});
  });

  const story = frame.locator('#story--extensions-dummy-extension-components-fullexample--only-required--primary-inner');

  await test.step('check if the story is rendered', async () => {
    await frame.getByPlaceholder('Edit string...').fill('this is still required');
    await expect(story).toContainText('requiredArgument => \'this is still required\' (22 chars)');
    await expect(docs).toContainText('this is still required');
  });
  await test.step('optionalBool => FALSE', async () => {
    await frame.locator('#set-optionalBool').click();
    await expect(story).toContainText('optionalBool => FALSE');
    await expect(docs).toContainText('optionalBool="{false}"');
    await expect(docs).toContainText('optionalBool: false');
  });
  await test.step('optionalBool => TRUE', async () => {
    await frame.locator('#control-optionalBool').click();
    await expect(story).toContainText('optionalBool => TRUE');
    await expect(docs).toContainText('optionalBool="{true}"');
    await expect(docs).toContainText('optionalBool: true');
  });
  await test.step('optionalInt => 13.37', async () => {
    await frame.locator('#set-optionalInt').click();
    await frame.locator('#control-optionalInt').fill('13.37');
    await expect(story).toContainText('optionalInt => 13 (integer)');
    await expect(docs).toContainText('optionalInt="13"');
    await expect(docs).toContainText('optionalInt: 13');
  });
  await test.step('optionalFloat => 13.38', async () => {
    await frame.locator('#set-optionalFloat').click();
    await frame.locator('#control-optionalFloat').fill('13.38');
    await expect(story).toContainText('optionalFloat => 13.38 (double)');
    await expect(docs).toContainText('optionalFloat="13.38"');
    await expect(docs).toContainText('optionalFloat: 13.38');
  });
  await test.step('optionalString => Optional String', async () => {
    await frame.locator('#set-optionalString').click();
    await frame.locator('#control-optionalString').fill('Optional String');
    await expect(story).toContainText('optionalString => \'Optional String\' (15 chars)');
    await expect(docs).toContainText('optionalString="Optional String"');
    await expect(docs).toContainText('optionalString: \'Optional String\'');

  });
  await test.step('slot => <b>\'default\' "Slot" Content</b>', async () => {
    await frame.locator('#set-slot____default').click();
    await frame.locator('#control-slot____default').fill('<b>\'default\' "Slot" Content</b>');
    await expect(story).toContainText('Slot Content: \'default\' "Slot" Content');
    await expect(docs).toContainText('<b>\'default\' "Slot" Content</b>');
    await expect(docs).toContainText('{\'<b>\\\'default\\\' "Slot" Content</b>\' ->');
  });
  await test.step('stroy with slot', async () => {
    const exampleWithSlot = frame.locator('#anchor--extensions-dummy-extension-components-fullexample--with-slot');
    await exampleWithSlot.getByRole('button', {name: 'Show code'}).click();
    await expect(exampleWithSlot.getByText('<de:fullexample').describe('Code Block Visible')).toBeVisible();
    await expect(exampleWithSlot).toMatchAriaSnapshot({name: 'story-with-slot.aria.yml'});
  });
});
