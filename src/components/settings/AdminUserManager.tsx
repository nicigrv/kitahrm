"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog"
import { ROLE_LABELS } from "@/lib/utils"
import { Plus, Edit, Loader2 } from "lucide-react"

interface Kita { id: string; name: string }
interface User {
  id: string; name: string | null; email: string; role: string
  kita: { id: string; name: string } | null
}

interface AdminUserManagerProps {
  users: User[]
  kitas: Kita[]
}

const emptyForm = { name: "", email: "", password: "", role: "KITA_STAFF", kitaId: "" }

export function AdminUserManager({ users: initialUsers, kitas }: AdminUserManagerProps) {
  const [users, setUsers] = useState(initialUsers)
  const [open, setOpen] = useState(false)
  const [editingId, setEditingId] = useState<string | null>(null)
  const [form, setForm] = useState(emptyForm)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState("")

  function openCreate() {
    setEditingId(null)
    setForm(emptyForm)
    setError("")
    setOpen(true)
  }

  function openEdit(user: User) {
    setEditingId(user.id)
    setForm({ name: user.name ?? "", email: user.email, password: "", role: user.role, kitaId: user.kita?.id ?? "" })
    setError("")
    setOpen(true)
  }

  function setField(field: string, value: string) {
    setForm((p) => ({ ...p, [field]: value }))
  }

  async function handleSave() {
    setError("")
    setLoading(true)
    try {
      const payload = {
        name: form.name,
        email: form.email,
        role: form.role,
        kitaId: form.kitaId || null,
        ...(form.password ? { password: form.password } : {}),
      }

      const url = editingId ? `/api/users/${editingId}` : "/api/users"
      const method = editingId ? "PATCH" : "POST"

      const res = await fetch(url, {
        method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })

      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || "Fehler")
      }

      const saved = await res.json()
      if (editingId) {
        setUsers((prev) => prev.map((u) => (u.id === editingId ? { ...u, ...saved, kita: kitas.find((k) => k.id === saved.kitaId) ?? null } : u)))
      } else {
        setUsers((prev) => [...prev, { ...saved, kita: kitas.find((k) => k.id === saved.kitaId) ?? null }])
      }
      setOpen(false)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Fehler")
    } finally {
      setLoading(false)
    }
  }

  return (
    <Card>
      <CardHeader>
        <div className="flex items-center justify-between">
          <CardTitle className="text-sm">Benutzerverwaltung ({users.length})</CardTitle>
          <Button size="sm" onClick={openCreate}>
            <Plus className="h-4 w-4 mr-1" />
            Neuer Benutzer
          </Button>
        </div>
      </CardHeader>
      <CardContent className="p-0">
        <table className="w-full text-sm">
          <thead className="bg-muted/50 border-b">
            <tr>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Name</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">E-Mail</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Rolle</th>
              <th className="text-left px-4 py-3 font-medium text-muted-foreground">Einrichtung</th>
              <th className="px-4 py-3" />
            </tr>
          </thead>
          <tbody>
            {users.map((user) => (
              <tr key={user.id} className="border-b last:border-0 hover:bg-muted/20">
                <td className="px-4 py-3 font-medium">{user.name}</td>
                <td className="px-4 py-3 text-muted-foreground">{user.email}</td>
                <td className="px-4 py-3">
                  <Badge variant="outline">{ROLE_LABELS[user.role] ?? user.role}</Badge>
                </td>
                <td className="px-4 py-3 text-muted-foreground">{user.kita?.name ?? "—"}</td>
                <td className="px-4 py-3">
                  <Button variant="ghost" size="sm" onClick={() => openEdit(user)}>
                    <Edit className="h-3.5 w-3.5" />
                  </Button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </CardContent>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editingId ? "Benutzer bearbeiten" : "Neuer Benutzer"}</DialogTitle>
          </DialogHeader>
          <div className="space-y-3">
            {error && <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">{error}</div>}
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1">
                <Label>Name</Label>
                <Input value={form.name} onChange={(e) => setField("name", e.target.value)} />
              </div>
              <div className="space-y-1">
                <Label>E-Mail *</Label>
                <Input type="email" value={form.email} onChange={(e) => setField("email", e.target.value)} required />
              </div>
            </div>
            <div className="space-y-1">
              <Label>{editingId ? "Neues Passwort" : "Passwort *"}</Label>
              <Input type="password" value={form.password} onChange={(e) => setField("password", e.target.value)} placeholder={editingId ? "Leer = unverändert" : ""} />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1">
                <Label>Rolle</Label>
                <Select value={form.role} onValueChange={(v) => setField("role", v)}>
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="ADMIN">Träger-Admin</SelectItem>
                    <SelectItem value="KITA_MANAGER">KITA-Leitung</SelectItem>
                    <SelectItem value="KITA_STAFF">KITA-Mitarbeiter</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1">
                <Label>Einrichtung</Label>
                <Select value={form.kitaId} onValueChange={(v) => setField("kitaId", v)}>
                  <SelectTrigger><SelectValue placeholder="Keine" /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="">Keine (Admin)</SelectItem>
                    {kitas.map((k) => (
                      <SelectItem key={k.id} value={k.id}>{k.name}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setOpen(false)}>Abbrechen</Button>
            <Button onClick={handleSave} disabled={loading}>
              {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
              Speichern
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </Card>
  )
}
