"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Loader2 } from "lucide-react"

interface UserSettingsFormProps {
  userId: string
  currentName: string
}

export function UserSettingsForm({ userId, currentName }: UserSettingsFormProps) {
  const [name, setName] = useState(currentName)
  const [password, setPassword] = useState("")
  const [confirm, setConfirm] = useState("")
  const [loading, setLoading] = useState(false)
  const [msg, setMsg] = useState("")
  const [error, setError] = useState("")

  async function handleSave(e: React.FormEvent) {
    e.preventDefault()
    if (password && password !== confirm) {
      setError("Passwörter stimmen nicht überein")
      return
    }
    setError("")
    setMsg("")
    setLoading(true)

    const res = await fetch(`/api/users/${userId}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name, password: password || undefined }),
    })

    setLoading(false)
    if (res.ok) {
      setMsg("Profil aktualisiert")
      setPassword("")
      setConfirm("")
    } else {
      setError("Fehler beim Speichern")
    }
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-sm">Profil bearbeiten</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSave} className="space-y-3">
          {error && <div className="rounded-md bg-destructive/10 p-2 text-xs text-destructive">{error}</div>}
          {msg && <div className="rounded-md bg-green-50 p-2 text-xs text-green-700">{msg}</div>}
          <div className="space-y-1">
            <Label>Name</Label>
            <Input value={name} onChange={(e) => setName(e.target.value)} />
          </div>
          <div className="space-y-1">
            <Label>Neues Passwort</Label>
            <Input type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="Leer lassen = unverändert" />
          </div>
          {password && (
            <div className="space-y-1">
              <Label>Passwort bestätigen</Label>
              <Input type="password" value={confirm} onChange={(e) => setConfirm(e.target.value)} />
            </div>
          )}
          <Button type="submit" size="sm" disabled={loading}>
            {loading && <Loader2 className="h-3.5 w-3.5 mr-1 animate-spin" />}
            Speichern
          </Button>
        </form>
      </CardContent>
    </Card>
  )
}
