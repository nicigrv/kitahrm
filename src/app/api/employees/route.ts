import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { canEditEmployees } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"

export async function GET(req: NextRequest) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const { role, kitaId } = session.user
  const kitaFilter = role === "ADMIN" ? {} : { kitaId: kitaId ?? undefined }

  const employees = await prisma.employee.findMany({
    where: kitaFilter,
    include: { kita: { select: { id: true, name: true } } },
    orderBy: [{ lastName: "asc" }, { firstName: "asc" }],
  })

  return NextResponse.json(employees)
}

export async function POST(req: NextRequest) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (!canEditEmployees(session.user.role as UserRole)) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const body = await req.json()

  // Enforce KITA scope for non-admin
  if (session.user.role !== "ADMIN" && body.kitaId !== session.user.kitaId) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const employee = await prisma.employee.create({
    data: {
      firstName: body.firstName,
      lastName: body.lastName,
      email: body.email || null,
      phone: body.phone || null,
      address: body.address || null,
      birthDate: body.birthDate ? new Date(body.birthDate) : null,
      position: body.position,
      startDate: new Date(body.startDate),
      endDate: body.endDate ? new Date(body.endDate) : null,
      contractType: body.contractType,
      weeklyHours: parseFloat(body.weeklyHours),
      isActive: body.isActive ?? true,
      notes: body.notes || null,
      kitaId: body.kitaId,
    },
  })

  return NextResponse.json(employee, { status: 201 })
}
