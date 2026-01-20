// validation schemas

import * as Yup from 'yup'

const email = Yup.string()
  .email('Please enter a valid email address')
  .required('Email is required')

const password = Yup.string()
  .min(6, 'Password must be at least 6 characters')
  .required('Password is required')

const passwordConfirmation = Yup.string()
  .oneOf([Yup.ref('password')], 'Passwords must match')
  .required('Please confirm your password')

const name = Yup.string()
  .min(2, 'Name must be at least 2 characters')
  .max(50, 'Name must be less than 50 characters')
  .required('Name is required')

// Auth schemas
export const loginSchema = Yup.object({
  email,
  password,
})

export const registerSchema = Yup.object({
  name,
  email,
  password,
  password_confirmation: passwordConfirmation,
})

// Announcement schemas
export const announcementSchema = Yup.object({
  title: Yup.string()
    .min(3, 'Title must be at least 3 characters')
    .max(200, 'Title must be less than 200 characters')
    .required('Title is required'),
  content: Yup.string()
    .min(10, 'Content must be at least 10 characters')
    .required('Content is required'),
  status: Yup.string()
    .oneOf(['draft', 'published'], 'Invalid status')
    .required('Status is required'),
})

// User schemas
export const userSchema = Yup.object({
  name,
  email,
  role: Yup.string()
    .oneOf(['admin', 'user', 'employee'], 'Invalid role')
    .required('Role is required'),
})

export const updatePasswordSchema = Yup.object({
  current_password: Yup.string().required('Current password is required'),
  password,
  password_confirmation: passwordConfirmation,
})

// Tenant schema
export const tenantSchema = Yup.object({
  name: Yup.string()
    .min(2, 'Company name must be at least 2 characters')
    .max(100, 'Company name must be less than 100 characters')
    .required('Company name is required'),
  subdomain: Yup.string()
    .min(3, 'Subdomain must be at least 3 characters')
    .max(30, 'Subdomain must be less than 30 characters')
    .matches(/^[a-z0-9-]+$/, 'Subdomain can only contain lowercase letters, numbers, and hyphens')
    .required('Subdomain is required'),
  admin_email: email,
  admin_password: password,
})

export type LoginFormValues = Yup.InferType<typeof loginSchema>
export type RegisterFormValues = Yup.InferType<typeof registerSchema>
export type AnnouncementFormValues = Yup.InferType<typeof announcementSchema>
export type UserFormValues = Yup.InferType<typeof userSchema>
export type TenantFormValues = Yup.InferType<typeof tenantSchema>
