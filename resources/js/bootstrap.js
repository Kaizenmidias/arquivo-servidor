import axios from 'axios';

window.axios = axios;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
if (csrfToken) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

window.getCsrfToken = () => csrfToken;
window.getCookieValue = (name) => {
  const encodedName = `${name}=`;
  const parts = document.cookie.split(';').map((item) => item.trim());
  const match = parts.find((part) => part.startsWith(encodedName));
  if (!match) {
    return '';
  }

  return decodeURIComponent(match.slice(encodedName.length));
};

if (import.meta.env.VITE_TRAE_DEBUG_ADMIN_AUTH_UPLOAD_419 === '1') {
  // #region debug-point C:axios-response-error
  window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
      fetch('http://127.0.0.1:7777/event', {
        method: 'POST',
        body: JSON.stringify({
          sessionId: 'admin-auth-upload-419',
          runId: 'pre-fix',
          hypothesisId: 'C',
          location: 'resources/js/bootstrap.js:axios-response-error',
          msg: '[DEBUG] Axios response error captured',
          data: {
            message: error?.message || null,
            code: error?.code || null,
            status: error?.response?.status || null,
            method: error?.config?.method || null,
            url: error?.config?.url || null,
            hasMetaCsrfToken: !!csrfToken,
            hasXsrfCookie: document.cookie.includes('XSRF-TOKEN='),
            hasSessionCookie: document.cookie.includes('-session='),
          },
          ts: Date.now(),
        }),
      }).catch(() => {});

      return Promise.reject(error);
    }
  );
  // #endregion
}
