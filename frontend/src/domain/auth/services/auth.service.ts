import apiClient, { getCsrfCookie } from '@/shared/api/client'
import type { LoginCredentials, RegisterData, AuthResponse, User } from '@/domain/auth/types'

export const authService = {
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    await getCsrfCookie()
    const response = await apiClient.post<AuthResponse>('/api/login', credentials)
    return response.data
  },

  async register(data: RegisterData): Promise<AuthResponse> {
    await getCsrfCookie()
    const response = await apiClient.post<AuthResponse>('/api/register', data)
    return response.data
  },

  async logout(): Promise<void> {
    await apiClient.post('/api/logout')
  },

  async getUser(): Promise<User> {
    const response = await apiClient.get<{ data: User }>('/api/user')
    return response.data.data
  },
}
