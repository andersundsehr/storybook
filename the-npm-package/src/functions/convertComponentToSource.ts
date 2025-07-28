import type { FluidComponent } from '../types/types';
import type { StrictArgs } from 'storybook/internal/csf';

function convertValueToString(key: string, argType: { type?: { name?: 'int' } }, value: ArgumentValue, inline: boolean): string {
  const createValue = () => {
    if ('variableName' in value) {
      if (inline) {
        return value.variableName; // For inline code, just return the variable name
      }
      return '{' + value.variableName + '}'; // For attributes, return the variable name wrapped in curly braces
    }
    const valueAsString = String(value.value);
    if (typeof value.value === 'boolean') {
      if (inline) {
        return valueAsString;
      }
      return '{' + valueAsString + '}';
    }
    if (typeof value.value === 'number') {
      if (argType?.type?.name === 'int') {
        return String(Number.parseInt(valueAsString, 10)); // Convert to integer
      }
      return String(Number.parseFloat(valueAsString)); // Convert to float
    }
    if (inline) {
      return '\'' + valueAsString.replace(/'/g, '\\\'') + '\''; // Escape single quotes for inline code
    }
    return valueAsString.replace(/"/g, '\\"'); // Escape double quotes for attributes
  };

  if (inline) {
    return key + ': ' + createValue();
  }
  return key + '="' + createValue() + '"';
}

function inlineCode(viewHelperArguments: Arguments, component: FluidComponent, defaultSlotContent?: string) {
  const argsStrings: string[] = Object.entries(viewHelperArguments)
    .map(([argumentName, argumentValue]) => convertValueToString(argumentName, component.argTypes[argumentName], argumentValue, true));
  const argsString = argsStrings.join(', ');

  let source = '{';

  if (defaultSlotContent) {
    defaultSlotContent = defaultSlotContent.replace(/'/g, '\\\''); // Escape single quotes for inline code
    source += `'${defaultSlotContent}' -> `;
  }
  source += `${component.fullName}(`;
  if ((source.length + argsString.length) > 80) {
    source += `\n  ${argsStrings.join(',\n  ')}\n`;
  } else {
    source += argsString;
  }

  source += ')}';
  return source;
}

function generateOpenTag(viewHelperArguments: Arguments, component: FluidComponent) {
  const argStrings: string[] = Object.entries(viewHelperArguments)
    .map(([argumentName, argumentValue]) => convertValueToString(argumentName, component.argTypes[argumentName], argumentValue, false));
  let argsString = argStrings.join(' ');
  let openTag = `<${component.fullName}`;
  if (argsString.length > 0) {
    openTag += ' ' + argsString;
  }
  if (openTag.length > 80) {
    argsString = argStrings.join('\n  ');
    openTag = `<${component.fullName} \n  ${argsString}\n`;
  }
  return openTag;
}

const SLOT_PREFIX = 'slot____';

type ArgumentValue = {
  value: unknown;
} | {
  variableName: string;
};

type Arguments = Record<string, ArgumentValue>;
type Slots = Record<string, string>;
interface ComponentData { viewHelperArguments: Arguments; slots: Slots }

function createComponentData(component: FluidComponent, args: StrictArgs): ComponentData {
  const viewHelperArguments: Arguments = {};
  const slots: Slots = {};

  for (const [key, value] of Object.entries(args)) {
    if (key.startsWith(SLOT_PREFIX)) {
      // This is a slot
      const slotName = key.replace(SLOT_PREFIX, '');
      if (value === null || value === undefined || value === '') {
        continue;
      }

      slots[slotName] = value as string;
      continue;
    }

    if (!key.includes('__')) {
      // This is a normal argument without virtual subkey
      viewHelperArguments[key] = { value };
      continue;
    }

    // This is a virtual argument
    const [variableName] = key.split('__');
    viewHelperArguments[variableName] = { variableName };
  }

  return { viewHelperArguments, slots };
}

export function convertComponentToSource(component: FluidComponent, args: StrictArgs): string {
  const { viewHelperArguments, slots } = createComponentData(component, args);

  let source = '';
  source += `<html\n  xmlns:${component.namespace}="http://typo3.org/ns/${component.collection.replace(/\\/g, '/')}"\n  data-namespace-typo3-fluid="true"\n>\n\n`;

  const usedArgs: string[] = [...new Set(Object.keys(args).filter(key => !key.startsWith(SLOT_PREFIX)).map(key => key.split('__')[0]))];

  source += generateOpenTag(viewHelperArguments, component);

  if (Object.keys(slots).length <= 0) {
    source += `/>\n`;
    source += `\n<!-- or -->\n\n`;
    source += `{namespace ${component.namespace}=${component.collection}}\n\n`;
    source += inlineCode(viewHelperArguments, component);
    return source;
  }
  source += '>\n';

  if (Object.keys(slots).length === 1 && slots.default) {
    // If there is only a default slot, we can use the inline code
    source += `  ${slots.default}\n`;
    source += '</' + component.fullName + '>\n';
    source += `\n<!-- or -->\n\n`;
    source += `{namespace ${component.namespace}=${component.collection}}\n\n`;
    source += inlineCode(viewHelperArguments, component, slots.default);
    return source;
  }

  for (const [slotName, slotValue] of Object.entries(slots)) {
    source += `  <f:fragment name="${slotName}">${slotValue}</f:fragment>\n`;
  }
  source += '</' + component.fullName + '>';

  return source;
}
