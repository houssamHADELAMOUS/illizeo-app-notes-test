import { useState } from 'react'
import { useFormik } from 'formik'
import { useNavigate } from 'react-router-dom'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
  Field as FieldWrapper,
  FieldDescription,
  FieldGroup,
  FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { ThemeToggle } from '@/components/ui/theme-toggle'
import * as Yup from 'yup'
import { ChevronLeft, ChevronRight, CheckCircle } from 'lucide-react'
import apiClient from '@/shared/api/client'

interface TenantFormData {
  company_name: string
  company_email: string
  domain: string
  admin_name: string
  admin_email: string
  admin_password: string
  admin_password_confirmation: string
}

export default function TenantRegistration() {
  const navigate = useNavigate()
  const [currentStep, setCurrentStep] = useState(1)
  const [isSuccess, setIsSuccess] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [isSubmitting, setIsSubmitting] = useState(false)
  
  const companySchema = Yup.object({
    company_name: Yup.string()
      .required('Company name is required'),
    company_email: Yup.string()
      .email('Invalid email address')
      .required('Company email is required'),
    domain: Yup.string()
      .matches(/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/, 'Invalid domain format')
      .required('Domain is required'),
  })
  
  const adminSchema = Yup.object({
    admin_name: Yup.string()
      .required('Admin name is required'),
    admin_email: Yup.string()
      .email('Invalid email address')
      .required('Admin email is required'),
    admin_password: Yup.string()
      .min(8, 'Password must be at least 8 characters')
      .matches(/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/, 'Password must contain uppercase, lowercase, and numbers')
      .required('Password is required'),
    admin_password_confirmation: Yup.string()
      .oneOf([Yup.ref('admin_password')], 'Passwords must match')
      .required('Please confirm your password'),
  })

  const formik = useFormik<TenantFormData>({
    initialValues: {
      company_name: '',
      company_email: '',
      domain: '',
      admin_name: '',
      admin_email: '',
      admin_password: '',
      admin_password_confirmation: '',
    },
    onSubmit: async (values) => {
      try {
        setIsSubmitting(true)
        setError(null)
        
        const { admin_password_confirmation, ...apiData } = values
        
        const response = await apiClient.post('/api/tenants', apiData)
        
        setIsSuccess(true)
        
        setTimeout(() => {
          formik.resetForm()
          setCurrentStep(1)
          setIsSuccess(false)
          navigate(`/login?domain=${values.domain}`)
        }, 2000)
      } catch (err: any) {
        const errorMessage = err.response?.data?.message || err.message || 'Failed to create workspace'
        setError(errorMessage)
      } finally {
        setIsSubmitting(false)
      }
    },
  })
  
  const validateStep1 = async () => {
    try {
      await companySchema.validate(formik.values, { abortEarly: false })
      return true
    } catch (error) {
      const errors: Record<string, string> = {}
      if (error instanceof Yup.ValidationError) {
        error.inner.forEach(err => {
          if (err.path) errors[err.path] = err.message
        })
        formik.setErrors(errors)
      }
      return false
    }
  }
  
  const validateStep2 = async () => {
    try {
      await adminSchema.validate(formik.values, { abortEarly: false })
      return true
    } catch (error) {
      const errors: Record<string, string> = {}
      if (error instanceof Yup.ValidationError) {
        error.inner.forEach(err => {
          if (err.path) errors[err.path] = err.message
        })
        formik.setErrors(errors)
      }
      return false
    }
  }
  
  const handleNext = async () => {
    if (currentStep === 1) {
      const isValid = await validateStep1()
      if (isValid) {
        setCurrentStep(2)
        formik.setErrors({
          admin_name: undefined,
          admin_email: undefined,
          admin_password: undefined,
          admin_password_confirmation: undefined,
        })
      }
    }
  }
  
  const handleBack = () => {
    if (currentStep > 1) {
      setCurrentStep(currentStep - 1)
      if (currentStep === 2) {
        formik.setErrors({
          company_name: undefined,
          company_email: undefined,
          domain: undefined,
        })
      }
    }
  }
  
  const handleSubmit = async () => {
    const isValid = await validateStep2()
    if (isValid) {
      await formik.submitForm()
    }
  }

  if (isSuccess) {
    return (
      <div className="min-h-svh bg-purple-50 dark:bg-purple-900 flex items-center justify-center p-4">
        <div className="fixed top-4 right-4">
          <ThemeToggle />
        </div>

        <div className="w-full max-w-5xl">
          <Card className="overflow-hidden border-none shadow-none">
            <CardContent className="grid p-0 md:grid-cols-2">
              <div className="p-6 md:p-8 flex items-center justify-center">
                <div className="flex flex-col items-center gap-4 text-center">
                  <div className="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                    <CheckCircle className="h-8 w-8 text-green-600 dark:text-green-400" />
                  </div>
                  <h2 className="text-2xl font-bold">Workspace Created!</h2>
                  <p className="text-muted-foreground text-sm">
                    Your workspace <strong>{formik.values.domain}.illizeo.com</strong> has been created successfully.
                    Redirecting to login...
                  </p>
                </div>
              </div>
              
              <div className="relative hidden md:block">
                <img
                  src="/login-image.png"
                  alt="Success"
                  className="absolute inset-0 h-full w-full object-cover dark:grayscale"
                />
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    )
  }

  return (
    <div className="min-h-svh bg-purple-50 dark:bg-purple-900 flex items-center justify-center p-4">
      <div className="fixed top-4 right-4">
        <ThemeToggle />
      </div>

      <div className="w-full max-w-5xl">
        <Card className="overflow-hidden border-none shadow-none">
          <CardContent className="grid p-0 md:grid-cols-2">
            <div className="p-6 md:p-8">
              <form>
                <FieldGroup>
                  <div className="flex flex-col items-center  text-center mb-4">
                    <img src="/loginlogo.png" alt="Illizeo" className='size-30 mb-2' />
                    
                    {/* Compact Multi-Step Banner */}
<div className="w-full">
  <div className="relative flex items-center justify-around mb-1">
    {[1, 2].map((step, index) => {
      const isActive = currentStep >= step
      const hasError = 
        (step === 1 && (formik.errors.company_name || formik.errors.company_email || formik.errors.domain)) ||
        (step === 2 && (formik.errors.admin_name || formik.errors.admin_email || formik.errors.admin_password || formik.errors.admin_password_confirmation))
      
      return (
        <div key={step} className="flex items-center">
          <div
            className={`w-6 h-6 rounded-full flex items-center justify-center text-xs cursor-pointer ${
              isActive 
                ? hasError
                  ? 'border border-red-500 text-red-500'
                  : 'bg-primary text-primary-foreground'
                : 'bg-gray-200 dark:bg-gray-700 text-gray-500'
            }`}
            onClick={() => {
              if (step === 1) setCurrentStep(1)
              else if (step === 2 && currentStep > 1) setCurrentStep(2)
            }}
          >
            {step}
          </div>
          
          {/* Add connecting line between circles (except after last circle) */}
          {index === 0 && (
            <div className="w-8 h-0.5 bg-gray-200 dark:bg-gray-700 mx-2"></div>
          )}
        </div>
      )
    })}
  </div>
  
  <div className="flex justify-around text-xs text-muted-foreground">
    <span>Company</span>
    <span>Admin</span>
  </div>
</div>  
                  
                  </div>

                  {error && (
                    <div className="bg-destructive/10 text-destructive text-sm p-3 rounded-md mb-4">
                      {error}
                    </div>
                  )}

                  {currentStep === 1 && (
                    <>
                      <FieldWrapper>
                        <FieldLabel htmlFor="company_name">Company Name</FieldLabel>
                        <Input
                          id="company_name"
                          name="company_name"
                          placeholder="Acme Inc."
                          value={formik.values.company_name}
                          onChange={formik.handleChange}
                          onBlur={formik.handleBlur}
                          className={formik.touched.company_name && formik.errors.company_name ? 'border-destructive' : ''}
                        />
                        {formik.touched.company_name && formik.errors.company_name && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.company_name}
                          </FieldDescription>
                        )}
                      </FieldWrapper>

                      <FieldWrapper>
                        <FieldLabel htmlFor="company_email">Company Email</FieldLabel>
                        <Input
                          id="company_email"
                          name="company_email"
                          type="email"
                          placeholder="company@example.com"
                          value={formik.values.company_email}
                          onChange={formik.handleChange}
                          onBlur={formik.handleBlur}
                          className={formik.touched.company_email && formik.errors.company_email ? 'border-destructive' : ''}
                        />
                        {formik.touched.company_email && formik.errors.company_email && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.company_email}
                          </FieldDescription>
                        )}
                      </FieldWrapper>

                      <FieldWrapper>
                        <FieldLabel htmlFor="domain">Domain</FieldLabel>
                        <div className="flex items-center">
                          <Input
                            id="domain"
                            name="domain"
                            placeholder="yourcompany"
                            value={formik.values.domain}
                            onChange={formik.handleChange}
                            onBlur={formik.handleBlur}
                            className={cn(
                              "rounded-r-none",
                              formik.touched.domain && formik.errors.domain ? 'border-destructive' : ''
                            )}
                          />
                          <div className="bg-muted border border-l-0 border-input px-3 py-2 rounded-r-md text-muted-foreground text-sm">
                            .illizeo.com
                          </div>
                        </div>
                        {formik.touched.domain && formik.errors.domain && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.domain}
                          </FieldDescription>
                        )}
                        <FieldDescription>
                          Access at: {formik.values.domain || 'yourcompany'}.illizeo.com
                        </FieldDescription>
                      </FieldWrapper>
                    </>
                  )}

                  {currentStep === 2 && (
                    <>
                      <FieldWrapper>
                        <FieldLabel htmlFor="admin_name">Full Name</FieldLabel>
                        <Input
                          id="admin_name"
                          name="admin_name"
                          placeholder="John Doe"
                          value={formik.values.admin_name}
                          onChange={formik.handleChange}
                          onBlur={formik.handleBlur}
                          className={formik.touched.admin_name && formik.errors.admin_name ? 'border-destructive' : ''}
                        />
                        {formik.touched.admin_name && formik.errors.admin_name && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.admin_name}
                          </FieldDescription>
                        )}
                      </FieldWrapper>

                      <FieldWrapper>
                        <FieldLabel htmlFor="admin_email">Email</FieldLabel>
                        <Input
                          id="admin_email"
                          name="admin_email"
                          type="email"
                          placeholder="admin@example.com"
                          value={formik.values.admin_email}
                          onChange={formik.handleChange}
                          onBlur={formik.handleBlur}
                          className={formik.touched.admin_email && formik.errors.admin_email ? 'border-destructive' : ''}
                        />
                        {formik.touched.admin_email && formik.errors.admin_email && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.admin_email}
                          </FieldDescription>
                        )}
                      </FieldWrapper>

                      <FieldWrapper>
                        <FieldLabel htmlFor="admin_password">Password</FieldLabel>
                        <Input
                          id="admin_password"
                          name="admin_password"
                          type="password"
                          placeholder="••••••••"
                          value={formik.values.admin_password}
                          onChange={formik.handleChange}
                          onBlur={formik.handleBlur}
                          className={formik.touched.admin_password && formik.errors.admin_password ? 'border-destructive' : ''}
                        />
                        {formik.touched.admin_password && formik.errors.admin_password && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.admin_password}
                          </FieldDescription>
                        )}
                        <FieldDescription>
                          Min 8 chars with uppercase, lowercase, and numbers
                        </FieldDescription>
                      </FieldWrapper>

                      <FieldWrapper>
                        <FieldLabel htmlFor="admin_password_confirmation">Confirm Password</FieldLabel>
                        <Input
                          id="admin_password_confirmation"
                          name="admin_password_confirmation"
                          type="password"
                          placeholder="••••••••"
                          value={formik.values.admin_password_confirmation}
                          onChange={formik.handleChange}
                          onBlur={formik.handleBlur}
                          className={formik.touched.admin_password_confirmation && formik.errors.admin_password_confirmation ? 'border-destructive' : ''}
                        />
                        {formik.touched.admin_password_confirmation && formik.errors.admin_password_confirmation && (
                          <FieldDescription className="text-destructive">
                            {formik.errors.admin_password_confirmation}
                          </FieldDescription>
                        )}
                      </FieldWrapper>
                    </>
                  )}

                  <div className="flex gap-3 mt-4">
                    {currentStep === 2 ? (
                      <>
                        <Button
                          type="button"
                          variant="outline"
                          onClick={handleBack}
                          className="flex-1"
                        >
                          <ChevronLeft className="mr-2 h-4 w-4" />
                          Back
                        </Button>
                        <Button
                          type="button"
                          onClick={handleSubmit}
                          className="flex-1"
                          disabled={isSubmitting}
                        >
                          {isSubmitting ? 'Creating...' : 'Create Workspace'}
                        </Button>
                      </>
                    ) : (
                      <Button
                        type="button"
                        onClick={handleNext}
                        className="w-full"
                      >
                        Continue
                        <ChevronRight className="ml-2 h-4 w-4" />
                      </Button>
                    )}
                  </div>

                  <FieldDescription className="text-center mt-3">
                    Already have a workspace?{' '}
                    <button
                      type="button"
                      onClick={() => navigate('/login')}
                      className="text-primary hover:underline font-medium"
                    >
                      Sign in here
                    </button>
                  </FieldDescription>
                </FieldGroup>
              </form>
            </div>
            
            <div className="relative hidden md:block ">
              <img
                src="/register-image.png"
                alt="Workspace"
                className="absolute inset-0 h-full w-full object-cover dark:grayscale "
              />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}