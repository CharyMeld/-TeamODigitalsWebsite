// resources/js/axiosConfig.js
import axios from "axios";

// Backend API URL from .env or fallback
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || "http://localhost:8080";

// Create a single Axios instance
export const apiClient = axios.create({
  baseURL: apiBaseUrl,
  withCredentials: true, // critical for session-based auth
  headers: {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
  },
});

// Track CSRF initialization state
let csrfInitialized = false;

// Initialize CSRF cookie for Laravel Sanctum
export const initCsrf = async () => {
  if (csrfInitialized) return;

  try {
    await fetch(`${apiBaseUrl}/sanctum/csrf-cookie`, {
      method: "GET",
      credentials: "include",
    });
    csrfInitialized = true;
    console.log("✅ CSRF cookie initialized");
  } catch (err) {
    console.warn("⚠️ CSRF initialization failed:", err.message);
  }
};

// Attach CSRF token from meta tag
const attachCsrfToken = (config) => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  if (token) config.headers = { ...config.headers, "X-CSRF-TOKEN": token };
  return config;
};

//  Centralized request wrapper
export const makeRequest = async (config) => {
  try {
    if (!csrfInitialized) await initCsrf();
    config = attachCsrfToken(config);
    return await apiClient(config);
  } catch (error) {
    const status = error.response?.status;

    if (status === 401) {
      console.warn("❌ 401 Unauthorized — session expired");
      csrfInitialized = false;
      window.dispatchEvent(new CustomEvent("auth:unauthorized"));
      if (!window.location.pathname.includes("/login")) {
        window.location.href = "/login";
      }
    } else if (status === 419) {
      console.warn("⚠️ 419 CSRF expired — reinitializing");
      csrfInitialized = false;
      await initCsrf();
      config = attachCsrfToken(config);
      return await apiClient(config);
    } else if (status === 403) {
      console.error("🚫 403 Forbidden — insufficient permissions");
    } else if (status >= 500) {
      console.error(`💥 Server error (${status})`, error.response?.data);
    }

    throw error;
  }
};

// Convenience wrappers
export const get = (url, config = {}) => makeRequest({ ...config, method: "GET", url });
export const post = (url, data, config = {}) => makeRequest({ ...config, method: "POST", url, data });
export const put = (url, data, config = {}) => makeRequest({ ...config, method: "PUT", url, data });
export const del = (url, config = {}) => makeRequest({ ...config, method: "DELETE", url });

export default apiClient;

