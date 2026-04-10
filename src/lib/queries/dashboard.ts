import { prisma } from "@/lib/prisma"
import type { UserRole } from "@/types/next-auth"
import { addDays } from "date-fns"

export async function getDashboardData(userRole: UserRole, userKitaId: string | null) {
  const kitaWhere = userRole === "ADMIN" ? {} : { id: userKitaId ?? undefined }

  const kitas = await prisma.kita.findMany({
    where: kitaWhere,
    include: {
      employees: {
        where: { isActive: true },
        include: {
          completions: {
            include: { category: true },
            orderBy: { completedDate: "desc" },
          },
        },
      },
    },
    orderBy: { name: "asc" },
  })

  const now = new Date()
  const warningDate = addDays(now, 60)

  const kitaStats = kitas.map((kita) => {
    const activeEmployees = kita.employees
    const totalHours = activeEmployees.reduce((sum, e) => sum + e.weeklyHours, 0)

    // First-aid: find employees with a valid (non-expired) EH completion
    const firstAidEmployees = activeEmployees.filter((emp) => {
      const ehCompletions = emp.completions.filter(
        (c) => c.category.isFirstAid && c.expiryDate && c.expiryDate > now
      )
      return ehCompletions.length > 0
    })

    // Expiry alerts for this KITA
    const expiryAlerts: ExpiryAlert[] = []
    for (const emp of activeEmployees) {
      for (const comp of emp.completions) {
        if (!comp.expiryDate) continue
        if (comp.expiryDate <= warningDate) {
          expiryAlerts.push({
            employeeId: emp.id,
            employeeName: `${emp.firstName} ${emp.lastName}`,
            categoryName: comp.category.name,
            expiryDate: comp.expiryDate,
            isExpired: comp.expiryDate < now,
            kitaId: kita.id,
            kitaName: kita.name,
          })
        }
      }
    }

    return {
      kitaId: kita.id,
      kitaName: kita.name,
      shortCode: kita.shortCode,
      activeEmployeeCount: activeEmployees.length,
      totalWeeklyHours: totalHours,
      firstAidCount: firstAidEmployees.length,
      minFirstAid: kita.minFirstAid,
      firstAidOk: firstAidEmployees.length >= kita.minFirstAid,
      firstAidEmployees: firstAidEmployees.map((e) => ({
        id: e.id,
        name: `${e.firstName} ${e.lastName}`,
        expiryDate: e.completions
          .filter((c) => c.category.isFirstAid && c.expiryDate && c.expiryDate > now)
          .sort((a, b) => (a.expiryDate?.getTime() ?? 0) - (b.expiryDate?.getTime() ?? 0))[0]?.expiryDate ?? null,
      })),
      expiryAlerts: expiryAlerts.sort((a, b) => a.expiryDate.getTime() - b.expiryDate.getTime()),
    }
  })

  const allExpiryAlerts = kitaStats
    .flatMap((k) => k.expiryAlerts)
    .sort((a, b) => a.expiryDate.getTime() - b.expiryDate.getTime())

  return { kitaStats, allExpiryAlerts }
}

export interface ExpiryAlert {
  employeeId: string
  employeeName: string
  categoryName: string
  expiryDate: Date
  isExpired: boolean
  kitaId: string
  kitaName: string
}
