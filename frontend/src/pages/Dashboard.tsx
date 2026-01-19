import { useUser, useLogout } from '@/domain/auth/hooks/useAuth'
import { Button } from '@/components/ui/button'
import { ThemeToggle } from '@/components/ui/theme-toggle'

export default function Dashboard() {
  const { data: user, isLoading } = useUser()
  const logout = useLogout()

  if (isLoading) {
    return (
      <div className="min-h-svh bg-background flex items-center justify-center">
        <div className="text-muted-foreground">Loading...</div>
      </div>
    )
  }

  return (
    <div className="min-h-svh bg-background text-foreground">
      <header className="border-b border-border px-6 py-4 flex items-center justify-between">
        <h1 className="text-xl font-bold text-primary">Illizeo</h1>
        <div className="flex items-center gap-4">
          <span className="text-sm text-muted-foreground">{user?.name}</span>
          <ThemeToggle />
          <Button variant="ghost" size="sm" onClick={() => logout.mutate()}>
            Logout
          </Button>
        </div>
      </header>

      <main className="p-6">
        <h2 className="text-2xl font-semibold mb-6">Dashboard</h2>
        <p className="text-muted-foreground">Welcome to your workspace, {user?.name}!</p>
      </main>
    </div>
  )
}
