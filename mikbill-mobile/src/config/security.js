/**
 * MikBill Mobile — Security Configuration
 * 
 * All security policies for the WebView app.
 * Only the whitelisted domain over HTTPS is allowed.
 */

// The base URL of your MikBill server
export const BASE_URL = 'https://pay.billnesia.com';

// Allowed domains — only these can be loaded in the WebView
export const ALLOWED_DOMAINS = [
    'pay.billnesia.com',
];

// Allowed URL schemes
export const ALLOWED_SCHEMES = ['https:'];

// URLs that should open in external browser (none by default for security)
export const EXTERNAL_LINK_PATTERNS = [];

/**
 * Check if a URL is safe to load in the WebView
 * @param {string} url - The URL to validate
 * @returns {boolean} - Whether the URL is allowed
 */
export function isUrlAllowed(url) {
    try {
        // Allow about:blank and data URIs for internal WebView use
        if (url === 'about:blank' || url.startsWith('data:')) {
            return true;
        }

        const parsed = new URL(url);

        // Enforce HTTPS only
        if (!ALLOWED_SCHEMES.includes(parsed.protocol)) {
            console.warn(`[Security] Blocked non-HTTPS URL: ${url}`);
            return false;
        }

        // Check domain whitelist
        const hostname = parsed.hostname.toLowerCase();
        const isAllowed = ALLOWED_DOMAINS.some(
            (domain) => hostname === domain || hostname.endsWith('.' + domain)
        );

        if (!isAllowed) {
            console.warn(`[Security] Blocked external domain: ${hostname}`);
            return false;
        }

        return true;
    } catch (e) {
        console.warn(`[Security] Invalid URL blocked: ${url}`);
        return false;
    }
}

/**
 * Check if URL should open externally
 * @param {string} url
 * @returns {boolean}
 */
export function shouldOpenExternally(url) {
    return EXTERNAL_LINK_PATTERNS.some((pattern) => url.includes(pattern));
}

/**
 * Injected JavaScript for additional security inside the WebView.
 * - Disables context menu (long press)
 * - Blocks window.open() popups
 * - Prevents drag-and-drop
 */
export const INJECTED_SECURITY_JS = `
  (function() {
    // Block window.open popups
    window.open = function() { return null; };
    
    // Disable context menu (long-press save image, etc.)
    document.addEventListener('contextmenu', function(e) {
      e.preventDefault();
    }, false);
    
    // Disable drag and drop
    document.addEventListener('dragstart', function(e) {
      e.preventDefault();
    }, false);

    // Notify RN that the page is loaded
    window.ReactNativeWebView.postMessage(JSON.stringify({
      type: 'PAGE_LOADED',
      url: window.location.href,
      title: document.title
    }));
  })();
  true;
`;
