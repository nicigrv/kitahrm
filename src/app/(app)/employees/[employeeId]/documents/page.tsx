import { requireAuth } from "@/lib/auth-helpers"
import { getEmployee } from "@/lib/queries/employees"
import { notFound, redirect } from "next/navigation"
import { canEditEmployees } from "@/lib/auth-helpers"
import { DocumentManager } from "@/components/employees/DocumentManager"
import { Button } from "@/components/ui/button"
import Link from "next/link"
import { ArrowLeft } from "lucide-react"
import type { UserRole } from "@/types/next-auth"

export default async function DocumentsPage({ params }: { params: { employeeId: string } }) {
  const session = await requireAuth()
  const { role, kitaId } = session.user

  if (!canEditEmployees(role as UserRole)) redirect(`/employees/${params.employeeId}`)

  const employee = await getEmployee(params.employeeId, role as UserRole, kitaId)
  if (!employee) notFound()

  return (
    <div className="space-y-6 max-w-3xl">
      <div className="flex items-center gap-3">
        <Button variant="ghost" size="sm" asChild>
          <Link href={`/employees/${params.employeeId}`}>
            <ArrowLeft className="h-4 w-4 mr-1" />
            Zurück
          </Link>
        </Button>
        <div>
          <h1 className="text-2xl font-bold">Dokumente</h1>
          <p className="text-muted-foreground text-sm">
            {employee.firstName} {employee.lastName}
          </p>
        </div>
      </div>
      <DocumentManager
        employeeId={params.employeeId}
        initialDocuments={employee.documents}
      />
    </div>
  )
}
