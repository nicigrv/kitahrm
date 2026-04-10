import { Shield, ShieldCheck, ShieldAlert } from "lucide-react"
import { formatDate } from "@/lib/utils"

interface FirstAidEmployee {
  id: string
  name: string
  expiryDate: Date | null
}

interface FirstAidWidgetProps {
  kitaName: string
  firstAidCount: number
  minFirstAid: number
  firstAidOk: boolean
  employees: FirstAidEmployee[]
  compact?: boolean
}

export function FirstAidWidget({ kitaName, firstAidCount, minFirstAid, firstAidOk, employees, compact }: FirstAidWidgetProps) {
  if (compact) {
    return (
      <div className={`flex items-center gap-2 px-3 py-2 rounded-lg ${firstAidOk ? "bg-green-50 border border-green-200" : "bg-red-50 border border-red-200"}`}>
        {firstAidOk
          ? <ShieldCheck className="h-4 w-4 text-green-600 shrink-0" />
          : <ShieldAlert className="h-4 w-4 text-red-600 shrink-0" />}
        <span className="text-sm">
          <span className={`font-semibold ${firstAidOk ? "text-green-700" : "text-red-700"}`}>
            {firstAidCount}/{minFirstAid}
          </span>
          <span className="text-muted-foreground ml-1">EH-Zertifizierte</span>
        </span>
      </div>
    )
  }

  return (
    <div className={`rounded-lg border p-4 ${firstAidOk ? "border-green-200 bg-green-50" : "border-red-200 bg-red-50"}`}>
      <div className="flex items-center gap-2 mb-3">
        {firstAidOk
          ? <ShieldCheck className="h-5 w-5 text-green-600" />
          : <ShieldAlert className="h-5 w-5 text-red-600" />}
        <h4 className={`font-semibold ${firstAidOk ? "text-green-800" : "text-red-800"}`}>
          Erste Hilfe
        </h4>
        <span className={`ml-auto text-sm font-bold ${firstAidOk ? "text-green-700" : "text-red-700"}`}>
          {firstAidCount} / {minFirstAid} mind. erforderlich
        </span>
      </div>
      {!firstAidOk && (
        <p className="text-sm text-red-700 mb-2">
          Achtung: Zu wenig gültige Erste-Hilfe-Zertifikate!
        </p>
      )}
      {employees.length > 0 && (
        <ul className="space-y-1">
          {employees.map((emp) => (
            <li key={emp.id} className="flex items-center justify-between text-xs">
              <span className={firstAidOk ? "text-green-700" : "text-red-700"}>{emp.name}</span>
              <span className="text-muted-foreground">gültig bis {formatDate(emp.expiryDate)}</span>
            </li>
          ))}
        </ul>
      )}
      {employees.length === 0 && (
        <p className="text-sm text-red-600">Keine gültigen EH-Zertifikate vorhanden</p>
      )}
    </div>
  )
}
