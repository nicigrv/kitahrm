import { requireAuth } from "@/lib/auth-helpers"
import { prisma } from "@/lib/prisma"
import { getEmployees } from "@/lib/queries/employees"
import { formatDate } from "@/lib/utils"
import { ExpiryBadge } from "@/components/employees/ExpiryBadge"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import Link from "next/link"
import { GraduationCap, Settings } from "lucide-react"
import type { UserRole } from "@/types/next-auth"

export default async function TrainingOverviewPage() {
  const session = await requireAuth()
  const { role, kitaId } = session.user

  const [employees, categories] = await Promise.all([
    getEmployees(role as UserRole, kitaId),
    prisma.trainingCategory.findMany({
      where: { isActive: true },
      orderBy: [{ sortOrder: "asc" }, { name: "asc" }],
    }),
  ])

  const activeEmployees = employees.filter((e) => e.isActive)

  // Build matrix: employee -> category -> latest completion
  const completions = await prisma.trainingCompletion.findMany({
    where: {
      employee: { kitaId: role === "ADMIN" ? undefined : kitaId ?? undefined },
    },
    include: { category: true },
    orderBy: { completedDate: "desc" },
  })

  // Map: employeeId -> categoryId -> completion
  const matrix = new Map<string, Map<string, (typeof completions)[0]>>()
  for (const comp of completions) {
    if (!matrix.has(comp.employeeId)) {
      matrix.set(comp.employeeId, new Map())
    }
    const empMap = matrix.get(comp.employeeId)!
    if (!empMap.has(comp.categoryId)) {
      empMap.set(comp.categoryId, comp)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">Schulungen & Zertifikate</h1>
          <p className="text-muted-foreground text-sm mt-1">
            Übersicht aller Schulungen
          </p>
        </div>
        {role === "ADMIN" && (
          <Button variant="outline" asChild>
            <Link href="/training/categories">
              <Settings className="h-4 w-4 mr-2" />
              Kategorien verwalten
            </Link>
          </Button>
        )}
      </div>

      {/* Category summary */}
      <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        {categories.map((cat) => {
          const catCompletions = completions.filter((c) => c.categoryId === cat.id)
          const validCount = catCompletions.filter(
            (c) => !c.expiryDate || c.expiryDate > new Date()
          ).length
          return (
            <Card key={cat.id} className="text-center">
              <CardContent className="pt-4 pb-3">
                <p className="text-xl font-bold">{validCount}</p>
                <p className="text-xs text-muted-foreground mt-1">{cat.name}</p>
                {cat.isFirstAid && (
                  <Badge variant="secondary" className="text-xs mt-1">EH</Badge>
                )}
              </CardContent>
            </Card>
          )
        })}
      </div>

      {/* Training Matrix */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <GraduationCap className="h-4 w-4" />
            Schulungsmatrix
          </CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-muted/50 border-b">
                <tr>
                  <th className="text-left px-4 py-3 font-medium text-muted-foreground min-w-[180px]">
                    Mitarbeiter
                  </th>
                  {role === "ADMIN" && (
                    <th className="text-left px-4 py-3 font-medium text-muted-foreground">
                      Einrichtung
                    </th>
                  )}
                  {categories.map((cat) => (
                    <th
                      key={cat.id}
                      className="text-center px-3 py-3 font-medium text-muted-foreground min-w-[120px]"
                    >
                      <div>{cat.name}</div>
                      {cat.validityMonths && (
                        <div className="text-xs font-normal">({cat.validityMonths} Mo.)</div>
                      )}
                    </th>
                  ))}
                  <th className="px-4 py-3" />
                </tr>
              </thead>
              <tbody>
                {activeEmployees.map((emp) => {
                  const empMatrix = matrix.get(emp.id)
                  return (
                    <tr key={emp.id} className="border-b last:border-0 hover:bg-muted/20">
                      <td className="px-4 py-3">
                        <Link
                          href={`/employees/${emp.id}`}
                          className="font-medium hover:text-primary"
                        >
                          {emp.lastName}, {emp.firstName}
                        </Link>
                        <div className="text-xs text-muted-foreground">{emp.position}</div>
                      </td>
                      {role === "ADMIN" && (
                        <td className="px-4 py-3">
                          <Badge variant="outline" className="text-xs">
                            {emp.kita.shortCode}
                          </Badge>
                        </td>
                      )}
                      {categories.map((cat) => {
                        const comp = empMatrix?.get(cat.id)
                        return (
                          <td key={cat.id} className="px-3 py-3 text-center">
                            {comp ? (
                              <div className="flex flex-col items-center gap-1">
                                <ExpiryBadge expiryDate={comp.expiryDate} />
                                <span className="text-xs text-muted-foreground">
                                  {formatDate(comp.completedDate)}
                                </span>
                              </div>
                            ) : (
                              <span className="text-xs text-muted-foreground">—</span>
                            )}
                          </td>
                        )
                      })}
                      <td className="px-4 py-3">
                        <Link
                          href={`/employees/${emp.id}/training`}
                          className="text-xs text-primary hover:underline whitespace-nowrap"
                        >
                          Bearbeiten
                        </Link>
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
