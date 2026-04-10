import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { deleteFile } from "@/lib/file-storage"
import { assertKitaAccess, canEditEmployees } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"

export async function DELETE(
  req: NextRequest,
  { params }: { params: { employeeId: string; docId: string } }
) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (!canEditEmployees(session.user.role as UserRole)) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const doc = await prisma.employeeDocument.findUnique({
    where: { id: params.docId },
    include: { employee: { select: { kitaId: true } } },
  })
  if (!doc) return NextResponse.json({ error: "Not found" }, { status: 404 })

  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, doc.employee.kitaId)
  } catch {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  await deleteFile(doc.storagePath)
  await prisma.employeeDocument.delete({ where: { id: params.docId } })

  return NextResponse.json({ success: true })
}
