import { requireAuth } from "@/lib/auth-helpers"
import { getEmployees } from "@/lib/queries/employees"
import { formatDate, CONTRACT_TYPE_LABELS } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import Link from "next/link"
import { Plus, Search, Users } from "lucide-react"
import type { UserRole } from "@/types/next-auth"

export default async function EmployeesPage({
  searchParams,
}: {
  searchParams: { q?: string; kita?: string; contract?: string }
}) {
  const session = await requireAuth()
  const { role, kitaId } = session.user
  const canEdit = role === "ADMIN" || role === "KITA_MANAGER"

  const employees = await getEmployees(role as UserRole, kitaId, searchParams.kita)

  // Client-side filtering by search query
  const q = searchParams.q?.toLowerCase() ?? ""
  const filtered = employees.filter((e) => {
    const matchName = `${e.firstName} ${e.lastName}`.toLowerCase().includes(q)
    const matchPos = e.position.toLowerCase().includes(q)
    const matchContract = searchParams.contract ? e.contractType === searchParams.contract : true
    return (matchName || matchPos) && matchContract
  })

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Mitarbeiter</h1>
          <p className="text-muted-foreground text-sm mt-1">{filtered.length} Mitarbeiter</p>
        </div>
        {canEdit && (
          <Button asChild>
            <Link href="/employees/new">
              <Plus className="h-4 w-4 mr-2" />
              Neu anlegen
            </Link>
          </Button>
        )}
      </div>

      {/* Search */}
      <div className="flex gap-3">
        <form className="flex gap-2 flex-1">
          <div className="relative flex-1 max-w-sm">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <input
              name="q"
              defaultValue={searchParams.q}
              placeholder="Name oder Position suchen..."
              className="w-full pl-9 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            />
          </div>
          <Button type="submit" variant="outline" size="sm">Suchen</Button>
        </form>
      </div>

      {/* Table */}
      <div className="rounded-lg border bg-white overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-muted/50 border-b">
            <tr>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Name</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Position</th>
              {role === "ADMIN" && <th className="text-left px-4 py-3 font-medium text-muted-foreground">Einrichtung</th>}
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Vertrag</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Std./Woche</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Seit</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Status</th>
              <th className="px-4 py-3" />
            </tr>
          </thead>
          <tbody>
            {filtered.length === 0 ? (
              <tr>
                <td colSpan={8} className="text-center py-12 text-muted-foreground">
                  <Users className="h-8 w-8 mx-auto mb-2 opacity-30" />
                  Keine Mitarbeiter gefunden
                </td>
              </tr>
            ) : (
              filtered.map((emp) => (
                <tr key={emp.id} className="border-b last:border-0 hover:bg-muted/20">
                  <td className="px-4 py-3">
                    <Link href={`/employees/${emp.id}`} className="font-medium hover:text-primary">
                      {emp.lastName}, {emp.firstName}
                    </Link>
                  </td>
                  <td className="px-4 py-3 text-muted-foreground">{emp.position}</td>
                  {role === "ADMIN" && (
                    <td className="px-4 py-3">
                      <Badge variant="outline">{emp.kita.shortCode}</Badge>
                    </td>
                  )}
                  <td className="px-4 py-3 text-muted-foreground">
                    {CONTRACT_TYPE_LABELS[emp.contractType] ?? emp.contractType}
                  </td>
                  <td className="px-4 py-3">{emp.weeklyHours} Std.</td>
                  <td className="px-4 py-3 text-muted-foreground">{formatDate(emp.startDate)}</td>
                  <td className="px-4 py-3">
                    <Badge variant={emp.isActive ? "success" : "secondary"}>
                      {emp.isActive ? "Aktiv" : "Inaktiv"}
                    </Badge>
                  </td>
                  <td className="px-4 py-3">
                    <Link href={`/employees/${emp.id}`} className="text-xs text-primary hover:underline">
                      Anzeigen
                    </Link>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}
