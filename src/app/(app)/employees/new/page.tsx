import { requireAuth } from "@/lib/auth-helpers"
import { canEditEmployees } from "@/lib/auth-helpers"
import { redirect } from "next/navigation"
import { prisma } from "@/lib/prisma"
import { EmployeeForm } from "@/components/employees/EmployeeForm"
import { Button } from "@/components/ui/button"
import Link from "next/link"
import { ArrowLeft } from "lucide-react"
import type { UserRole } from "@/types/next-auth"

export default async function NewEmployeePage() {
  const session = await requireAuth()
  const { role, kitaId } = session.user

  if (!canEditEmployees(role as UserRole)) redirect("/employees")

  const kitas = role === "ADMIN"
    ? await prisma.kita.findMany({ orderBy: { name: "asc" } })
    : await prisma.kita.findMany({ where: { id: kitaId ?? undefined }, orderBy: { name: "asc" } })

  return (
    <div className="space-y-6 max-w-2xl">
      <div className="flex items-center gap-3">
        <Button variant="ghost" size="sm" asChild>
          <Link href="/employees">
            <ArrowLeft className="h-4 w-4 mr-1" />
            Zurück
          </Link>
        </Button>
        <h1 className="text-2xl font-bold">Mitarbeiter anlegen</h1>
      </div>
      <EmployeeForm kitas={kitas} defaultKitaId={kitaId ?? undefined} />
    </div>
  )
}
