import { requireAuth } from "@/lib/auth-helpers"
import { getEmployee } from "@/lib/queries/employees"
import { notFound } from "next/navigation"
import { formatDate, formatDateLong, CONTRACT_TYPE_LABELS } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs"
import Link from "next/link"
import { ArrowLeft, Edit, FileText, GraduationCap, User } from "lucide-react"
import { ExpiryBadge } from "@/components/employees/ExpiryBadge"
import type { UserRole } from "@/types/next-auth"

export default async function EmployeeDetailPage({
  params,
}: {
  params: { employeeId: string }
}) {
  const session = await requireAuth()
  const { role, kitaId } = session.user
  const canEdit = role === "ADMIN" || role === "KITA_MANAGER"

  const employee = await getEmployee(params.employeeId, role as UserRole, kitaId)
  if (!employee) notFound()

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="sm" asChild>
          <Link href="/employees">
            <ArrowLeft className="h-4 w-4 mr-1" />
            Zurück
          </Link>
        </Button>
        <div className="flex-1">
          <h1 className="text-2xl font-bold">
            {employee.firstName} {employee.lastName}
          </h1>
          <p className="text-muted-foreground text-sm">
            {employee.position} · {employee.kita.name}
          </p>
        </div>
        <div className="flex items-center gap-2">
          <Badge variant={employee.isActive ? "success" : "secondary"}>
            {employee.isActive ? "Aktiv" : "Inaktiv"}
          </Badge>
          {canEdit && (
            <Button asChild size="sm">
              <Link href={`/employees/${employee.id}/edit`}>
                <Edit className="h-4 w-4 mr-1" />
                Bearbeiten
              </Link>
            </Button>
          )}
        </div>
      </div>

      <Tabs defaultValue="stammdaten">
        <TabsList>
          <TabsTrigger value="stammdaten">
            <User className="h-4 w-4 mr-1" />
            Stammdaten
          </TabsTrigger>
          <TabsTrigger value="dokumente">
            <FileText className="h-4 w-4 mr-1" />
            Dokumente ({employee.documents.length})
          </TabsTrigger>
          <TabsTrigger value="schulungen">
            <GraduationCap className="h-4 w-4 mr-1" />
            Schulungen ({employee.completions.length})
          </TabsTrigger>
        </TabsList>

        {/* Stammdaten */}
        <TabsContent value="stammdaten">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <Card>
              <CardHeader>
                <CardTitle className="text-sm">Persönliche Daten</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 text-sm">
                <Row label="Vorname" value={employee.firstName} />
                <Row label="Nachname" value={employee.lastName} />
                <Row label="Geburtsdatum" value={formatDateLong(employee.birthDate)} />
                <Row label="E-Mail" value={employee.email || "—"} />
                <Row label="Telefon" value={employee.phone || "—"} />
                <Row label="Adresse" value={employee.address || "—"} />
              </CardContent>
            </Card>
            <Card>
              <CardHeader>
                <CardTitle className="text-sm">Beschäftigung</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 text-sm">
                <Row label="Einrichtung" value={employee.kita.name} />
                <Row label="Position" value={employee.position} />
                <Row label="Vertragsart" value={CONTRACT_TYPE_LABELS[employee.contractType] ?? employee.contractType} />
                <Row label="Wochenstunden" value={`${employee.weeklyHours} Std.`} />
                <Row label="Beschäftigt seit" value={formatDate(employee.startDate)} />
                {employee.endDate && <Row label="Beschäftigt bis" value={formatDate(employee.endDate)} />}
                {employee.notes && <Row label="Notizen" value={employee.notes} />}
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Dokumente */}
        <TabsContent value="dokumente">
          <div className="mt-4 space-y-3">
            {canEdit && (
              <Button asChild size="sm">
                <Link href={`/employees/${employee.id}/documents`}>
                  <FileText className="h-4 w-4 mr-1" />
                  Dokumente verwalten
                </Link>
              </Button>
            )}
            {employee.documents.length === 0 ? (
              <div className="text-center py-10 text-muted-foreground border rounded-lg">
                Noch keine Dokumente hochgeladen
              </div>
            ) : (
              <div className="border rounded-lg overflow-hidden">
                <table className="w-full text-sm">
                  <thead className="bg-muted/50 border-b">
                    <tr>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Dateiname</th>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Bezeichnung</th>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Hochgeladen</th>
                      <th className="px-4 py-3" />
                    </tr>
                  </thead>
                  <tbody>
                    {employee.documents.map((doc) => (
                      <tr key={doc.id} className="border-b last:border-0 hover:bg-muted/20">
                        <td className="px-4 py-3 font-medium">{doc.fileName}</td>
                        <td className="px-4 py-3 text-muted-foreground">{doc.label || "—"}</td>
                        <td className="px-4 py-3 text-muted-foreground">{formatDate(doc.uploadedAt)}</td>
                        <td className="px-4 py-3">
                          <a
                            href={`/api/employees/${employee.id}/documents/${doc.id}/download`}
                            className="text-xs text-primary hover:underline"
                            target="_blank"
                          >
                            Download
                          </a>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </TabsContent>

        {/* Schulungen */}
        <TabsContent value="schulungen">
          <div className="mt-4 space-y-3">
            {canEdit && (
              <Button asChild size="sm">
                <Link href={`/employees/${employee.id}/training`}>
                  <GraduationCap className="h-4 w-4 mr-1" />
                  Schulungen verwalten
                </Link>
              </Button>
            )}
            {employee.completions.length === 0 ? (
              <div className="text-center py-10 text-muted-foreground border rounded-lg">
                Noch keine Schulungen eingetragen
              </div>
            ) : (
              <div className="border rounded-lg overflow-hidden">
                <table className="w-full text-sm">
                  <thead className="bg-muted/50 border-b">
                    <tr>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Schulung</th>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Absolviert</th>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Gültig bis</th>
                      <th className="text-left px-4 py-3 font-medium text-muted-foreground">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {employee.completions.map((comp) => (
                      <tr key={comp.id} className="border-b last:border-0 hover:bg-muted/20">
                        <td className="px-4 py-3 font-medium">
                          {comp.category.name}
                          {comp.category.isFirstAid && (
                            <Badge variant="secondary" className="ml-2 text-xs">EH</Badge>
                          )}
                        </td>
                        <td className="px-4 py-3 text-muted-foreground">{formatDate(comp.completedDate)}</td>
                        <td className="px-4 py-3 text-muted-foreground">{formatDate(comp.expiryDate)}</td>
                        <td className="px-4 py-3">
                          <ExpiryBadge expiryDate={comp.expiryDate} />
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </TabsContent>
      </Tabs>
    </div>
  )
}

function Row({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex justify-between py-1 border-b border-dashed border-muted last:border-0">
      <span className="text-muted-foreground">{label}</span>
      <span className="font-medium text-right max-w-[60%]">{value}</span>
    </div>
  )
}
