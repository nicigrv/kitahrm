import { requireAdmin } from "@/lib/auth-helpers"
import { prisma } from "@/lib/prisma"
import { CategoryManager } from "@/components/training/CategoryManager"

export default async function CategoriesPage() {
  await requireAdmin()

  const categories = await prisma.trainingCategory.findMany({
    orderBy: [{ sortOrder: "asc" }, { name: "asc" }],
    include: { _count: { select: { completions: true } } },
  })

  return (
    <div className="space-y-6 max-w-3xl">
      <div>
        <h1 className="text-2xl font-bold">Schulungskategorien</h1>
        <p className="text-muted-foreground text-sm mt-1">
          Verwalten Sie die Schulungskategorien für alle Einrichtungen
        </p>
      </div>
      <CategoryManager initialCategories={categories} />
    </div>
  )
}
