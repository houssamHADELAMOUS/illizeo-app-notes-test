// query keys

export const QUERY_KEYS = {
  // Auth
  USER: ['user'] as const,
  
  // Announcements
  ANNOUNCEMENTS: {
    all: ['announcements'] as const,
    published: (page: number) => ['announcements', 'published', page] as const,
    my: (page: number) => ['announcements', 'my', page] as const,
    users: (page: number) => ['announcements', 'users', page] as const,
    detail: (id: number) => ['announcements', id] as const,
  },
  
  // Users
  USERS: {
    all: ['users'] as const,
    list: (page: number) => ['users', 'list', page] as const,
    detail: (id: number) => ['users', id] as const,
  },
  
  // Tenants
  TENANTS: {
    all: ['tenants'] as const,
    detail: (id: number) => ['tenants', id] as const,
  },
} as const

export const invalidateKeys = {
  announcements: () => QUERY_KEYS.ANNOUNCEMENTS.all,
  users: () => QUERY_KEYS.USERS.all,
  tenants: () => QUERY_KEYS.TENANTS.all,
}
