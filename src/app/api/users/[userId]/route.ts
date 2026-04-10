import { NextRequest, NextResponse } from "next/server"
import { auth } from "@/auth"
import { prisma } from "@/lib/prisma"
import bcrypt from "bcryptjs"

export async function PATCH(req: NextRequest, { params }: { params: { userId: string } }) {
  const session = await auth()
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  // Users can update their own profile; admins can update anyone
  const isSelf = session.user.id === params.userId
  const isAdmin = session.user.role === "ADMIN"

  if (!isSelf && !isAdmin) {
    return NextResponse.json({ error: "Forbidden" }, { status: 403 })
  }

  const body = await req.json()
  const data: Record<string, unknown> = {}

  if (body.name !== undefined) data.name = body.name
  if (body.password) data.passwordHash = await bcrypt.hash(body.password, 12)

  // Only admins can change role/kitaId
  if (isAdmin) {
    if (body.role) data.role = body.role
    if (body.kitaId !== undefined) data.kitaId = body.kitaId || null
    if (body.email) data.email = body.email
  }

  const user = await prisma.user.update({
    where: { id: params.userId },
    data,
  })
  return NextResponse.json(user)
}
