# Subdomain Support Implementation

## Overview
The frontend now fully supports subdomain-based tenant access. Users can access their tenant's application directly via subdomain (e.g., `acme.localhost:5173`), and the app automatically detects and uses the subdomain.

## How It Works

### 1. Automatic Subdomain Detection (`src/utils/subdomain.js`)
- **`getCurrentSubdomain()`**: Extracts the subdomain from the current URL
  - `acme.localhost:5173` → `"acme"`
  - `tenant1.localhost` → `"tenant1"`
  - `localhost:5173` → `null` (no subdomain)

- **`getBaseDomain()`**: Returns the domain without the subdomain
  - `acme.localhost:5173` → `"localhost:5173"`

- **`getUrlWithSubdomain(subdomain)`**: Builds a URL with a specific subdomain
  - Useful for redirecting users between tenants

### 2. App-Level Initialization (`src/App.jsx`)
- On app load, `getCurrentSubdomain()` is called
- If a subdomain exists, it's automatically stored in `localStorage` under key `'subdomain'`
- This ensures the API client always sends the `X-Tenant` header with the correct subdomain

### 3. Login Page Enhancement (`src/pages/Login.jsx`)
- The subdomain field is pre-populated with the current URL's subdomain
- If user accesses `acme.localhost`, the subdomain field shows `"acme"` automatically
- User can still override it if accessing from a non-subdomain URL (e.g., `localhost`)

### 4. API Integration
- The API client (in `src/services/api.js`) already includes the subdomain as an `X-Tenant` header:
  ```javascript
  const subdomain = localStorage.getItem('subdomain');
  if (subdomain) {
    config.headers['X-Tenant'] = subdomain;
  }
  ```

## User Flows

### Flow 1: Access via Subdomain
1. User visits `acme.localhost:5173`
2. App automatically detects subdomain `"acme"`
3. Stores `"acme"` in localStorage
4. On login page, subdomain field is pre-filled with `"acme"`
5. User enters email & password
6. Login request includes `X-Tenant: acme` header
7. Redirected to dashboard

### Flow 2: New Registration
1. User visits `localhost:5173` (no subdomain)
2. Registers new company with subdomain `"acme"`
3. On successful registration, user is redirected to login
4. User should then visit `acme.localhost:5173` to login
5. App detects subdomain and pre-fills form

### Flow 3: Multi-Tenant Access
- User with access to multiple tenants can visit:
  - `acme.localhost:5173` → accesses Acme Corp
  - `techstart.localhost:5173` → accesses TechStart
  - Each automatically sets correct `X-Tenant` header

## Setup for Local Development

### 1. Update `/etc/hosts` (or `C:\Windows\System32\drivers\etc\hosts`)
Add entries for your test tenants:
```
127.0.0.1 localhost
127.0.0.1 acme.localhost
127.0.0.1 techstart.localhost
127.0.0.1 mycompany.localhost
```

### 2. Run Development Server
```bash
cd notes-app-frontend
npm run dev
```

The app will be available at `http://localhost:5173` and you can access different tenants via:
- `http://acme.localhost:5173`
- `http://techstart.localhost:5173`
- etc.

### 3. Backend Configuration
Ensure your backend is configured to accept `X-Tenant` header from subdomains. The backend's `CustomInitializeTenancyBySubdomain` middleware handles this.

## Browser Compatibility
Works with all modern browsers that support:
- `window.location` API (standard)
- `localStorage` API (standard)
- ES6 (if using Babel transpilation)

## Notes
- Subdomains are case-insensitive (internally converted to lowercase)
- Only alphanumeric characters and hyphens are allowed in subdomains
- `www` and `localhost` are filtered out as they're not tenant subdomains
- The utility functions handle both `localhost` and real domains seamlessly
