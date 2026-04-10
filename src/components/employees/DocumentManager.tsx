"use client"

import { useState, useRef } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { formatDate, formatFileSize } from "@/lib/utils"
import { Upload, Trash2, Download, FileText, Loader2 } from "lucide-react"

interface Document {
  id: string
  fileName: string
  label: string | null
  mimeType: string
  sizeBytes: number
  uploadedAt: Date
}

interface DocumentManagerProps {
  employeeId: string
  initialDocuments: Document[]
}

export function DocumentManager({ employeeId, initialDocuments }: DocumentManagerProps) {
  const [documents, setDocuments] = useState(initialDocuments)
  const [label, setLabel] = useState("")
  const [uploading, setUploading] = useState(false)
  const [error, setError] = useState("")
  const fileRef = useRef<HTMLInputElement>(null)

  async function handleUpload(e: React.FormEvent) {
    e.preventDefault()
    const file = fileRef.current?.files?.[0]
    if (!file) return

    setError("")
    setUploading(true)

    const formData = new FormData()
    formData.append("file", file)
    if (label) formData.append("label", label)

    try {
      const res = await fetch(`/api/employees/${employeeId}/documents`, {
        method: "POST",
        body: formData,
      })

      if (!res.ok) {
        const data = await res.json()
        throw new Error(data.error || "Upload fehlgeschlagen")
      }

      const doc = await res.json()
      setDocuments((prev) => [doc, ...prev])
      setLabel("")
      if (fileRef.current) fileRef.current.value = ""
    } catch (err) {
      setError(err instanceof Error ? err.message : "Fehler beim Upload")
    } finally {
      setUploading(false)
    }
  }

  async function handleDelete(docId: string) {
    if (!confirm("Dokument wirklich löschen?")) return

    const res = await fetch(`/api/employees/${employeeId}/documents/${docId}`, {
      method: "DELETE",
    })

    if (res.ok) {
      setDocuments((prev) => prev.filter((d) => d.id !== docId))
    }
  }

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader>
          <CardTitle className="text-sm">Dokument hochladen</CardTitle>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleUpload} className="space-y-3">
            {error && (
              <div className="rounded-md bg-destructive/10 p-3 text-sm text-destructive">{error}</div>
            )}
            <div className="space-y-1">
              <Label>Datei auswählen *</Label>
              <Input
                ref={fileRef}
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.docx,.doc"
                required
                className="cursor-pointer"
              />
              <p className="text-xs text-muted-foreground">PDF, JPG, PNG, DOCX – max. 10 MB</p>
            </div>
            <div className="space-y-1">
              <Label>Bezeichnung</Label>
              <Input
                value={label}
                onChange={(e) => setLabel(e.target.value)}
                placeholder="z.B. Arbeitsvertrag 2024"
              />
            </div>
            <Button type="submit" disabled={uploading}>
              {uploading ? <Loader2 className="h-4 w-4 mr-2 animate-spin" /> : <Upload className="h-4 w-4 mr-2" />}
              Hochladen
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="text-sm">Hochgeladene Dokumente ({documents.length})</CardTitle>
        </CardHeader>
        <CardContent>
          {documents.length === 0 ? (
            <div className="text-center py-8 text-muted-foreground text-sm">
              Noch keine Dokumente vorhanden
            </div>
          ) : (
            <div className="space-y-2">
              {documents.map((doc) => (
                <div
                  key={doc.id}
                  className="flex items-center gap-3 p-3 rounded-lg border hover:bg-muted/30"
                >
                  <FileText className="h-8 w-8 text-blue-500 shrink-0" />
                  <div className="flex-1 min-w-0">
                    <p className="font-medium text-sm truncate">{doc.fileName}</p>
                    <p className="text-xs text-muted-foreground">
                      {doc.label && <span className="mr-2">{doc.label} ·</span>}
                      {formatFileSize(doc.sizeBytes)} · {formatDate(doc.uploadedAt)}
                    </p>
                  </div>
                  <div className="flex items-center gap-1 shrink-0">
                    <Button variant="ghost" size="sm" asChild>
                      <a
                        href={`/api/employees/${employeeId}/documents/${doc.id}/download`}
                        target="_blank"
                      >
                        <Download className="h-4 w-4" />
                      </a>
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDelete(doc.id)}
                      className="text-destructive hover:text-destructive"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
