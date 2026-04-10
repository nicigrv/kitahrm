"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Loader2, Save } from "lucide-react"

interface KitaSettingsFormProps {
  kita: {
    id: string
    name: string
    address: string | null
    phone: string | null
    email: string | null
    minFirstAid: number
  }
}

export function KitaSettingsForm({ kita }: KitaSettingsFormProps) {
  const [form, setForm] = useState({
    name: kita.name,
    address: kita.address ?? "",
    phone: kita.phone ?? "",
    email: kita.email ?? "",
    minFirstAid: String(kita.minFirstAid),
  })
  const [loading, setLoading] = useState(false)
  const [saved, setSaved] = useState(false)
  const [error, setError] = useState("")

  function set(field: string, value: string) {
    setForm((p) => ({ ...p, [field]: value }))
    setSaved(false)
  }

  async function handleSave() {
    setLoading(true)
    setError("")
    try {
      const res = await fetch(`/api/kitas/${kita.id}`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...form,
          minFirstAid: parseInt(form.minFirstAid),
        }),
      })
      if (!res.ok) throw new Error("Fehler beim Speichern")
      setSaved(true)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Fehler")
    } finally {
      setLoading(false)
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-sm">Einstellungen</CardTitle>
      </CardHeader>
      <CardContent className="space-y-3">
        {error && (
          <div className="rounded-md bg-destructive/10 p-2 text-xs text-destructive">{error}</div>
        )}
        {saved && (
          <div className="rounded-md bg-green-50 p-2 text-xs text-green-700">Gespeichert!</div>
        )}
        <div className="space-y-1">
          <Label className="text-xs">Name</Label>
          <Input value={form.name} onChange={(e) => set("name", e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label className="text-xs">Adresse</Label>
          <Input value={form.address} onChange={(e) => set("address", e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label className="text-xs">Telefon</Label>
          <Input value={form.phone} onChange={(e) => set("phone", e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label className="text-xs">E-Mail</Label>
          <Input type="email" value={form.email} onChange={(e) => set("email", e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label className="text-xs">Mind. EH-Personen</Label>
          <Input
            type="number"
            min="0"
            value={form.minFirstAid}
            onChange={(e) => set("minFirstAid", e.target.value)}
          />
        </div>
        <Button size="sm" onClick={handleSave} disabled={loading} className="w-full">
          {loading ? <Loader2 className="h-3.5 w-3.5 mr-1 animate-spin" /> : <Save className="h-3.5 w-3.5 mr-1" />}
          Speichern
        </Button>
      </CardContent>
    </Card>
  )
}
