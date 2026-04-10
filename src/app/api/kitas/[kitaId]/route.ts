import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"

export async function PATCH(req: NextRequest, { params }: { params: { kitaId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (session.user.role !== "ADMIN") return NextResponse.json({ error: "Forbidden" }, { status: 403 })

  const body = await req.json()

  const kita = await prisma.kita.update({
    where: { id: params.kitaId },
    data: {
      name: body.name,
      address: body.address || null,
      phone: body.phone || null,
      email: body.email || null,
      minFirstAid: parseInt(body.minFirstAid) || 2,
    },
  })
  return NextResponse.json(kita)
}
