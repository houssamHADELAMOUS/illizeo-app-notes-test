import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useNavigate } from 'react-router-dom'
import { authService } from '@/domain/auth/services/auth.service'
import { QUERY_KEYS } from '@/shared/api/queryKeys'
import { ROUTES } from '@/shared/constants'
import type { LoginCredentials, RegisterData } from '@/domain/auth/types'

// init auth
authService.initializeAuth()

export const useUser = () => {
  return useQuery({
    queryKey: QUERY_KEYS.USER,
    queryFn: authService.getUser,
    retry: false,
    staleTime: 1000 * 60 * 5,
    enabled: authService.isAuthenticated(),
  })
}

export const useLogin = () => {
  const queryClient = useQueryClient()
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (credentials: LoginCredentials) => authService.login(credentials),
    onSuccess: (data) => {
      queryClient.setQueryData(QUERY_KEYS.USER, data.user)
      navigate(ROUTES.DASHBOARD)
    },
  })
}

export const useRegister = () => {
  const queryClient = useQueryClient()
  const navigate = useNavigate()

  return useMutation({
    mutationFn: (data: RegisterData) => authService.register(data),
    onSuccess: (data) => {
      queryClient.setQueryData(QUERY_KEYS.USER, data.user)
      navigate(ROUTES.DASHBOARD)
    },
  })
}

export const useLogout = () => {
  const queryClient = useQueryClient()
  const navigate = useNavigate()

  return useMutation({
    mutationFn: authService.logout,
    onSuccess: () => {
      queryClient.clear()
      navigate(ROUTES.LOGIN)
    },
  })
}
