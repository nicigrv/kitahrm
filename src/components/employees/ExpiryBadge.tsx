import { Badge } from "@/components/ui/badge"
import { getExpiryStatus, formatDate } from "@/lib/utils"

interface ExpiryBadgeProps {
  expiryDate: Date | string | null | undefined
  showDate?: boolean
}

export function ExpiryBadge({ expiryDate, showDate }: ExpiryBadgeProps) {
  const status = getExpiryStatus(expiryDate)

  if (status === "none") {
    return <Badge variant="secondary">Kein Ablauf</Badge>
  }

  if (status === "expired") {
    return (
      <Badge variant="danger">
        Abgelaufen {showDate && expiryDate ? `(${formatDate(expiryDate)})` : ""}
      </Badge>
    )
  }

  if (status === "warning") {
    return (
      <Badge variant="warning">
        Läuft ab {showDate && expiryDate ? formatDate(expiryDate) : ""}
      </Badge>
    )
  }

  return (
    <Badge variant="success">
      Gültig {showDate && expiryDate ? `bis ${formatDate(expiryDate)}` : ""}
    </Badge>
  )
}
