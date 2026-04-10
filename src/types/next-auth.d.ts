import { DefaultSession } from "next-auth"

export type UserRole = "ADMIN" | "KITA_MANAGER" | "KITA_STAFF"

declare module "next-auth" {
  interface Session {
    user: {
      id: string
      role: UserRole
      kitaId: string | null
    } & DefaultSession["user"]
  }

  interface User {
    role: UserRole
    kitaId: string | null
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    id: string
    role: UserRole
    kitaId: string | null
  }
}
