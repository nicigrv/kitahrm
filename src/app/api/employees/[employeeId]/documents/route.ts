import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { saveFile } from "@/lib/file-storage"
import { assertKitaAccess, canEditEmployees } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"

export async function GET(req: NextRequest, { params }: { params: { employeeId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const employee = await prisma.employee.findUnique({ where: { id: params.employeeId } })
  if (!employee) return NextResponse.json({ error: "Not found" }, { status: 404 })

  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, employee.kitaId)
  } catch {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const docs = await prisma.employeeDocument.findMany({
    where: { employeeId: params.employeeId },
    orderBy: { uploadedAt: "desc" },
  })
  return NextResponse.json(docs)
}

export async function POST(req: NextRequest, { params }: { params: { employeeId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (!canEditEmployees(session.user.role as UserRole)) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const employee = await prisma.employee.findUnique({ where: { id: params.employeeId } })
  if (!employee) return NextResponse.json({ error: "Not found" }, { status: 404 })

  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, employee.kitaId)
  } catch {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const formData = await req.formData()
  const file = formData.get("file") as File | null
  const label = formData.get("label") as string | null

  if (!file) return NextResponse.json({ error: "Keine Datei" }, { status: 400 })

  try {
    const stored = await saveFile(params.employeeId, file)
    const doc = await prisma.employeeDocument.create({
      data: {
        employeeId: params.employeeId,
        fileName: stored.fileName,
        storagePath: stored.storagePath,
        mimeType: stored.mimeType,
        sizeBytes: stored.sizeBytes,
        label: label || null,
      },
    })
    return NextResponse.json(doc, { status: 201 })
  } catch (err) {
    return NextResponse.json(
      { error: err instanceof Error ? err.message : "Upload fehlgeschlagen" },
      { status: 400 }
    )
  }
}
