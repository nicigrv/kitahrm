import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import { canEditEmployees, assertKitaAccess } from "@/lib/auth-helpers"
import type { UserRole } from "@/types/next-auth"

async function getEmployeeWithAuth(employeeId: string, session: { user: { role: string; kitaId: string | null } }) {
  const employee = await prisma.employee.findUnique({ where: { id: employeeId } })
  if (!employee) return null
  try {
    assertKitaAccess(session.user.role as UserRole, session.user.kitaId, employee.kitaId)
  } catch {
    return null
  }
  return employee
}

export async function GET(req: NextRequest, { params }: { params: { employeeId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const employee = await getEmployeeWithAuth(params.employeeId, session)
  if (!employee) return NextResponse.json({ error: "Not found" }, { status: 404 })

  return NextResponse.json(employee)
}

export async function PATCH(req: NextRequest, { params }: { params: { employeeId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (!canEditEmployees(session.user.role as UserRole)) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const employee = await getEmployeeWithAuth(params.employeeId, session)
  if (!employee) return NextResponse.json({ error: "Not found" }, { status: 404 })

  const body = await req.json()

  const updated = await prisma.employee.update({
    where: { id: params.employeeId },
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
    },
  })

  return NextResponse.json(updated)
}

export async function DELETE(req: NextRequest, { params }: { params: { employeeId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (session.user.role !== "ADMIN") {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  await prisma.employee.delete({ where: { id: params.employeeId } })
  return NextResponse.json({ success: true })
}
