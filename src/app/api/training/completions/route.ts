import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { assertKitaAccess, canEditEmployees } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"
import { addMonths } from "date-fns"

export async function POST(req: NextRequest) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (!canEditEmployees(session.user.role as UserRole)) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const body = await req.json()

  const employee = await prisma.employee.findUnique({ where: { id: body.employeeId } })
  if (!employee) return NextResponse.json({ error: "Mitarbeiter nicht gefunden" }, { status: 404 })

  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, employee.kitaId)
  } catch {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const category = await prisma.trainingCategory.findUnique({ where: { id: body.categoryId } })
  if (!category) return NextResponse.json({ error: "Kategorie nicht gefunden" }, { status: 404 })

  // Auto-calculate expiry from category validity if not provided
  const completedDate = new Date(body.completedDate)
  let expiryDate = body.expiryDate ? new Date(body.expiryDate) : null
  if (!expiryDate && category.validityMonths) {
    expiryDate = addMonths(completedDate, category.validityMonths)
  }

  const completion = await prisma.trainingCompletion.create({
    data: {
      employeeId: body.employeeId,
      categoryId: body.categoryId,
      completedDate,
      expiryDate,
      notes: body.notes || null,
    },
    include: { category: true },
  })

  return NextResponse.json(completion, { status: 201 })
}
