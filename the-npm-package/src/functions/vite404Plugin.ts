import type { PluginOption, ViteDevServer } from 'vite';
import { createProxyServer } from 'http-proxy-3';
import shimmer from 'shimmer';

export function vite404Plugin(url: string): PluginOption {
  return {
    name: 'proxy-typo3',
    configureServer(server: ViteDevServer) {
      const colorYellow = '\x1b[33m';
      const colorReset = '\x1b[0m';
      console.log(colorYellow + '@andersundsehr/storybook-typo3:' + colorReset + ' vite404Plugin enabled, proxying requests to TYPO3 at ' + url);
      const proxyServer = createProxyServer({
        target: url,
        changeOrigin: true,
      });
      server.middlewares.use((req, res, next) => {
        shimmer.massWrap(
          [res],
          ['writeHead', 'end'],
          (original) =>
            function <X>(this: X) {
              if (handled404()) {
                return this; // Prevent further processing if 404 is handled
              }
              // @ts-ignore
              original.call(this, ...arguments);
              return this;
            },
        );

        const handled404 = () => {
          if (res.statusCode !== 404) {
            return false;
          }
          console.log('😁 Proxying request: ' + req.method + ' ' + req.url);
          // res.writeHead = origWriteHead;
          // res.end = origEnd;
          shimmer.massUnwrap([res], ['writeHead', 'end']);

          // TODO handle websocket requests, but how?
          // => Or we can use one vite server for the TYPO3 backend and one for storybook. How would that look like?
          proxyServer.web(req, res);
          return true;
        };

        next();
      });
    },
  };
}
