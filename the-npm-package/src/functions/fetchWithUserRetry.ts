/*
We want to make it obvious for the user what he has to do.

We have 2 different use cases:
- background fetches (componentMeta & preview)
- html Fetches

most of the time the user has to do something in their IDE to fix the problem.
So we want to show a message that tells the user what he has to do.

In the case of a background fetch, we want to show the full error in the console.
And give the user a alert with the error message and a retry button.

In the case of a html fetch, we want to show the error as html response?
- yes: because the user can see the error in the browser and can fix it
- no: because storybook does not understand that it is an error

In any case we should get the error in json so we can handle it in the frontend as we like.
Sometimes it can still be text/html so we need to handle that as well.
 */

type PossibleResults = string | object;

export async function fetchWithUserRetry<T extends PossibleResults>(url: string, options: RequestInit, message: string, resultType: 'json' | 'text' = 'json'): Promise<T> {
  try {
    options = { ...options }; // Clone options to avoid mutating the original
    options.signal = options.signal || AbortSignal.timeout(5000);
    const response = await fetch(url, options);
    if (!response.ok) {
      return retry<T>(response, url, options, message, resultType);
    }
    return (await response[resultType]()) as T;
  } catch (error) {
    return retry(error, url, options, message, resultType);
  }
}


async function retry<T extends PossibleResults>(errorOrResponse: unknown, url: string, options: RequestInit, message: string, resultType: 'json' | 'text'): Promise<T> {
  const errorType = await getErrorExplanation(errorOrResponse);
  let exception = errorOrResponse instanceof Error ? errorOrResponse : undefined;

  let confirmationMessage = '';

  if (errorType.errorType === 'network') {
    confirmationMessage = `🛜+🚫 A network error occurred while fetching ${message} from TYPO3:\n\n`
      + `💬 ${errorType.message}\n\n`
      + `⏩ Please check your network connection and try again.\n\n`
      + `🔗 url: ${url}\n\n`;
    exception = new Error(`Network error: ${errorType.message}`);
  } else if (errorType.errorType === 'extension') {
    if (resultType === 'text') {
      // no retry via confirm()
      return errorType.errorHtml as T;
    }
    confirmationMessage = `💥 An error occurred while fetching ${message} from TYPO3:\n\n`
      + `💬 ${errorType.reason}\n\n`
      + `${errorType.stackTrace ? `🕵🏻‍♂️ Stack trace: ${errorType.stackTrace}\n\n` : ''}`
      + `🔗 url: ${url}\n\n`;
    exception = new Error(`Extension error: ${errorType.reason}`);
  } else {
    if (resultType === 'text' && errorType.message) {
      // no retry via confirm()
      return errorType.message as T;
    }
    // unknown error
    confirmationMessage = `😳 An error occurred while fetching ${message} from TYPO3:\n\n`
      + `💬 ${errorType.message}\n\n`
      + `#️⃣ Status code: ${errorType.statusCode || 'unknown'}\n\n`
      + `🔗 url: ${url}\n\n`;
  }

  confirmationMessage = confirmationMessage.trim();
  confirmationMessage = confirmationMessage.length > 700 ? confirmationMessage.substring(0, 700 - 3) + '\n…' : confirmationMessage;
  const retry = confirm(confirmationMessage + '\n\n Do you want to retry 🔄❓');
  if (retry) {
    options.signal = undefined;
    return fetchWithUserRetry<T>(url, options, message, resultType);
  }
  throw exception || new Error(`Failed to fetch ${message} from TYPO3: ${JSON.stringify(errorType)}`);
}

type ErrorResult =
  | {
      errorType: 'unknown';
      statusCode?: number;
      message: string;
    }
  | {
      errorType: 'network';
      message: string;
    }
  | {
      errorType: 'extension';
      errorHtml: string;
      reason: string;
      stackTrace: string;
    };

async function getErrorExplanation(errorOrResponse: unknown): Promise<ErrorResult> {
  if (errorOrResponse instanceof Response) {
    let text: string | undefined = undefined;

    try {
      text = await errorOrResponse.text();
    } catch (e) {
      return {
        errorType: 'unknown',
        message: `Failed to read response from TYPO3: ${errorOrResponse.status}\n ${errorOrResponse.statusText}`,
      };
    }

    try {
      const extensionError = JSON.parse(text);
      if (extensionError && typeof extensionError === 'object' && 'errorType' in extensionError && extensionError.errorType === 'extension') {
        return extensionError as ErrorResult;
      }
      return {
        errorType: 'unknown',
        statusCode: errorOrResponse.status,
        message: `Received an unexpected response from TYPO3: ${errorOrResponse.status}\n ${JSON.stringify(extensionError)}`,
      };
    } catch (e) {
      console.warn('⁉️ Failed to parse JSON from TYPO3 response:', e);
    }

    return {
      errorType: 'unknown',
      message: `Received an unexpected response from TYPO3: ${errorOrResponse.status}\n ${text}`,
    };
  }

  if (errorOrResponse instanceof Error) {
    return {
      errorType: 'network',
      message: errorOrResponse.message,
    };
  }
  if (typeof errorOrResponse === 'string') {
    return {
      errorType: 'unknown',
      message: errorOrResponse,
    };
  }
  return {
    errorType: 'unknown',
    message: `An unknown error occurred: ${JSON.stringify(errorOrResponse) || ''}`,
  };
}
