import type { FluidComponent } from '../types/types';
import type { StrictArgs } from 'storybook/internal/csf';

function convertValueToString(key: string, argType: any, value: unknown, inline: boolean): string {
  const innerCall = () => {
    const valueAsString = String(value);
    if (typeof value === 'boolean') {
      if (inline) {
        return valueAsString;
      }
      return '{' + valueAsString + '}';
    }
    if (typeof value === 'number') {
      if (argType.type.name === 'int') {
        return String(Number.parseInt(valueAsString, 10)); // Convert to integer for inline code
      }
      return String(Number.parseFloat(valueAsString)); // Convert to integer for inline code
    }
    if (inline) {
      return '\'' + valueAsString.replace(/'/g, '\\\'') + '\''; // Escape single quotes for inline code
    }
    return valueAsString.replace(/"/g, '\\"'); // Escape double quotes for attributes
  };

  if (inline) {
    return key + ': ' + innerCall();
  }
  return key + '="' + innerCall() + '"';
}

function inlineCode(usedArgs: string[], args: StrictArgs, component: FluidComponent, defaultSlotContent?: string) {
  const argsString = usedArgs.map(key => convertValueToString(key, component.argTypes[key], args[key], true)).join(', ');

  if (defaultSlotContent) {
    defaultSlotContent = defaultSlotContent.replace(/'/g, '\\\''); // Escape single quotes for inline code
    return `{'${defaultSlotContent}' -> ${component.fullName}(${argsString})'}`;
  }

  return `{${component.fullName}(${argsString})}`;
}

function generateOpenTag(usedArgs: string[], args: StrictArgs, component: FluidComponent) {
  const argStrings = usedArgs.map(key => convertValueToString(key, component.argTypes[key], args[key], false));
  let argsString = argStrings.join(' ');
  let openTag = `<${component.fullName} ${argsString}`;
  if (openTag.length > 80) {
    argsString = argStrings.join('\n  ');
    openTag = `<${component.fullName} \n  ${argsString}\n`;
  }
  return openTag;
}

export function convertComponentToSource(component: FluidComponent, args: StrictArgs): string {
  // TODO handle other types Object? Date? ...
  // TODO handle transformation of args to string: if arg has (.*)__ prefix it is a virtual argument and should result in $1="{$1}" instead of $1_$2="valueof$2"

  let source = '';
  source += `<html\n  xmlns:${component.namespace}="http://typo3.org/ns/${component.collection.replace(/\\/g, '/')}"\n  data-namespace-typo3-fluid="true"\n>\n\n`;

  const usedArgs = Object.keys(args).filter(key => !key.startsWith('slot____'));
  const slots = Object.keys(args).filter(key => key.startsWith('slot____'))
    .filter((slot) => {
      const slotValue = args[slot];
      return slotValue !== '' && slotValue !== null && slotValue !== undefined;
    });

  source += generateOpenTag(usedArgs, args, component);

  if (slots.length <= 0) {
    source += `/>\n`;
    source += `\n<!-- or -->\n\n`;
    source += `{namespace ${component.namespace}=${component.collection}}\n\n`;
    source += inlineCode(usedArgs, args, component);
    return source;
  }
  source += '>\n';

  if (slots.length === 1 && slots[0] === 'slot____default') {
    // If there is only a default slot, we can use the inline code
    source += `  ${args.slot____default}\n`;
    source += '</' + component.fullName + '>\n';
    source += `\n<!-- or -->\n\n`;
    source += inlineCode(usedArgs, args, component, args.slot____default ?? '');
    return source;
  }

  for (const slot of slots) {
    const slotName = slot.replace('slot____', '');
    const slotValue = args[slot];
    source += `  <f:fragment name="${slotName}">${slotValue}</f:fragment>\n`;
  }
  source += '</' + component.fullName + '>';

  return source;
}
