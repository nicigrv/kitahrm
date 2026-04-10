import { PrismaClient } from "@prisma/client"
import bcrypt from "bcryptjs"
import { addMonths, subMonths } from "date-fns"

const prisma = new PrismaClient()

async function main() {
  console.log("🌱 Seeding database...")

  // ─── Training Categories ─────────────────────────────────────────────────

  const categories = await Promise.all([
    prisma.trainingCategory.upsert({
      where: { name: "Erste Hilfe" },
      update: {},
      create: {
        name: "Erste Hilfe",
        description: "Erste-Hilfe-Kurs (mind. 9 UE)",
        validityMonths: 24,
        isFirstAid: true,
        isActive: true,
        sortOrder: 1,
      },
    }),
    prisma.trainingCategory.upsert({
      where: { name: "Brandschutz" },
      update: {},
      create: {
        name: "Brandschutz",
        description: "Brandschutzunterweisung",
        validityMonths: 12,
        isFirstAid: false,
        isActive: true,
        sortOrder: 2,
      },
    }),
    prisma.trainingCategory.upsert({
      where: { name: "Datenschutz" },
      update: {},
      create: {
        name: "Datenschutz",
        description: "DSGVO Schulung",
        validityMonths: 24,
        isFirstAid: false,
        isActive: true,
        sortOrder: 3,
      },
    }),
    prisma.trainingCategory.upsert({
      where: { name: "Kinderschutz" },
      update: {},
      create: {
        name: "Kinderschutz",
        description: "Kinderschutzschulung §8a SGB VIII",
        validityMonths: 36,
        isFirstAid: false,
        isActive: true,
        sortOrder: 4,
      },
    }),
    prisma.trainingCategory.upsert({
      where: { name: "Hygiene" },
      update: {},
      create: {
        name: "Hygiene",
        description: "Hygieneunterweisung",
        validityMonths: 12,
        isFirstAid: false,
        isActive: true,
        sortOrder: 5,
      },
    }),
  ])

  const [ehKat] = categories
  console.log(`✅ ${categories.length} Schulungskategorien angelegt`)

  // ─── KITAs ───────────────────────────────────────────────────────────────

  const kitaData = [
    { name: "Kita Sonnenschein", shortCode: "SONN", address: "Sonnenallee 12, 10967 Berlin", phone: "030 12345-10", email: "sonnenschein@kita-traeger.de", minFirstAid: 2 },
    { name: "Kita Regenbogen", shortCode: "REGEN", address: "Regenbogenweg 5, 10963 Berlin", phone: "030 12345-20", email: "regenbogen@kita-traeger.de", minFirstAid: 2 },
    { name: "Kita Schmetterlinge", shortCode: "SCHM", address: "Schmetterlingsstr. 8, 12345 Berlin", phone: "030 12345-30", email: "schmetterlinge@kita-traeger.de", minFirstAid: 2 },
    { name: "Kita Sternchen", shortCode: "STERN", address: "Sternstraße 22, 10115 Berlin", phone: "030 12345-40", email: "sternchen@kita-traeger.de", minFirstAid: 2 },
    { name: "Kita Löwenzahn", shortCode: "LOEWE", address: "Löwenzahnallee 3, 10247 Berlin", phone: "030 12345-50", email: "loewenzahn@kita-traeger.de", minFirstAid: 2 },
  ]

  const kitas = []
  for (const data of kitaData) {
    const kita = await prisma.kita.upsert({
      where: { shortCode: data.shortCode },
      update: {},
      create: data,
    })
    kitas.push(kita)
  }
  console.log(`✅ ${kitas.length} KITAs angelegt`)

  // ─── Users ───────────────────────────────────────────────────────────────

  const adminHash = await bcrypt.hash("Admin123!", 12)
  const managerHash = await bcrypt.hash("Manager123!", 12)

  const admin = await prisma.user.upsert({
    where: { email: "admin@kita-traeger.de" },
    update: {},
    create: {
      name: "Admin Träger",
      email: "admin@kita-traeger.de",
      passwordHash: adminHash,
      role: "ADMIN",
    },
  })

  // Create one manager per KITA
  const managers = []
  for (let i = 0; i < kitas.length; i++) {
    const kita = kitas[i]
    const email = `leitung.${kita.shortCode.toLowerCase()}@kita-traeger.de`
    const manager = await prisma.user.upsert({
      where: { email },
      update: {},
      create: {
        name: `Leitung ${kita.name}`,
        email,
        passwordHash: managerHash,
        role: "KITA_MANAGER",
        kitaId: kita.id,
      },
    })
    managers.push(manager)
  }
  console.log(`✅ 1 Admin + ${managers.length} KITA-Manager angelegt`)

  // ─── Employees ───────────────────────────────────────────────────────────

  const positions = [
    "Erzieherin",
    "Erzieher",
    "Kinderpflegerin",
    "Sozialpädagogische Fachkraft",
    "Gruppenleitung",
    "Leitung",
    "Praktikantin",
    "Köchin",
  ]

  const contracts = ["UNBEFRISTET", "BEFRISTET", "MINIJOB", "AUSBILDUNG", "PRAKTIKUM"]
  const hours = [39.0, 35.0, 30.0, 20.0, 15.0, 10.0]

  const firstNames = [
    "Anna", "Maria", "Sarah", "Julia", "Laura", "Lisa", "Emma", "Sophie",
    "Thomas", "Michael", "Stefan", "Klaus", "Peter", "Hans", "Felix", "Max"
  ]
  const lastNames = [
    "Müller", "Schmidt", "Schneider", "Fischer", "Weber", "Meyer", "Wagner",
    "Becker", "Schulz", "Hoffmann", "Koch", "Richter", "Klein", "Wolf", "Schröder"
  ]

  let employeeCount = 0
  for (const kita of kitas) {
    const count = 5 + Math.floor(Math.random() * 3) // 5-7 per KITA
    for (let i = 0; i < count; i++) {
      const firstName = firstNames[Math.floor(Math.random() * firstNames.length)]
      const lastName = lastNames[Math.floor(Math.random() * lastNames.length)]
      const position = i === 0 ? "Leitung" : positions[Math.floor(Math.random() * positions.length)]
      const contractType = i < 4 ? "UNBEFRISTET" : contracts[Math.floor(Math.random() * contracts.length)]
      const weeklyHours = i === 0 ? 39.0 : hours[Math.floor(Math.random() * hours.length)]
      const startDate = subMonths(new Date(), 12 + Math.floor(Math.random() * 48))

      const emp = await prisma.employee.create({
        data: {
          firstName,
          lastName,
          email: `${firstName.toLowerCase()}.${lastName.toLowerCase()}.${employeeCount}@kita-traeger.de`,
          phone: `030 ${Math.floor(10000000 + Math.random() * 90000000)}`,
          address: `Musterstraße ${i + 1}, 10115 Berlin`,
          birthDate: new Date(1975 + Math.floor(Math.random() * 25), Math.floor(Math.random() * 12), 1 + Math.floor(Math.random() * 28)),
          position,
          startDate,
          contractType,
          weeklyHours,
          isActive: true,
          kitaId: kita.id,
        },
      })
      employeeCount++

      // Add some training completions
      // First-aid: alternately valid, expired, missing
      if (i % 3 !== 2) {
        const completedDate = i % 3 === 0
          ? subMonths(new Date(), 6)   // valid (6 months ago, valid for 24 months)
          : subMonths(new Date(), 26)  // expired (26 months ago)
        await prisma.trainingCompletion.create({
          data: {
            employeeId: emp.id,
            categoryId: ehKat.id,
            completedDate,
            expiryDate: addMonths(completedDate, 24),
          },
        })
      }

      // Brandschutz for most
      if (i % 4 !== 3) {
        const completedDate = subMonths(new Date(), 3)
        await prisma.trainingCompletion.create({
          data: {
            employeeId: emp.id,
            categoryId: categories[1].id,
            completedDate,
            expiryDate: addMonths(completedDate, 12),
          },
        })
      }
    }
  }
  console.log(`✅ ${employeeCount} Mitarbeiter angelegt`)
  console.log("🎉 Seed abgeschlossen!")
  console.log("")
  console.log("Login-Daten:")
  console.log("  Admin:        admin@kita-traeger.de / Admin123!")
  console.log("  Kita-Leitung: leitung.sonn@kita-traeger.de / Manager123!")
}

main()
  .catch((e) => {
    console.error(e)
    process.exit(1)
  })
  .finally(async () => {
    await prisma.$disconnect()
  })
