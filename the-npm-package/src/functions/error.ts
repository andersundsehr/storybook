export function error(message: string, code: number): never {
  message += ` (code: ${code})`;
  alert(message);
  throw new Error(message);
}
