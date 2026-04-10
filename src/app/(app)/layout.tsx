import { requireAuth } from "@/lib/auth-helpers"
import { prisma } from "@/lib/prisma"
import { Sidebar } from "@/components/layout/Sidebar"
import { TopBar } from "@/components/layout/TopBar"
import type { UserRole } from "@/types/next-auth"

export default async function AppLayout({ children }: { children: React.ReactNode }) {
  const session = await requireAuth()
  const { role, kitaId, name, email } = session.user

  let kitaName: string | null = null
  if (kitaId) {
    const kita = await prisma.kita.findUnique({ where: { id: kitaId }, select: { name: true } })
    kitaName = kita?.name ?? null
  }

  return (
    <div className="flex min-h-screen bg-gray-50">
      <Sidebar role={role as UserRole} kitaName={kitaName} />
      <div className="flex flex-1 flex-col">
        <TopBar userName={name} userEmail={email} role={role as UserRole} />
        <main className="flex-1 p-6 overflow-auto">{children}</main>
      </div>
    </div>
  )
}
