import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"
import { format, formatDistanceToNow, differenceInDays } from "date-fns"
import { de } from "date-fns/locale"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatDate(date: Date | string | null | undefined): string {
  if (!date) return "—"
  return format(new Date(date), "dd.MM.yyyy", { locale: de })
}

export function formatDateLong(date: Date | string | null | undefined): string {
  if (!date) return "—"
  return format(new Date(date), "dd. MMMM yyyy", { locale: de })
}

export function formatRelative(date: Date | string | null | undefined): string {
  if (!date) return "—"
  return formatDistanceToNow(new Date(date), { addSuffix: true, locale: de })
}

export function getExpiryStatus(expiryDate: Date | string | null | undefined): "valid" | "warning" | "expired" | "none" {
  if (!expiryDate) return "none"
  const days = differenceInDays(new Date(expiryDate), new Date())
  if (days < 0) return "expired"
  if (days <= 60) return "warning"
  return "valid"
}

export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

export const CONTRACT_TYPE_LABELS: Record<string, string> = {
  UNBEFRISTET: "Unbefristet",
  BEFRISTET: "Befristet",
  MINIJOB: "Minijob",
  AUSBILDUNG: "Ausbildung",
  PRAKTIKUM: "Praktikum",
  ELTERNZEIT: "Elternzeit",
}

export const ROLE_LABELS: Record<string, string> = {
  ADMIN: "Träger-Admin",
  KITA_MANAGER: "KITA-Leitung",
  KITA_STAFF: "KITA-Mitarbeiter",
}
