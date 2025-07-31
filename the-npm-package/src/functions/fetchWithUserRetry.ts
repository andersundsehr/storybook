/*
We want to make it obvious for the user what he has to do.

We have 2 different usecases:
- background fetches (componentMeta & preview)
- html Fetches

most of the time the user has to do something in there IDE to fix the problem.
So we want to show a message that tells the user what he has to do.

In the case of a background fetch, we want to show the full error in the console.
And give the user a alert with the error message and a retry button.

In the case of a html fetch, we want to show the error as html response?
- yes: because the user can see the error in the browser and can fix it
- no: because storybook dose not understand that it is an error

In any case we should get the error in json so we can handle it in the frontend as we like.
Some times it can still be text/html so we need to handle that as well.
 */

export async function fetchWithUserRetry<T>(url: string, options: RequestInit, message: string, result: 'json' | 'text' = 'json'): Promise<T> {
  options = { ...options }; // Clone options to avoid mutating the original
  options.signal = options.signal || AbortSignal.timeout(5000);
  try {
    const response = await fetch(url, options);
    if (!response.ok) {
      // let responseBody = await response.text();
      // if (responseBody) {
      //   responseBody += '\n';
      // }
      // throw new Error(`${responseBody}HTTP ${response.status}\nURL: ${url}\nError while fetching ${message}`);
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
