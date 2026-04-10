import { auth } from "@/auth"
import { redirect } from "next/navigation"
import type { UserRole } from "@/types/next-auth"

export async function requireAuth() {
  const session = await auth()
  if (!session?.user) redirect("/login")
  return session
}

export async function requireAdmin() {
  const session = await requireAuth()
  if (session.user.role !== "ADMIN") redirect("/dashboard")
  return session
}

export function assertKitaAccess(
  userRole: UserRole,
  userKitaId: string | null,
  kitaId: string
): void {
  if (userRole === "ADMIN") return
  if (userKitaId !== kitaId) {
    throw new Error("Kein Zugriff auf diese KITA")
  }
}

export function canEditEmployees(role: UserRole): boolean {
  return role === "ADMIN" || role === "KITA_MANAGER"
}
