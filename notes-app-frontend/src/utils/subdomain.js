/**
 * Utility functions for handling subdomains
 */

/**
 * Extract subdomain from current hostname
 * Examples:
 *   acme.localhost:5173 -> "acme"
 *   tenant1.localhost -> "tenant1"
 *   localhost:5173 -> null
 *   localhost -> null
 */
export const getCurrentSubdomain = () => {
  const hostname = window.location.hostname;
  
  // Split by dots
  const parts = hostname.split('.');
  
  // If less than 2 parts (e.g., "localhost"), no subdomain
  if (parts.length < 2) {
    return null;
  }
  
  // Get first part (subdomain)
  const subdomain = parts[0];
  
  // Filter out localhost and IP addresses
  if (subdomain === 'localhost' || subdomain === 'www' || /^\d+$/.test(subdomain)) {
    return null;
  }
  
  return subdomain.toLowerCase();
};

/**
 * Get the base domain without subdomain
 * Examples:
 *   acme.localhost:5173 -> "localhost:5173"
 *   tenant1.localhost -> "localhost"
 */
export const getBaseDomain = () => {
  const hostname = window.location.hostname;
  const port = window.location.port ? `:${window.location.port}` : '';
  
  const parts = hostname.split('.');
  
  if (parts.length < 2) {
    return hostname + port;
  }
  
  // Remove subdomain and join rest
  return parts.slice(1).join('.') + port;
};

/**
 * Build URL with specified subdomain
 */
export const getUrlWithSubdomain = (subdomain) => {
  const protocol = window.location.protocol;
  const baseDomain = getBaseDomain();
  
  if (subdomain) {
    return `${protocol}//${subdomain}.${baseDomain}`;
  }
  
  return `${protocol}//${baseDomain}`;
};
