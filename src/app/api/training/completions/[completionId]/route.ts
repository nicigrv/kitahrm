import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { assertKitaAccess, canEditEmployees } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"

export async function DELETE(req: NextRequest, { params }: { params: { completionId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (!canEditEmployees(session.user.role as UserRole)) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const completion = await prisma.trainingCompletion.findUnique({
    where: { id: params.completionId },
    include: { employee: { select: { kitaId: true } } },
  })
  if (!completion) return NextResponse.json({ error: "Not found" }, { status: 404 })

  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, completion.employee.kitaId)
  } catch {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  await prisma.trainingCompletion.delete({ where: { id: params.completionId } })
  return NextResponse.json({ success: true })
}
