import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import bcrypt from "bcryptjs"

export async function POST(req: NextRequest) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  if (session.user.role !== "ADMIN") return NextResponse.json({ error: "Forbidden" }, { status: 403 })

  const body = await req.json()

  if (!body.email || !body.password) {
    return NextResponse.json({ error: "E-Mail und Passwort erforderlich" }, { status: 400 })
  }

  const passwordHash = await bcrypt.hash(body.password, 12)

  const user = await prisma.user.create({
    data: {
      name: body.name || null,
      email: body.email,
      passwordHash,
      role: body.role ?? "KITA_STAFF",
      kitaId: body.kitaId || null,
    },
  })
  return NextResponse.json(user, { status: 201 })
}
