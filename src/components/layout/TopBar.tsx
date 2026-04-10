"use client"

import { signOut } from "next-auth/react"
import { Button } from "@/components/ui/button"
import { LogOut, User } from "lucide-react"
import type { UserRole } from "@/types/next-auth"
import { ROLE_LABELS } from "@/lib/utils"

interface TopBarProps {
  userName?: string | null
  userEmail?: string | null
  role: UserRole
}

export function TopBar({ userName, userEmail, role }: TopBarProps) {
  return (
    <header className="h-16 border-b bg-white flex items-center justify-between px-6 shrink-0">
      <div />
      <div className="flex items-center gap-4">
        <div className="flex items-center gap-2 text-sm">
          <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground">
            <User className="h-4 w-4" />
          </div>
          <div className="text-right">
            <p className="font-medium leading-tight">{userName || userEmail}</p>
            <p className="text-xs text-muted-foreground">{ROLE_LABELS[role]}</p>
          </div>
        </div>
        <Button
          variant="ghost"
          size="sm"
          onClick={() => signOut({ callbackUrl: "/login" })}
          className="text-muted-foreground"
        >
          <LogOut className="h-4 w-4 mr-1" />
          Abmelden
        </Button>
      </div>
    </header>
  )
}
