#!/bin/sh
set -e

echo "KITA-HRM wird gestartet..."

export DATABASE_URL="${DATABASE_URL:-file:/app/data/production.db}"

# First run: copy the pre-migrated template DB bundled in the image.
# Migrations already ran at build time - no prisma CLI needed here.
if [ ! -f "/app/data/production.db" ]; then
  echo "Neue Datenbank wird aus Template initialisiert..."
  cp /app/prisma/template.db /app/data/production.db

  echo "Startdaten werden eingefuegt..."
  node -e "
const { PrismaClient } = require('@prisma/client');
const bcrypt = require('bcryptjs');
const prisma = new PrismaClient();

async function seed() {
  await Promise.all([
    prisma.trainingCategory.upsert({ where: { name: 'Erste Hilfe' }, update: {}, create: { name: 'Erste Hilfe', description: 'Erste-Hilfe-Kurs (mind. 9 UE)', validityMonths: 24, isFirstAid: true, isActive: true, sortOrder: 1 } }),
    prisma.trainingCategory.upsert({ where: { name: 'Brandschutz' }, update: {}, create: { name: 'Brandschutz', description: 'Brandschutzunterweisung', validityMonths: 12, isFirstAid: false, isActive: true, sortOrder: 2 } }),
    prisma.trainingCategory.upsert({ where: { name: 'Datenschutz' }, update: {}, create: { name: 'Datenschutz', description: 'DSGVO Schulung', validityMonths: 24, isFirstAid: false, isActive: true, sortOrder: 3 } }),
    prisma.trainingCategory.upsert({ where: { name: 'Kinderschutz' }, update: {}, create: { name: 'Kinderschutz', description: 'Kinderschutzschulung §8a SGB VIII', validityMonths: 36, isFirstAid: false, isActive: true, sortOrder: 4 } }),
  ]);

  const kitaData = [
    { name: 'Kita Sonnenschein',   shortCode: 'SONN',  minFirstAid: 2 },
    { name: 'Kita Regenbogen',     shortCode: 'REGEN', minFirstAid: 2 },
    { name: 'Kita Schmetterlinge', shortCode: 'SCHM',  minFirstAid: 2 },
    { name: 'Kita Sternchen',      shortCode: 'STERN', minFirstAid: 2 },
    { name: 'Kita Loewenzahn',     shortCode: 'LOEWE', minFirstAid: 2 },
  ];
  const kitas = [];
  for (const data of kitaData) {
    const kita = await prisma.kita.upsert({ where: { shortCode: data.shortCode }, update: {}, create: data });
    kitas.push(kita);
  }

  const adminHash = await bcrypt.hash(process.env.ADMIN_PASSWORD || 'Admin123!', 12);
  await prisma.user.upsert({
    where: { email: 'admin@kita-traeger.de' },
    update: {},
    create: { name: 'Admin Traeger', email: 'admin@kita-traeger.de', passwordHash: adminHash, role: 'ADMIN' }
  });

  const managerHash = await bcrypt.hash(process.env.MANAGER_PASSWORD || 'Manager123!', 12);
  for (const kita of kitas) {
    const email = 'leitung.' + kita.shortCode.toLowerCase() + '@kita-traeger.de';
    await prisma.user.upsert({
      where: { email },
      update: {},
      create: { name: 'Leitung ' + kita.name, email, passwordHash: managerHash, role: 'KITA_MANAGER', kitaId: kita.id }
    });
  }

  await prisma.\$disconnect();
  console.log('Startdaten eingefuegt.');
}

seed().catch(e => { console.error(e); process.exit(1); });
" 2>&1
fi

echo "Bereit. Server startet auf Port ${PORT:-3000}..."
exec node server.js
