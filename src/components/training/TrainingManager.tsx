"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { ExpiryBadge } from "@/components/employees/ExpiryBadge"
import { formatDate } from "@/lib/utils"
import { Plus, Trash2, Loader2, GraduationCap } from "lucide-react"

interface TrainingCategory {
  id: string
  name: string
  validityMonths: number | null
  isFirstAid: boolean
}

interface TrainingCompletion {
  id: string
  categoryId: string
  category: TrainingCategory
  completedDate: Date
  expiryDate: Date | null
  notes: string | null
}

interface TrainingManagerProps {
  employeeId: string
  initialCompletions: TrainingCompletion[]
  categories: TrainingCategory[]
}

export function TrainingManager({ employeeId, initialCompletions, categories }: TrainingManagerProps) {
  const [completions, setCompletions] = useState(initialCompletions)
  const [showForm, setShowForm] = useState(false)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState("")

  const [form, setForm] = useState({
    categoryId: "",
    completedDate: new Date().toISOString().split("T")[0],
    expiryDate: "",
    notes: "",
  })

  function setField(field: string, value: string) {
    setForm((prev) => ({ ...prev, [field]: value }))
  }

  // Auto-fill expiry when category changes
  function handleCategoryChange(categoryId: string) {
    const cat = categories.find((c) => c.id === categoryId)
    setField("categoryId", categoryId)
    if (cat?.validityMonths && form.completedDate) {
      const d = new Date(form.completedDate)
      d.setMonth(d.getMonth() + cat.validityMonths)
      setField("expiryDate", d.toISOString().split("T")[0])
    }
  }

  function handleDateChange(date: string) {
    setField("completedDate", date)
    const cat = categories.find((c) => c.id === form.categoryId)
    if (cat?.validityMonths && date) {
      const d = new Date(date)
      d.setMonth(d.getMonth() + cat.validityMonths)
      setField("expiryDate", d.toISOString().split("T")[0])
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!form.categoryId) return
    setError("")
    setLoading(true)

    try {
      const res = await fetch("/api/training/completions", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ ...form, employeeId }),
      })

      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || "Fehler")
      }

      const comp = await res.json()
      setCompletions((prev) => [comp, ...prev])
      setForm({ categoryId: "", completedDate: new Date().toISOString().split("T")[0], expiryDate: "", notes: "" })
      setShowForm(false)
    } catch (err) {
      setError(err instanceof Error ? err.message : "Unbekannter Fehler")
    } finally {
      setLoading(false)
    }
  }

  async function handleDelete(completionId: string) {
    if (!confirm("Schulungseintrag löschen?")) return
    const res = await fetch(`/api/training/completions/${completionId}`, { method: "DELETE" })
    if (res.ok) {
      setCompletions((prev) => prev.filter((c) => c.id !== completionId))
    }
  }

  return (
    <div className="space-y-4">
      {!showForm ? (
        <Button onClick={() => setShowForm(true)}>
          <Plus className="h-4 w-4 mr-2" />
          Schulung eintragen
        </Button>
      ) : (
        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Schulung eintragen</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-3">
              {error && (
                <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">{error}</div>
              )}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div className="space-y-1">
                  <Label>Schulungskategorie *</Label>
                  <Select value={form.categoryId} onValueChange={handleCategoryChange}>
                    <SelectTrigger>
                      <SelectValue placeholder="Kategorie wählen..." />
                    </SelectTrigger>
                    <SelectContent>
                      {categories.map((cat) => (
                        <SelectItem key={cat.id} value={cat.id}>
                          {cat.name}
                          {cat.isFirstAid && " (EH)"}
                          {cat.validityMonths && ` – ${cat.validityMonths} Monate`}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-1">
                  <Label>Absolviert am *</Label>
                  <Input
                    type="date"
                    value={form.completedDate}
                    onChange={(e) => handleDateChange(e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-1">
                  <Label>Gültig bis</Label>
                  <Input
                    type="date"
                    value={form.expiryDate}
                    onChange={(e) => setField("expiryDate", e.target.value)}
                  />
                </div>
                <div className="space-y-1">
                  <Label>Notizen</Label>
                  <Input
                    value={form.notes}
                    onChange={(e) => setField("notes", e.target.value)}
                    placeholder="Optional"
                  />
                </div>
              </div>
              <div className="flex gap-2">
                <Button type="submit" disabled={loading}>
                  {loading && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
                  Speichern
                </Button>
                <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
                  Abbrechen
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <CardTitle className="text-sm">Absolvierte Schulungen ({completions.length})</CardTitle>
        </CardHeader>
        <CardContent>
          {completions.length === 0 ? (
            <div className="text-center py-8 text-muted-foreground text-sm">
              <GraduationCap className="h-8 w-8 mx-auto mb-2 opacity-30" />
              Noch keine Schulungen eingetragen
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b">
                    <th className="text-left py-2 px-3 font-medium text-muted-foreground">Schulung</th>
                    <th className="text-left py-2 px-3 font-medium text-muted-foreground">Absolviert</th>
                    <th className="text-left py-2 px-3 font-medium text-muted-foreground">Gültig bis</th>
                    <th className="text-left py-2 px-3 font-medium text-muted-foreground">Status</th>
                    <th className="py-2 px-3" />
                  </tr>
                </thead>
                <tbody>
                  {completions.map((comp) => (
                    <tr key={comp.id} className="border-b last:border-0 hover:bg-muted/20">
                      <td className="py-2 px-3 font-medium">
                        {comp.category.name}
                        {comp.category.isFirstAid && (
                          <Badge variant="secondary" className="ml-2 text-xs">EH</Badge>
                        )}
                      </td>
                      <td className="py-2 px-3 text-muted-foreground">{formatDate(comp.completedDate)}</td>
                      <td className="py-2 px-3 text-muted-foreground">{formatDate(comp.expiryDate)}</td>
                      <td className="py-2 px-3">
                        <ExpiryBadge expiryDate={comp.expiryDate} showDate />
                      </td>
                      <td className="py-2 px-3">
                        <Button
                          variant="ghost"
                          size="sm"
                          onClick={() => handleDelete(comp.id)}
                          className="text-destructive hover:text-destructive"
                        >
                          <Trash2 className="h-3.5 w-3.5" />
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
