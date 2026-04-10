import { requireAuth } from "@/lib/auth-helpers"
import { getDashboardData } from "@/lib/queries/dashboard"
import { KitaOverviewCard } from "@/components/dashboard/KitaOverviewCard"
import { ExpiryAlertsTable } from "@/components/dashboard/ExpiryAlertsTable"
import { StaffingChart } from "@/components/dashboard/StaffingChart"
import { FirstAidWidget } from "@/components/dashboard/FirstAidWidget"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { AlertTriangle, Users, Building2, Shield } from "lucide-react"
import type { UserRole } from "@/types/next-auth"

export default async function DashboardPage() {
  const session = await requireAuth()
  const { role, kitaId } = session.user
  const { kitaStats, allExpiryAlerts } = await getDashboardData(role as UserRole, kitaId)

  const isAdmin = role === "ADMIN"
  const totalEmployees = kitaStats.reduce((s, k) => s + k.activeEmployeeCount, 0)
  const totalHours = kitaStats.reduce((s, k) => s + k.totalWeeklyHours, 0)
  const kitasOk = kitaStats.filter((k) => k.firstAidOk).length
  const alertCount = allExpiryAlerts.length

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Dashboard</h1>
        <p className="text-muted-foreground text-sm mt-1">
          {isAdmin ? "Übersicht aller Einrichtungen" : `${kitaStats[0]?.kitaName ?? "Einrichtung"} – Personalübersicht`}
        </p>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <Users className="h-8 w-8 text-blue-500" />
              <div>
                <p className="text-2xl font-bold">{totalEmployees}</p>
                <p className="text-xs text-muted-foreground">Aktive Mitarbeiter</p>
              </div>
            </div>
          </CardContent>
        </Card>
        {isAdmin && (
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center gap-3">
                <Building2 className="h-8 w-8 text-purple-500" />
                <div>
                  <p className="text-2xl font-bold">{kitaStats.length}</p>
                  <p className="text-xs text-muted-foreground">Einrichtungen</p>
                </div>
              </div>
            </CardContent>
          </Card>
        )}
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <Shield className={`h-8 w-8 ${kitasOk === kitaStats.length ? "text-green-500" : "text-red-500"}`} />
              <div>
                <p className="text-2xl font-bold">{kitasOk}/{kitaStats.length}</p>
                <p className="text-xs text-muted-foreground">EH-Status OK</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center gap-3">
              <AlertTriangle className={`h-8 w-8 ${alertCount > 0 ? "text-yellow-500" : "text-green-500"}`} />
              <div>
                <p className="text-2xl font-bold">{alertCount}</p>
                <p className="text-xs text-muted-foreground">Ablauf-Warnungen</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Admin: KITA Grid + Charts */}
      {isAdmin ? (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {kitaStats.map((stat) => (
              <KitaOverviewCard
                key={stat.kitaId}
                kitaId={stat.kitaId}
                kitaName={stat.kitaName}
                shortCode={stat.shortCode}
                activeEmployeeCount={stat.activeEmployeeCount}
                totalWeeklyHours={stat.totalWeeklyHours}
                firstAidCount={stat.firstAidCount}
                minFirstAid={stat.minFirstAid}
                firstAidOk={stat.firstAidOk}
                firstAidEmployees={stat.firstAidEmployees}
              />
            ))}
          </div>

          <Card>
            <CardHeader>
              <CardTitle className="text-base">Personalauslastung je Einrichtung</CardTitle>
            </CardHeader>
            <CardContent>
              <StaffingChart data={kitaStats} />
            </CardContent>
          </Card>
        </>
      ) : (
        /* KITA View: Single KITA detailed */
        kitaStats[0] && (
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <Card>
              <CardHeader>
                <CardTitle className="text-base">Erste Hilfe Abdeckung</CardTitle>
              </CardHeader>
              <CardContent>
                <FirstAidWidget
                  kitaName={kitaStats[0].kitaName}
                  firstAidCount={kitaStats[0].firstAidCount}
                  minFirstAid={kitaStats[0].minFirstAid}
                  firstAidOk={kitaStats[0].firstAidOk}
                  employees={kitaStats[0].firstAidEmployees}
                />
              </CardContent>
            </Card>
            <Card>
              <CardContent className="pt-6 space-y-4">
                <div className="flex justify-between items-center py-3 border-b">
                  <span className="text-sm text-muted-foreground">Aktive Mitarbeiter</span>
                  <span className="font-bold">{kitaStats[0].activeEmployeeCount}</span>
                </div>
                <div className="flex justify-between items-center py-3 border-b">
                  <span className="text-sm text-muted-foreground">Wochenstunden gesamt</span>
                  <span className="font-bold">{Math.round(kitaStats[0].totalWeeklyHours)} Std.</span>
                </div>
                <div className="flex justify-between items-center py-3">
                  <span className="text-sm text-muted-foreground">EH-Zertifizierte</span>
                  <span className="font-bold">{kitaStats[0].firstAidCount} Personen</span>
                </div>
              </CardContent>
            </Card>
          </div>
        )
      )}

      {/* Expiry Alerts */}
      <Card>
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <AlertTriangle className="h-4 w-4 text-yellow-500" />
            Ablaufende Zertifikate (nächste 60 Tage)
          </CardTitle>
        </CardHeader>
        <CardContent>
          <ExpiryAlertsTable alerts={allExpiryAlerts} showKita={isAdmin} />
        </CardContent>
      </Card>
    </div>
  )
}
