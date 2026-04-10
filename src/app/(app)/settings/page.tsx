import { requireAuth } from "@/lib/auth-helpers"
import { prisma } from "@/lib/prisma"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { ROLE_LABELS } from "@/lib/utils"
import { UserSettingsForm } from "@/components/settings/UserSettingsForm"
import { AdminUserManager } from "@/components/settings/AdminUserManager"
import type { UserRole } from "@/types/next-auth"

export default async function SettingsPage() {
  const session = await requireAuth()
  const { id, role, kitaId } = session.user

  const user = await prisma.user.findUnique({
    where: { id },
    include: { kita: { select: { name: true } } },
  })

  const isAdmin = role === "ADMIN"
  const allUsers = isAdmin
    ? await prisma.user.findMany({
        include: { kita: { select: { id: true, name: true } } },
        orderBy: { name: "asc" },
      })
    : []
  const allKitas = isAdmin
    ? await prisma.kita.findMany({ orderBy: { name: "asc" } })
    : []

  return (
    <div className="space-y-6 max-w-4xl">
      <h1 className="text-2xl font-bold">Einstellungen</h1>

      {/* Profile */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Mein Profil</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm">
            <div className="flex justify-between">
              <span className="text-muted-foreground">Name</span>
              <span className="font-medium">{user?.name}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">E-Mail</span>
              <span className="font-medium">{user?.email}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-muted-foreground">Rolle</span>
              <Badge variant="secondary">{ROLE_LABELS[role]}</Badge>
            </div>
            {user?.kita && (
              <div className="flex justify-between">
                <span className="text-muted-foreground">Einrichtung</span>
                <span className="font-medium">{user.kita.name}</span>
              </div>
            )}
          </CardContent>
        </Card>

        <UserSettingsForm userId={id} currentName={user?.name ?? ""} />
      </div>

      {/* Admin: User Management */}
      {isAdmin && (
        <AdminUserManager users={allUsers} kitas={allKitas} />
      )}
    </div>
  )
}
