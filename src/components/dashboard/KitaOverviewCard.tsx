import Link from "next/link"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { FirstAidWidget } from "./FirstAidWidget"
import { Users, Clock, ArrowRight } from "lucide-react"

interface KitaOverviewCardProps {
  kitaId: string
  kitaName: string
  shortCode: string
  activeEmployeeCount: number
  totalWeeklyHours: number
  firstAidCount: number
  minFirstAid: number
  firstAidOk: boolean
  firstAidEmployees: { id: string; name: string; expiryDate: Date | null }[]
}

export function KitaOverviewCard({
  kitaId,
  kitaName,
  shortCode,
  activeEmployeeCount,
  totalWeeklyHours,
  firstAidCount,
  minFirstAid,
  firstAidOk,
  firstAidEmployees,
}: KitaOverviewCardProps) {
  return (
    <Card className="hover:shadow-md transition-shadow">
      <CardHeader className="pb-3">
        <div className="flex items-center justify-between">
          <CardTitle className="text-base">{kitaName}</CardTitle>
          <Link
            href={`/kitas/${kitaId}`}
            className="text-xs text-primary hover:underline flex items-center gap-1"
          >
            Details <ArrowRight className="h-3 w-3" />
          </Link>
        </div>
      </CardHeader>
      <CardContent className="space-y-3">
        <div className="grid grid-cols-2 gap-3">
          <div className="flex items-center gap-2">
            <Users className="h-4 w-4 text-muted-foreground" />
            <div>
              <p className="text-xl font-bold">{activeEmployeeCount}</p>
              <p className="text-xs text-muted-foreground">Mitarbeiter</p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Clock className="h-4 w-4 text-muted-foreground" />
            <div>
              <p className="text-xl font-bold">{Math.round(totalWeeklyHours)}</p>
              <p className="text-xs text-muted-foreground">Std./Woche</p>
            </div>
          </div>
        </div>
        <FirstAidWidget
          kitaName={kitaName}
          firstAidCount={firstAidCount}
          minFirstAid={minFirstAid}
          firstAidOk={firstAidOk}
          employees={firstAidEmployees}
          compact
        />
      </CardContent>
    </Card>
  )
}
