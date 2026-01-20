// route paths

export const ROUTES = {
  // Public routes
  LOGIN: '/login',
  REGISTER: '/register',
  
  // Dashboard routes
  DASHBOARD: '/dashboard',
  HOME: '/',
  
  // Announcement routes
  ANNOUNCEMENTS: '/dashboard/announcements',
  MY_ANNOUNCEMENTS: '/dashboard/my-announcements',
  USER_ANNOUNCEMENTS: '/dashboard/user-announcements',
  
  // User routes
  USERS: '/dashboard/users',
  PROFILE: '/dashboard/profile',
  
  // Dynamic routes
  ANNOUNCEMENT_DETAIL: (id: string | number) => `/dashboard/announcements/${id}`,
  USER_DETAIL: (id: string | number) => `/dashboard/users/${id}`,
} as const

export const PUBLIC_ROUTES = [ROUTES.LOGIN, ROUTES.REGISTER] as const
export const PROTECTED_ROUTES = [ROUTES.DASHBOARD, ROUTES.MY_ANNOUNCEMENTS] as const
export const ADMIN_ROUTES = [ROUTES.USERS, ROUTES.USER_ANNOUNCEMENTS] as const
