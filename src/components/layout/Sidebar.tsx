"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"
import {
  LayoutDashboard,
  Users,
  Building2,
  GraduationCap,
  Settings,
  ChevronRight,
} from "lucide-react"
import type { UserRole } from "@/types/next-auth"

interface NavItem {
  href: string
  label: string
  icon: React.ComponentType<{ className?: string }>
  adminOnly?: boolean
}

const navItems: NavItem[] = [
  { href: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
  { href: "/employees", label: "Mitarbeiter", icon: Users },
  { href: "/training", label: "Schulungen", icon: GraduationCap },
  { href: "/kitas", label: "Einrichtungen", icon: Building2, adminOnly: true },
  { href: "/settings", label: "Einstellungen", icon: Settings },
]

interface SidebarProps {
  role: UserRole
  kitaName?: string | null
}

export function Sidebar({ role, kitaName }: SidebarProps) {
  const pathname = usePathname()

  const visibleItems = navItems.filter((item) => {
    if (item.adminOnly && role !== "ADMIN") return false
    return true
  })

  return (
    <aside className="flex flex-col w-64 min-h-screen bg-gray-900 text-white">
      {/* Logo */}
      <div className="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600">
          <Building2 className="h-5 w-5 text-white" />
        </div>
        <div>
          <p className="font-bold text-sm leading-tight">KITA-HRM</p>
          <p className="text-xs text-gray-400">Personalverwaltung</p>
        </div>
      </div>

      {/* KITA Info */}
      {kitaName && (
        <div className="px-4 py-3 mx-3 mt-4 rounded-lg bg-blue-600/20 border border-blue-500/30">
          <p className="text-xs text-blue-300 font-medium">{kitaName}</p>
        </div>
      )}
      {role === "ADMIN" && (
        <div className="px-4 py-3 mx-3 mt-4 rounded-lg bg-amber-600/20 border border-amber-500/30">
          <p className="text-xs text-amber-300 font-medium">Träger-Admin</p>
          <p className="text-xs text-amber-400/70">Alle Einrichtungen</p>
        </div>
      )}

      {/* Navigation */}
      <nav className="flex-1 px-3 py-4 space-y-1">
        {visibleItems.map((item) => {
          const isActive = pathname === item.href || pathname.startsWith(item.href + "/")
          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                "flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors",
                isActive
                  ? "bg-blue-600 text-white"
                  : "text-gray-300 hover:bg-gray-800 hover:text-white"
              )}
            >
              <item.icon className="h-4 w-4 shrink-0" />
              <span className="flex-1">{item.label}</span>
              {isActive && <ChevronRight className="h-3 w-3 opacity-60" />}
            </Link>
          )
        })}
      </nav>
    </aside>
  )
}
