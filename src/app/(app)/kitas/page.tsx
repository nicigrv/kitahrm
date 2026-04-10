import { requireAdmin } from "@/lib/auth-helpers"
import { prisma } from "@/lib/prisma"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import Link from "next/link"
import { Building2, Users, Clock, ArrowRight, ShieldCheck, ShieldAlert } from "lucide-react"

export default async function KitasPage() {
  await requireAdmin()

  const kitas = await prisma.kita.findMany({
    orderBy: { name: "asc" },
    include: {
      employees: {
        where: { isActive: true },
        select: { weeklyHours: true },
      },
      users: { select: { id: true, role: true } },
      _count: { select: { employees: true } },
    },
  })

  // First-aid coverage per KITA
  const now = new Date()
  const ehCompletions = await prisma.trainingCompletion.findMany({
    where: {
      category: { isFirstAid: true },
      OR: [{ expiryDate: null }, { expiryDate: { gte: now } }],
      employee: { isActive: true },
    },
    select: { employee: { select: { kitaId: true } } },
  })

  const ehByKita = new Map<string, number>()
  for (const c of ehCompletions) {
    const kid = c.employee.kitaId
    ehByKita.set(kid, (ehByKita.get(kid) ?? 0) + 1)
  }

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Einrichtungen</h1>
        <p className="text-muted-foreground text-sm mt-1">
          Alle {kitas.length} Einrichtungen des Trägers
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {kitas.map((kita) => {
          const activeEmployees = kita.employees.length
          const totalHours = kita.employees.reduce((s, e) => s + e.weeklyHours, 0)
          const ehCount = ehByKita.get(kita.id) ?? 0
          const ehOk = ehCount >= kita.minFirstAid

          return (
            <Card key={kita.id} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-3">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100">
                      <Building2 className="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                      <CardTitle className="text-base">{kita.name}</CardTitle>
                      <p className="text-xs text-muted-foreground">{kita.shortCode}</p>
                    </div>
                  </div>
                  <Button variant="ghost" size="sm" asChild>
                    <Link href={`/kitas/${kita.id}`}>
                      <ArrowRight className="h-4 w-4" />
                    </Link>
                  </Button>
                </div>
              </CardHeader>
              <CardContent className="space-y-3">
                {kita.address && (
                  <p className="text-xs text-muted-foreground">{kita.address}</p>
                )}
                <div className="grid grid-cols-2 gap-2">
                  <div className="flex items-center gap-1.5 text-sm">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    <span className="font-medium">{activeEmployees}</span>
                    <span className="text-muted-foreground">Mitarbeiter</span>
                  </div>
                  <div className="flex items-center gap-1.5 text-sm">
                    <Clock className="h-4 w-4 text-muted-foreground" />
                    <span className="font-medium">{Math.round(totalHours)}</span>
                    <span className="text-muted-foreground">Std./Woche</span>
                  </div>
                </div>
                <div className={`flex items-center gap-2 rounded-lg px-3 py-2 text-sm ${ehOk ? "bg-green-50 border border-green-200" : "bg-red-50 border border-red-200"}`}>
                  {ehOk
                    ? <ShieldCheck className="h-4 w-4 text-green-600 shrink-0" />
                    : <ShieldAlert className="h-4 w-4 text-red-600 shrink-0" />}
                  <span className={ehOk ? "text-green-700" : "text-red-700"}>
                    <strong>{ehCount}/{kita.minFirstAid}</strong> EH-Zertifizierte
                  </span>
                </div>
              </CardContent>
            </Card>
          )
        })}
      </div>
    </div>
  )
}
