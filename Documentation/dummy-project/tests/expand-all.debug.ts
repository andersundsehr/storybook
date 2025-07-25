import { test } from '@playwright/test';

export async function expandAllDebug(locator) {
  await test.step('Expand all debug sections (first level)', async () => {
    for (const expander of await locator.locator('details.extbase-debugger-tree').all()) {
      await expander.evaluate(el => el.open = true);
    }
  });
}
