import { formatDate } from "@/lib/utils"
import { AlertTriangle, AlertCircle } from "lucide-react"
import type { ExpiryAlert } from "@/lib/queries/dashboard"

interface ExpiryAlertsTableProps {
  alerts: ExpiryAlert[]
  showKita?: boolean
}

export function ExpiryAlertsTable({ alerts, showKita }: ExpiryAlertsTableProps) {
  if (alerts.length === 0) {
    return (
      <div className="text-center py-8 text-muted-foreground text-sm">
        Keine ablaufenden Zertifikate in den nächsten 60 Tagen
      </div>
    )
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b">
            <th className="text-left py-2 px-3 text-muted-foreground font-medium">Status</th>
            <th className="text-left py-2 px-3 text-muted-foreground font-medium">Mitarbeiter</th>
            {showKita && <th className="text-left py-2 px-3 text-muted-foreground font-medium">Einrichtung</th>}
            <th className="text-left py-2 px-3 text-muted-foreground font-medium">Schulung</th>
            <th className="text-left py-2 px-3 text-muted-foreground font-medium">Ablaufdatum</th>
          </tr>
        </thead>
        <tbody>
          {alerts.map((alert, i) => (
            <tr key={i} className="border-b last:border-0 hover:bg-muted/30">
              <td className="py-2 px-3">
                {alert.isExpired ? (
                  <span className="flex items-center gap-1 text-red-600 font-medium">
                    <AlertCircle className="h-3.5 w-3.5" />
                    Abgelaufen
                  </span>
                ) : (
                  <span className="flex items-center gap-1 text-yellow-600 font-medium">
                    <AlertTriangle className="h-3.5 w-3.5" />
                    Läuft ab
                  </span>
                )}
              </td>
              <td className="py-2 px-3 font-medium">{alert.employeeName}</td>
              {showKita && <td className="py-2 px-3 text-muted-foreground">{alert.kitaName}</td>}
              <td className="py-2 px-3">{alert.categoryName}</td>
              <td className={`py-2 px-3 ${alert.isExpired ? "text-red-600 font-medium" : "text-yellow-600"}`}>
                {formatDate(alert.expiryDate)}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
