import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"

export async function PATCH(req: NextRequest, { params }: { params: { categoryId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (session.user.role !== "ADMIN") return NextResponse.json({ error: "Forbidden" }, { status: 403 })

  const body = await req.json()

  const category = await prisma.trainingCategory.update({
    where: { id: params.categoryId },
    data: {
      name: body.name,
      description: body.description || null,
      validityMonths: body.validityMonths ? parseInt(body.validityMonths) : null,
      isFirstAid: body.isFirstAid ?? false,
      isActive: body.isActive ?? true,
      sortOrder: body.sortOrder ?? 0,
    },
  })
  return NextResponse.json(category)
}

export async function DELETE(req: NextRequest, { params }: { params: { categoryId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (session.user.role !== "ADMIN") return NextResponse.json({ error: "Forbidden" }, { status: 403 })

  await prisma.trainingCategory.update({
    where: { id: params.categoryId },
    data: { isActive: false },
  })
  return NextResponse.json({ success: true })
}
