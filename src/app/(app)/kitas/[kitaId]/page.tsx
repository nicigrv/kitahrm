import { requireAdmin } from "@/lib/auth-helpers"
import { prisma } from "@/lib/prisma"
import { notFound } from "next/navigation"
import { formatDate, CONTRACT_TYPE_LABELS } from "@/lib/utils"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import Link from "next/link"
import { ArrowLeft, Users, Clock, Phone, Mail, MapPin } from "lucide-react"
import { KitaSettingsForm } from "@/components/kitas/KitaSettingsForm"

export default async function KitaDetailPage({
  params,
}: {
  params: { kitaId: string }
}) {
  await requireAdmin()

  const kita = await prisma.kita.findUnique({
    where: { id: params.kitaId },
    include: {
      employees: {
        where: { isActive: true },
        orderBy: [{ lastName: "asc" }, { firstName: "asc" }],
      },
      users: {
        select: { id: true, name: true, email: true, role: true },
      },
    },
  })

  if (!kita) notFound()

  const totalHours = kita.employees.reduce((s, e) => s + e.weeklyHours, 0)

  return (
    <div className="space-y-6 max-w-5xl">
      <div className="flex items-center gap-3">
        <Button variant="ghost" size="sm" asChild>
          <Link href="/kitas">
            <ArrowLeft className="h-4 w-4 mr-1" />
            Zurück
          </Link>
        </Button>
        <h1 className="text-2xl font-bold">{kita.name}</h1>
        <Badge variant="outline">{kita.shortCode}</Badge>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Left: Info + Settings */}
        <div className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Kontaktdaten</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2 text-sm">
              {kita.address && (
                <div className="flex gap-2">
                  <MapPin className="h-4 w-4 text-muted-foreground mt-0.5 shrink-0" />
                  <span>{kita.address}</span>
                </div>
              )}
              {kita.phone && (
                <div className="flex gap-2">
                  <Phone className="h-4 w-4 text-muted-foreground shrink-0" />
                  <span>{kita.phone}</span>
                </div>
              )}
              {kita.email && (
                <div className="flex gap-2">
                  <Mail className="h-4 w-4 text-muted-foreground shrink-0" />
                  <span>{kita.email}</span>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Statistiken</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-sm">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Aktive Mitarbeiter</span>
                <span className="font-bold">{kita.employees.length}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Wochenstunden</span>
                <span className="font-bold">{Math.round(totalHours)} Std.</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Mind. EH-Personen</span>
                <span className="font-bold">{kita.minFirstAid}</span>
              </div>
            </CardContent>
          </Card>

          <KitaSettingsForm kita={{ id: kita.id, name: kita.name, address: kita.address, phone: kita.phone, email: kita.email, minFirstAid: kita.minFirstAid }} />
        </div>

        {/* Right: Employee list */}
        <div className="lg:col-span-2">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle className="text-sm flex items-center gap-2">
                  <Users className="h-4 w-4" />
                  Mitarbeiter ({kita.employees.length})
                </CardTitle>
                <Button size="sm" asChild>
                  <Link href={`/employees/new`}>Neu anlegen</Link>
                </Button>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              <table className="w-full text-sm">
                <thead className="bg-muted/50 border-b">
                  <tr>
                    <th className="text-left px-4 py-3 font-medium text-muted-foreground">Name</th>
                    <th className="text-left px-4 py-3 font-medium text-muted-foreground">Position</th>
                    <th className="text-left px-4 py-3 font-medium text-muted-foreground">Vertrag</th>
                    <th className="text-left px-4 py-3 font-medium text-muted-foreground">Std.</th>
                    <th className="px-4 py-3" />
                  </tr>
                </thead>
                <tbody>
                  {kita.employees.map((emp) => (
                    <tr key={emp.id} className="border-b last:border-0 hover:bg-muted/20">
                      <td className="px-4 py-3 font-medium">
                        {emp.lastName}, {emp.firstName}
                      </td>
                      <td className="px-4 py-3 text-muted-foreground">{emp.position}</td>
                      <td className="px-4 py-3 text-muted-foreground">
                        {CONTRACT_TYPE_LABELS[emp.contractType] ?? emp.contractType}
                      </td>
                      <td className="px-4 py-3">{emp.weeklyHours}</td>
                      <td className="px-4 py-3">
                        <Link
                          href={`/employees/${emp.id}`}
                          className="text-xs text-primary hover:underline"
                        >
                          Anzeigen
                        </Link>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  )
}
