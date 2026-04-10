import { prisma } from "@/lib/prisma"
import type { UserRole } from "@/types/next-auth"

export async function getEmployees(userRole: UserRole, userKitaId: string | null, kitaId?: string) {
  const where: Record<string, unknown> = {}

  if (userRole !== "ADMIN") {
    where.kitaId = userKitaId
  } else if (kitaId) {
    where.kitaId = kitaId
  }

  return prisma.employee.findMany({
    where,
    include: {
      kita: { select: { id: true, name: true, shortCode: true } },
      _count: { select: { documents: true, completions: true } },
    },
    orderBy: [{ lastName: "asc" }, { firstName: "asc" }],
  })
}

export async function getEmployee(id: string, userRole: UserRole, userKitaId: string | null) {
  const employee = await prisma.employee.findUnique({
    where: { id },
    include: {
      kita: true,
      documents: { orderBy: { uploadedAt: "desc" } },
      completions: {
        include: { category: true },
        orderBy: { completedDate: "desc" },
      },
    },
  })

  if (!employee) return null
  if (userRole !== "ADMIN" && employee.kitaId !== userKitaId) return null

  return employee
}
