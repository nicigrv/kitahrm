import { writeFile, mkdir, unlink, readFile } from "fs/promises"
import path from "path"
import { randomUUID } from "crypto"

const UPLOADS_DIR = path.join(process.cwd(), "uploads")
const MAX_FILE_SIZE = 10 * 1024 * 1024 // 10 MB
const ALLOWED_TYPES = [
  "application/pdf",
  "image/jpeg",
  "image/png",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/msword",
]

export async function saveFile(
  employeeId: string,
  file: File
): Promise<{ storagePath: string; fileName: string; mimeType: string; sizeBytes: number }> {
  if (file.size > MAX_FILE_SIZE) {
    throw new Error("Datei ist zu groß (max. 10 MB)")
  }
  if (!ALLOWED_TYPES.includes(file.type)) {
    throw new Error("Dateityp nicht erlaubt (PDF, JPG, PNG, DOCX)")
  }

  const ext = path.extname(file.name) || ".bin"
  const uuid = randomUUID()
  const subDir = path.join(UPLOADS_DIR, employeeId)
  await mkdir(subDir, { recursive: true })

  const fileName = `${uuid}${ext}`
  const fullPath = path.join(subDir, fileName)
  const buffer = Buffer.from(await file.arrayBuffer())
  await writeFile(fullPath, buffer)

  return {
    storagePath: path.join(employeeId, fileName),
    fileName: file.name,
    mimeType: file.type,
    sizeBytes: file.size,
  }
}

export async function readFileFromStorage(storagePath: string): Promise<Buffer> {
  const fullPath = path.join(UPLOADS_DIR, storagePath)
  return readFile(fullPath)
}

export async function deleteFile(storagePath: string): Promise<void> {
  const fullPath = path.join(UPLOADS_DIR, storagePath)
  await unlink(fullPath).catch(() => {})
}
