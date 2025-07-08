export async function fetchWithUserRetry(url: string, options: RequestInit, message: string): Promise<Response> {
  options = { ...options }; // Clone options to avoid mutating the original
  options.signal = options.signal || AbortSignal.timeout(5000);
  try {
    return await fetch(url, options);
  } catch (error) {
    console.error('Fetch failed:', { error });
    const retry = confirm(
      'Error while fetching ' + message + '. \n\n'
      + 'fetch: ' + url + '\n'
      + 'ERROR: ' + String(error) + '\n\n'
      + 'look at the console for more details.\n\n'
      + 'Do you want to retry?',
    );
    if (retry) {
      return fetchWithUserRetry(url, options, message);
    }
    throw error; // Re-throw the error if the user does not want to retry
  }
}
