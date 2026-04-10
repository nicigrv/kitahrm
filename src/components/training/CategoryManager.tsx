"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from "@/components/ui/dialog"
import { Plus, Edit, Trash2, Loader2, Shield } from "lucide-react"

interface TrainingCategory {
  id: string
  name: string
  description: string | null
  validityMonths: number | null
  isFirstAid: boolean
  isActive: boolean
  sortOrder: number
  _count?: { completions: number }
}

interface CategoryManagerProps {
  initialCategories: TrainingCategory[]
}

const emptyForm = {
  name: "",
  description: "",
  validityMonths: "",
  isFirstAid: false,
  sortOrder: "0",
}

export function CategoryManager({ initialCategories }: CategoryManagerProps) {
  const [categories, setCategories] = useState(initialCategories)
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

  function openEdit(cat: TrainingCategory) {
    setEditingId(cat.id)
    setForm({
      name: cat.name,
      description: cat.description ?? "",
      validityMonths: cat.validityMonths ? String(cat.validityMonths) : "",
      isFirstAid: cat.isFirstAid,
      sortOrder: String(cat.sortOrder),
    })
    setError("")
    setOpen(true)
  }

  function setField(field: string, value: string | boolean) {
    setForm((p) => ({ ...p, [field]: value }))
  }

  async function handleSave() {
    if (!form.name.trim()) {
      setError("Name ist erforderlich")
      return
    }
    setLoading(true)
    setError("")

    const payload = {
      name: form.name,
      description: form.description || null,
      validityMonths: form.validityMonths ? parseInt(form.validityMonths) : null,
      isFirstAid: form.isFirstAid,
      sortOrder: parseInt(form.sortOrder) || 0,
    }

    try {
      const url = editingId
        ? `/api/training/categories/${editingId}`
        : "/api/training/categories"
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
        setCategories((prev) =>
          prev.map((c) => (c.id === editingId ? { ...c, ...saved } : c))
        )
      } else {
        setCategories((prev) => [...prev, saved])
      }
      setOpen(false)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Unbekannter Fehler")
    } finally {
      setLoading(false)
    }
  }

  async function handleDelete(cat: TrainingCategory) {
    if (!confirm(`Kategorie "${cat.name}" deaktivieren?`)) return

    const res = await fetch(`/api/training/categories/${cat.id}`, {
      method: "DELETE",
    })

    if (res.ok) {
      setCategories((prev) =>
        prev.map((c) => (c.id === cat.id ? { ...c, isActive: false } : c))
      )
    }
  }

  return (
    <div className="space-y-4">
      <Button onClick={openCreate}>
        <Plus className="h-4 w-4 mr-2" />
        Neue Kategorie
      </Button>

      <Card>
        <CardHeader>
          <CardTitle className="text-sm">
            Kategorien ({categories.length})
          </CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <table className="w-full text-sm">
            <thead className="bg-muted/50 border-b">
              <tr>
                <th className="text-left px-4 py-3 font-medium text-muted-foreground">Name</th>
                <th className="text-left px-4 py-3 font-medium text-muted-foreground">Gültigkeit</th>
                <th className="text-left px-4 py-3 font-medium text-muted-foreground">Typ</th>
                <th className="text-left px-4 py-3 font-medium text-muted-foreground">Einträge</th>
                <th className="text-left px-4 py-3 font-medium text-muted-foreground">Status</th>
                <th className="px-4 py-3" />
              </tr>
            </thead>
            <tbody>
              {categories.map((cat) => (
                <tr key={cat.id} className="border-b last:border-0 hover:bg-muted/20">
                  <td className="px-4 py-3">
                    <p className="font-medium">{cat.name}</p>
                    {cat.description && (
                      <p className="text-xs text-muted-foreground">{cat.description}</p>
                    )}
                  </td>
                  <td className="px-4 py-3 text-muted-foreground">
                    {cat.validityMonths ? `${cat.validityMonths} Monate` : "Kein Ablauf"}
                  </td>
                  <td className="px-4 py-3">
                    {cat.isFirstAid && (
                      <Badge variant="secondary" className="gap-1">
                        <Shield className="h-3 w-3" />
                        Erste Hilfe
                      </Badge>
                    )}
                  </td>
                  <td className="px-4 py-3 text-muted-foreground">
                    {cat._count?.completions ?? 0} Einträge
                  </td>
                  <td className="px-4 py-3">
                    <Badge variant={cat.isActive ? "success" : "secondary"}>
                      {cat.isActive ? "Aktiv" : "Inaktiv"}
                    </Badge>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-1">
                      <Button variant="ghost" size="sm" onClick={() => openEdit(cat)}>
                        <Edit className="h-3.5 w-3.5" />
                      </Button>
                      {cat.isActive && (
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleDelete(cat)}
                          className="text-destructive hover:text-destructive"
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </Button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </CardContent>
      </Card>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {editingId ? "Kategorie bearbeiten" : "Neue Kategorie"}
            </DialogTitle>
          </DialogHeader>
          <div className="space-y-3">
            {error && (
              <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
                {error}
              </div>
            )}
            <div className="space-y-1">
              <Label>Name *</Label>
              <Input
                value={form.name}
                onChange={(e) => setField("name", e.target.value)}
                placeholder="z.B. Erste Hilfe"
              />
            </div>
            <div className="space-y-1">
              <Label>Beschreibung</Label>
              <Input
                value={form.description}
                onChange={(e) => setField("description", e.target.value)}
                placeholder="Optional"
              />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1">
                <Label>Gültigkeit (Monate)</Label>
                <Input
                  type="number"
                  min="1"
                  value={form.validityMonths}
                  onChange={(e) => setField("validityMonths", e.target.value)}
                  placeholder="z.B. 24"
                />
              </div>
              <div className="space-y-1">
                <Label>Sortierung</Label>
                <Input
                  type="number"
                  value={form.sortOrder}
                  onChange={(e) => setField("sortOrder", e.target.value)}
                />
              </div>
            </div>
            <div className="flex items-center gap-2">
              <input
                type="checkbox"
                id="isFirstAid"
                checked={form.isFirstAid}
                onChange={(e) => setField("isFirstAid", e.target.checked)}
                className="h-4 w-4"
              />
              <Label htmlFor="isFirstAid" className="cursor-pointer">
                Zählt als Erste-Hilfe-Zertifikat
              </Label>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setOpen(false)}>
              Abbrechen
            </Button>
            <Button onClick={handleSave} disabled={loading}>
              {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
              Speichern
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
