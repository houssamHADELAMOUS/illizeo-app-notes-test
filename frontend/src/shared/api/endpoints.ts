// api endpoints

const API_PREFIX = '/api' as const

export const AUTH_ENDPOINTS = {
  LOGIN: `${API_PREFIX}/auth/login`,
  LOGOUT: `${API_PREFIX}/auth/logout`,
  REGISTER: `${API_PREFIX}/auth/register`,
  ME: `${API_PREFIX}/auth/me`,
  REFRESH: `${API_PREFIX}/auth/refresh`,
  FORGOT_PASSWORD: `${API_PREFIX}/auth/forgot-password`,
  RESET_PASSWORD: `${API_PREFIX}/auth/reset-password`,
} as const

// User endpoints
export const USER_ENDPOINTS = {
  LIST: `${API_PREFIX}/users`,
  DETAIL: (id: string | number) => `${API_PREFIX}/users/${id}`,
  CREATE: `${API_PREFIX}/users`,
  UPDATE: (id: string | number) => `${API_PREFIX}/users/${id}`,
  DELETE: (id: string | number) => `${API_PREFIX}/users/${id}`,
  PROFILE: `${API_PREFIX}/users/profile`,
} as const

// Announcement endpoints
export const ANNOUNCEMENT_ENDPOINTS = {
  LIST: `${API_PREFIX}/announcements`,
  MY: `${API_PREFIX}/announcements/my`,
  USERS: `${API_PREFIX}/announcements/users`,
  DETAIL: (id: string | number) => `${API_PREFIX}/announcements/${id}`,
  CREATE: `${API_PREFIX}/announcements`,
  UPDATE: (id: string | number) => `${API_PREFIX}/announcements/${id}`,
  DELETE: (id: string | number) => `${API_PREFIX}/announcements/${id}`,
  PUBLISH: (id: string | number) => `${API_PREFIX}/announcements/${id}/publish`,
  UNPUBLISH: (id: string | number) => `${API_PREFIX}/announcements/${id}/unpublish`,
} as const

// Tenant endpoints
export const TENANT_ENDPOINTS = {
  LIST: `${API_PREFIX}/tenants`,
  DETAIL: (id: string | number) => `${API_PREFIX}/tenants/${id}`,
  CREATE: `${API_PREFIX}/tenants`,
  UPDATE: (id: string | number) => `${API_PREFIX}/tenants/${id}`,
  DELETE: (id: string | number) => `${API_PREFIX}/tenants/${id}`,
} as const

export const API_ENDPOINTS = {
  AUTH: AUTH_ENDPOINTS,
  USERS: USER_ENDPOINTS,
  ANNOUNCEMENTS: ANNOUNCEMENT_ENDPOINTS,
  TENANTS: TENANT_ENDPOINTS,
} as const

export type AuthEndpoint = typeof AUTH_ENDPOINTS[keyof typeof AUTH_ENDPOINTS]
export type UserEndpoint = typeof USER_ENDPOINTS[keyof typeof USER_ENDPOINTS]
export type AnnouncementEndpoint = typeof ANNOUNCEMENT_ENDPOINTS[keyof typeof ANNOUNCEMENT_ENDPOINTS]
