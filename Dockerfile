# ─── Stage 1: Dependencies ────────────────────────────────────────────────────
FROM node:22-alpine AS deps

RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

COPY package.json pnpm-lock.yaml .npmrc ./
# Prisma postinstall (prisma generate) needs the schema file
COPY prisma ./prisma

RUN pnpm install --frozen-lockfile


# ─── Stage 2: Builder ─────────────────────────────────────────────────────────
FROM node:22-alpine AS builder

RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

# Full node_modules including pnpm virtual store (.pnpm/) with all binaries
COPY --from=deps /app/node_modules ./node_modules
COPY . .

# Ensure public/ exists (Next.js standalone copy requires it)
RUN mkdir -p public

# Generate Prisma JS client → node_modules/.prisma/client/ (linux-musl binary)
RUN pnpm exec prisma generate

# Run migrations at BUILD TIME against a throw-away SQLite file.
# The builder has the complete pnpm tree, so @prisma/engines is resolvable
# via node_modules/.pnpm/ even though it is not a direct project dependency.
ENV DATABASE_URL="file:/app/prisma/template.db"
RUN node_modules/.bin/prisma migrate deploy

# Build Next.js standalone
ENV NEXT_TELEMETRY_DISABLED=1
ENV NODE_ENV=production
ENV NEXTAUTH_SECRET="build-time-placeholder"
ENV NEXTAUTH_URL="http://localhost:3000"
RUN pnpm build


# ─── Stage 3: Runner ──────────────────────────────────────────────────────────
FROM node:22-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

RUN addgroup --system --gid 1001 nodejs && \
    adduser --system --uid 1001 nextjs

# Standalone Next.js server
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static
COPY --from=builder /app/public ./public

# Prisma schema + pre-migrated template.db (no CLI needed at runtime)
COPY --from=builder /app/prisma ./prisma

# Prisma JS query-engine client (linux-musl .so binary included)
COPY --from=builder /app/node_modules/.prisma ./node_modules/.prisma
COPY --from=builder /app/node_modules/@prisma/client ./node_modules/@prisma/client

# bcryptjs is pure-JS; needed by the entrypoint seed script
COPY --from=builder /app/node_modules/bcryptjs ./node_modules/bcryptjs

# Startup script
COPY docker-entrypoint.sh ./
RUN chmod +x docker-entrypoint.sh

# Persistent volume mount points with correct ownership
RUN mkdir -p /app/uploads /app/data && \
    chown -R nextjs:nodejs /app/uploads /app/data

RUN chown -R nextjs:nodejs /app

USER nextjs

EXPOSE 3000
ENV PORT=3000
ENV HOSTNAME="0.0.0.0"

ENTRYPOINT ["./docker-entrypoint.sh"]
