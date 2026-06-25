Bodhivaas Phase-1 Migrations

This folder contains non-destructive SQL migrations for Phase-1 of the Bodhivaas ERP migration.

Files:
- 20260614_001_bodhivaas_phase1.sql  : Main Phase-1 migration (attendance, exams, fees, assignments, faculty, placements, library, certificates).

Safety and usage:
1. BACKUP your production database before running any migration.
2. Run migrations in a staging environment first.

To execute a migration locally (from project root):

```bash
php tools/db_migrate.php supabase/migrations/20260614_001_bodhivaas_phase1.sql
```

The runner will prompt for confirmation and will execute statements in the SQL file. It is intentionally simple; for complex deployments use a proper migration tool.

Notes:
- The SQL uses `IF NOT EXISTS` and preserves existing data structures.
- Some cleanup or manual fixes (for inconsistent or plaintext admin passwords) may be required after migration.
- After migration, run integrity checks (script can be provided).
