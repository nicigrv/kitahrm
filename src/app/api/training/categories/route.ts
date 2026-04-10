import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"

export async function GET() {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const categories = await prisma.trainingCategory.findMany({
    orderBy: [{ sortOrder: "asc" }, { name: "asc" }],
  })
  return NextResponse.json(categories)
}

export async function POST(req: NextRequest) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (session.user.role !== "ADMIN") return NextResponse.json({ error: "Forbidden" }, { status: 403 })

  const body = await req.json()

  const category = await prisma.trainingCategory.create({
    data: {
      name: body.name,
      description: body.description || null,
      validityMonths: body.validityMonths ? parseInt(body.validityMonths) : null,
      isFirstAid: body.isFirstAid ?? false,
      isActive: true,
      sortOrder: body.sortOrder ?? 0,
    },
  })
  return NextResponse.json(category, { status: 201 })
}
