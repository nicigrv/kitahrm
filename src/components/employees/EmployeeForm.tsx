"use client"

import { useState } from "react"
import { useRouter } from "next/navigation"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Loader2 } from "lucide-react"

interface Kita {
  id: string
  name: string
}

interface EmployeeFormProps {
  kitas: Kita[]
  defaultKitaId?: string
  employee?: {
    id: string
    firstName: string
    lastName: string
    email?: string | null
    phone?: string | null
    address?: string | null
    birthDate?: Date | null
    position: string
    startDate: Date
    endDate?: Date | null
    contractType: string
    weeklyHours: number
    isActive: boolean
    notes?: string | null
    kitaId: string
  }
}

const CONTRACT_TYPES = [
  { value: "UNBEFRISTET", label: "Unbefristet" },
  { value: "BEFRISTET", label: "Befristet" },
  { value: "MINIJOB", label: "Minijob" },
  { value: "AUSBILDUNG", label: "Ausbildung" },
  { value: "PRAKTIKUM", label: "Praktikum" },
  { value: "ELTERNZEIT", label: "Elternzeit" },
]

function toDateInput(d: Date | null | undefined): string {
  if (!d) return ""
  const date = new Date(d)
  return date.toISOString().split("T")[0]
}

export function EmployeeForm({ kitas, defaultKitaId, employee }: EmployeeFormProps) {
  const router = useRouter()
  const isEdit = !!employee

  const [loading, setLoading] = useState(false)
  const [error, setError] = useState("")

  const [form, setForm] = useState({
    firstName: employee?.firstName ?? "",
    lastName: employee?.lastName ?? "",
    email: employee?.email ?? "",
    phone: employee?.phone ?? "",
    address: employee?.address ?? "",
    birthDate: toDateInput(employee?.birthDate),
    position: employee?.position ?? "",
    startDate: toDateInput(employee?.startDate) || new Date().toISOString().split("T")[0],
    endDate: toDateInput(employee?.endDate),
    contractType: employee?.contractType ?? "UNBEFRISTET",
    weeklyHours: String(employee?.weeklyHours ?? 39),
    isActive: employee?.isActive ?? true,
    notes: employee?.notes ?? "",
    kitaId: employee?.kitaId ?? defaultKitaId ?? kitas[0]?.id ?? "",
  })

  function set(field: string, value: string | boolean) {
    setForm((prev) => ({ ...prev, [field]: value }))
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError("")
    setLoading(true)

    const payload = {
      ...form,
      weeklyHours: parseFloat(form.weeklyHours),
    }

    try {
      const url = isEdit ? `/api/employees/${employee!.id}` : "/api/employees"
      const method = isEdit ? "PATCH" : "POST"
      const res = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })

      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || "Fehler beim Speichern")
      }

      const result = await res.json()
      router.push(`/employees/${result.id}`)
      router.refresh()
    } catch (err) {
      setError(err instanceof Error ? err.message : "Unbekannter Fehler")
    } finally {
      setLoading(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {error && (
        <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">{error}</div>
      )}

      <Card>
        <CardHeader><CardTitle className="text-sm">Persönliche Daten</CardTitle></CardHeader>
        <CardContent className="grid grid-cols-2 gap-4">
          <div className="space-y-1">
            <Label>Vorname *</Label>
            <Input value={form.firstName} onChange={(e) => set("firstName", e.target.value)} required />
          </div>
          <div className="space-y-1">
            <Label>Nachname *</Label>
            <Input value={form.lastName} onChange={(e) => set("lastName", e.target.value)} required />
          </div>
          <div className="space-y-1">
            <Label>Geburtsdatum</Label>
            <Input type="date" value={form.birthDate} onChange={(e) => set("birthDate", e.target.value)} />
          </div>
          <div className="space-y-1">
            <Label>E-Mail</Label>
            <Input type="email" value={form.email} onChange={(e) => set("email", e.target.value)} />
          </div>
          <div className="space-y-1">
            <Label>Telefon</Label>
            <Input value={form.phone} onChange={(e) => set("phone", e.target.value)} />
          </div>
          <div className="space-y-1 col-span-2">
            <Label>Adresse</Label>
            <Input value={form.address} onChange={(e) => set("address", e.target.value)} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader><CardTitle className="text-sm">Beschäftigung</CardTitle></CardHeader>
        <CardContent className="grid grid-cols-2 gap-4">
          <div className="space-y-1">
            <Label>Einrichtung *</Label>
            <Select value={form.kitaId} onValueChange={(v) => set("kitaId", v)}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {kitas.map((k) => (
                  <SelectItem key={k.id} value={k.id}>{k.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-1">
            <Label>Position *</Label>
            <Input value={form.position} onChange={(e) => set("position", e.target.value)} required placeholder="z.B. Erzieherin" />
          </div>
          <div className="space-y-1">
            <Label>Vertragsart *</Label>
            <Select value={form.contractType} onValueChange={(v) => set("contractType", v)}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {CONTRACT_TYPES.map((c) => (
                  <SelectItem key={c.value} value={c.value}>{c.label}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-1">
            <Label>Wochenstunden *</Label>
            <Input type="number" min="1" max="50" step="0.5" value={form.weeklyHours} onChange={(e) => set("weeklyHours", e.target.value)} required />
          </div>
          <div className="space-y-1">
            <Label>Beschäftigt seit *</Label>
            <Input type="date" value={form.startDate} onChange={(e) => set("startDate", e.target.value)} required />
          </div>
          <div className="space-y-1">
            <Label>Beschäftigt bis</Label>
            <Input type="date" value={form.endDate} onChange={(e) => set("endDate", e.target.value)} />
          </div>
          <div className="space-y-1 col-span-2">
            <Label>Notizen</Label>
            <textarea
              value={form.notes}
              onChange={(e) => set("notes", e.target.value)}
              rows={3}
              className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            />
          </div>
        </CardContent>
      </Card>

      <div className="flex gap-3 justify-end">
        <Button type="button" variant="outline" onClick={() => router.back()}>
          Abbrechen
        </Button>
        <Button type="submit" disabled={loading}>
          {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
          {isEdit ? "Speichern" : "Anlegen"}
        </Button>
      </div>
    </form>
  )
}
