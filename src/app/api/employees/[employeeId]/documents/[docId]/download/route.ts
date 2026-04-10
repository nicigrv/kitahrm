import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { readFileFromStorage } from "@/lib/file-storage"
import { assertKitaAccess } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"

export async function GET(
  req: NextRequest,
  { params }: { params: { employeeId: string; docId: string } }
) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const doc = await prisma.employeeDocument.findUnique({
    where: { id: params.docId },
    include: { employee: { select: { kitaId: true } } },
  })
  if (!doc || doc.employeeId !== params.employeeId) {
    return NextResponse.json({ error: "Not found" }, { status: 404 })
  }

  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, doc.employee.kitaId)
  } catch {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  try {
    const buffer = await readFileFromStorage(doc.storagePath)
    return new NextResponse(new Uint8Array(buffer), {
      headers: {
        "Content-Type": doc.mimeType,
        "Content-Disposition": `inline; filename="${encodeURIComponent(doc.fileName)}"`,
        "Content-Length": String(buffer.length),
      },
    })
  } catch {
    return NextResponse.json({ error: "Datei nicht gefunden" }, { status: 404 })
  }
}
