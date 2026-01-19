import { Moon, Sun, Monitor } from "lucide-react"
import { useTheme } from "@/context/theme-context"
import { Button } from "@/components/ui/button"

interface ThemeToggleProps {
  variant?: "icon" | "full"
  className?: string
}

export function ThemeToggle({ variant = "icon", className }: ThemeToggleProps) {
  const { theme, setTheme, resolvedTheme } = useTheme()

  if (variant === "full") {
    return (
      <div className={`flex items-center gap-1 rounded-lg bg-secondary p-1 ${className}`}>
        <Button
          variant={theme === "light" ? "default" : "ghost"}
          size="sm"
          onClick={() => setTheme("light")}
          className="h-8 px-3"
        >
          <Sun className="h-4 w-4 mr-1 text-black" />
          Light
        </Button>
        <Button
          variant={theme === "dark" ? "default" : "ghost"}
          size="sm"
          onClick={() => setTheme("dark")}
          className="h-8 px-3"
        >
          <Moon className="h-4 w-4 mr-1" />
          Dark
        </Button>
        <Button
          variant={theme === "system" ? "default" : "ghost"}
          size="sm"
          onClick={() => setTheme("system")}
          className="h-8 px-3"
        >
          <Monitor className="h-4 w-4 mr-1" />
          System
        </Button>
      </div>
    )
  }

  // Icon-only toggle (cycles through themes)
  const cycleTheme = () => {
    if (theme === "light") setTheme("dark")
    else if (theme === "dark") setTheme("system")
    else setTheme("light")
  }

  return (
    <Button
      variant="ghost"
      size="icon"
      onClick={cycleTheme}
      className={className}
      title={`Current: ${theme} (${resolvedTheme})`}
    >
      {resolvedTheme === "dark" ? (
        <Moon className="h-8 w-8 text-purple-300" />
      ) : (
        <Sun className="h-8 w-8 text-purple-900" />
      )}
      <span className="sr-only">Toggle theme</span>
    </Button>
  )
}
