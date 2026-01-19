import { createRoot } from 'react-dom/client'
import { RouterProvider } from 'react-router-dom'
import '@/index.css'
import { ThemeProvider } from '@/context/theme-context'
import { QueryProvider } from '@/providers/QueryProvider'
import { router } from '@/router'

createRoot(document.getElementById('root')!).render(
  <QueryProvider>
    <ThemeProvider defaultTheme="system">
      <RouterProvider router={router} />
    </ThemeProvider>
  </QueryProvider>
)
