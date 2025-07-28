import { test, type TestContext } from 'node:test';
import { convertComponentToSource } from './convertComponentToSource.ts';
import type { FluidComponent } from '../types/types.ts';
import type { StrictArgs } from 'storybook/internal/csf';

interface TestCase { component: FluidComponent; args: StrictArgs; expected: string }

const component = { name: 'test', fullName: 'c:test', argTypes: {}, collection: 'Class', namespace: 'c' };
const testCases: Record<string, TestCase> = {
  ///////////////////////////////////////////
  withoutAnyProperties: {
    component,
    args: {},
    expected: `<html
  xmlns:c="http://typo3.org/ns/Class"
  data-namespace-typo3-fluid="true"
>

<c:test/>

<!-- or -->

{namespace c=Class}

{c:test()}`,
  },
  ///////////////////////////////////////////
  withSimpleArguments: {
    component,
    args: { arg1: 'value1', arg2: 'value2' },
    expected: `<html
  xmlns:c="http://typo3.org/ns/Class"
  data-namespace-typo3-fluid="true"
>

<c:test arg1="value1" arg2="value2"/>

<!-- or -->

{namespace c=Class}

{c:test(arg1: 'value1', arg2: 'value2')}`,
  },
  ///////////////////////////////////////////
  withSlot: {
    component,
    args: { arg1: 'value\'"1', arg2: 'value2', slot____default: 'default \'"slot' },
    expected: `<html
  xmlns:c="http://typo3.org/ns/Class"
  data-namespace-typo3-fluid="true"
>

<c:test arg1="value'\\"1" arg2="value2">
  default '"slot
</c:test>

<!-- or -->

{namespace c=Class}

{'default \\'"slot' -> c:test(arg1: 'value\\'"1', arg2: 'value2')}`,
  },
  ///////////////////////////////////////////
  withMultipleSlots: {
    component,
    args: { slot____a: 'slotA', slot____b: 'slotB', slot____default: 'slotDefault' },
    expected: `<html
  xmlns:c="http://typo3.org/ns/Class"
  data-namespace-typo3-fluid="true"
>

<c:test>
  <f:fragment name="a">slotA</f:fragment>
  <f:fragment name="b">slotB</f:fragment>
  <f:fragment name="default">slotDefault</f:fragment>
</c:test>`,
  },
  ///////////////////////////////////////////
  withTransformedArgument: {
    component,
    args: { arg1__argA: 'value1', arg1__argB: 'value2' },
    expected: `<html
  xmlns:c="http://typo3.org/ns/Class"
  data-namespace-typo3-fluid="true"
>

<c:test arg1="{arg1}"/>

<!-- or -->

{namespace c=Class}

{c:test(arg1: arg1)}`,
  },
  ///////////////////////////////////////////
  longLines: {
    component,
    args: { arg1: 'veryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValue', arg2: 'veryLongValueveryLongValueveryLongValueveryLongValueveryLongValue' },
    expected: `<html
  xmlns:c="http://typo3.org/ns/Class"
  data-namespace-typo3-fluid="true"
>

<c:test 
  arg1="veryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValue"
  arg2="veryLongValueveryLongValueveryLongValueveryLongValueveryLongValue"
/>

<!-- or -->

{namespace c=Class}

{c:test(
  arg1: 'veryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValueveryLongValue',
  arg2: 'veryLongValueveryLongValueveryLongValueveryLongValueveryLongValue'
)}`,
  },
  ///////////////////////////////////////////
};

for (const [name, { component, args, expected }] of Object.entries(testCases)) {
  test(`convertComponentToSource - ${name}`, (t: TestContext) => {
    const result = convertComponentToSource(component, args);
    t.assert.strictEqual(result, expected);
  });
}
