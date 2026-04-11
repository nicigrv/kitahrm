# KITA-HRM

Personalverwaltungssystem für Kita-Träger – entwickelt mit **Laravel 11**, Tailwind CSS, Alpine.js und MySQL.

---

## Features

| Bereich | Funktionen |
|---|---|
| **Dashboard** | Personalauslastung (Stunden), Erste-Hilfe-Abdeckung, Ablaufwarnungen |
| **Kita-Verwaltung** | Einrichtungen anlegen/bearbeiten, Mindestbesetzung & Schulungsanforderungen pro Kita |
| **Mitarbeiterverwaltung** | Stammdaten, Vertragsarten, Wochenstunden, Dokumente (PDF/DOCX/Bild), Schulungsnachweise |
| **Schulungsmatrix** | Übersicht Schulungskategorien × Mitarbeiter, Ablaufdaten, Ampelstatus |
| **Gemeinsamer Kalender** | Schließtage, verkürzte Öffnungszeiten, Fortbildungen, Infos – alle Kitas farbig |
| **Schließtage-Kalender** | Kita-spezifische Monatsansicht mit Verwaltung der Schließtage |
| **Benutzerverwaltung** | Admin, Kita-Leitung, Kita-Personal – rollenbasierter Zugriff |
| **System-Update** | Eingebautes Update-Tool lädt neue Version von GitHub und migriert die DB automatisch |
| **Web-Installer** | `setup.php` richtet DB-Tabellen und Startdaten ein |

---

## Rollen

| Rolle | Zugriff |
|---|---|
| `ADMIN` | Vollzugriff auf alle Einrichtungen |
| `KITA_MANAGER` | Lesen/Schreiben nur für die eigene Einrichtung |
| `KITA_STAFF` | Lesezugriff auf eigene Einrichtung |

---

## Installation

1. Dateien auf den Webserver hochladen
2. `https://domain.de/setup.php` aufrufen
3. Datenbankzugangsdaten und Admin-Passwort eingeben
4. `setup.php` danach löschen: `https://domain.de/setup.php?delete=1`

---

## System-Update

1. `GITHUB_TOKEN=...` in `.env` eintragen (read-only Token genügt)
2. `https://domain.de/update.php` aufrufen
3. Admin-Passwort eingeben und Update starten

`.env`, `storage/`, `uploads/` werden **nicht** überschrieben.

---

## Changelog

### 2026-04-11 – v3

- **Gemeinsamer Kalender** (`/calendar`): Schließtage, verkürzte Öffnungszeiten (mit Uhrzeiten), Fortbildungen und Infos aller Kitas in einer Monatsansicht; jede Kita erhält eine eigene Farbe; mehrtägige Ereignisse möglich
- **Bugfix: update.php 500-Fehler** – PHP-Syntaxfehler in `match`-Ausdruck behoben
- **Bugfix: Mitarbeiter anlegen 500-Fehler** – Typ-Vergleich `kita_id` (int vs. string) in `assertKitaAccess` korrigiert; `start_date->diff()` jetzt null-sicher
- **UI: „Mitarbeiter aktiv"** – Schaltfläche durch modernen Toggle-Switch ersetzt
- **Sidebar** – Link „System-Update" öffnet `update.php` mit korrektem URL; „Kalender"-Eintrag für alle Rollen hinzugefügt

### 2026-04-11 – v2

- **System-Update** (`/update.php`): GitHub-ZIP-Download, Datei-Sync, automatische Migrationen, Cache-Leerung
- **Stunden-basierter Bedarf**: `target_weekly_hours` pro Kita; Fortschrittsbalken in Übersicht und Detailseite
- **Schließtage-Kalender** (pro Kita): Monatsraster mit Eintragen/Löschen
- **Mitarbeiter-Detailseite**: Hero-Header, Tab-System (Stammdaten / Dokumente / Schulungen), Drag-and-Drop-Upload
- **Kita-Detailseite**: Stats-Zeile (Erste-Hilfe + Wochenstunden), Kalender-Link

### 2026-04-10 – v1

- Kita-Verwaltung: Anlegen, Bearbeiten, Löschen mit Mindestbesetzung und Schulungsanforderungen
- Benutzerverwaltung: Admin erstellt/verwaltet KITA_MANAGER und KITA_STAFF Accounts
- Mitarbeiter-Liste: Statistikkarten, sortierbare Spalten, Live-Filterung, farbige Vertragstyp-Badges
- Kita-Übersicht: Card-Grid mit Stunden-Fortschrittsbalken und Ersthelfer-Status
- Hosttech-Kompatibilität: automatische Subverzeichnis-Erkennung in `public/index.php`
