export async function fetchWithUserRetry<T>(url: string, options: RequestInit, message: string, result: 'json' | 'text' = 'json'): Promise<T> {
  options = { ...options }; // Clone options to avoid mutating the original
  options.signal = options.signal || AbortSignal.timeout(5000);
  try {
    const response = await fetch(url, options);
    if (!response.ok) {
      let responseBody = await response.text();
      if (responseBody) {
        responseBody += '\n';
      }
      throw new Error(`${responseBody}HTTP ${response.status}\nURL: ${url}\nError while fetching ${message}`);
    }
    if (result === 'text') {
      return await response.text() as T;
    }
    return await response.json() as T;
  } catch (error) {
    console.error('Fetch failed:', { error });
    const retry = confirm(''
      + 'ERROR: ' + String(error) + '\n\n\n'
      + 'Error while fetching ' + message + '. \n\n'
      + 'fetch: ' + url + '\n\n'
      + 'look at the console for more details.\n\n'
      + 'Do you want to retry?',
    );
    if (retry) {
      options.signal = undefined; // Reset the signal to avoid reusing the same AbortSignal
      return fetchWithUserRetry(url, options, message);
    }
    throw error; // Re-throw the error if the user does not want to retry
  }
}
