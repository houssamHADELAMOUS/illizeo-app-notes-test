// error handling utils

import { AxiosError } from 'axios'
import type { ApiError } from '@/shared/types/api'

// default messages by status
const DEFAULT_ERROR_MESSAGES: Record<number, string> = {
  400: 'Invalid request. Please check your input.',
  401: 'You are not authorized. Please log in again.',
  403: 'You do not have permission to perform this action.',
  404: 'The requested resource was not found.',
  422: 'Validation failed. Please check your input.',
  429: 'Too many requests. Please try again later.',
  500: 'Server error. Please try again later.',
  503: 'Service unavailable. Please try again later.',
}

// get user friendly message
export function getErrorMessage(error: unknown): string {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiError | undefined
    
    if (data?.errors) {
      const firstError = Object.values(data.errors)[0]
      if (Array.isArray(firstError) && firstError.length > 0) {
        return firstError[0]
      }
    }
    
    if (data?.message) {
      return data.message
    }
    
    const status = error.response?.status
    if (status && DEFAULT_ERROR_MESSAGES[status]) {
      return DEFAULT_ERROR_MESSAGES[status]
    }
    
    // Network error
    if (error.code === 'ERR_NETWORK') {
      return 'Network error. Please check your connection.'
    }
  }
  
  if (error instanceof Error) {
    return error.message
  }
  
  return 'An unexpected error occurred.'
}

// get validation errors map
export function getValidationErrors(error: unknown): Record<string, string> | null {
  if (error instanceof AxiosError) {
    const data = error.response?.data as ApiError | undefined
    
    if (data?.errors) {
      const result: Record<string, string> = {}
      for (const [field, messages] of Object.entries(data.errors)) {
        if (Array.isArray(messages) && messages.length > 0) {
          result[field] = messages[0]
        }
      }
      return result
    }
  }
  
  return null
}

// check http status
export function isHttpError(error: unknown, status: number): boolean {
  return error instanceof AxiosError && error.response?.status === status
}

// 401
export function isUnauthorized(error: unknown): boolean {
  return isHttpError(error, 401)
}

// 403
export function isForbidden(error: unknown): boolean {
  return isHttpError(error, 403)
}

// 404
export function isNotFound(error: unknown): boolean {
  return isHttpError(error, 404)
}

// 422
export function isValidationError(error: unknown): boolean {
  return isHttpError(error, 422)
}
