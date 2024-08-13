import { fetch as fetchPolyfill } from 'whatwg-fetch'; // polyfill for IE

if (!window.fetch) {
  window.fetch = fetchPolyfill;
}
