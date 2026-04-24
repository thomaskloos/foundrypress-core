# FoundryPress Release Script

## Files
- `build-release.ps1` -> Builds clean Core and Pro zip files
- `RELEASE_SCRIPT_NOTES.md` -> Quick setup notes

## Expected project structure

```text
FoundryPress/
  core/
  pro/
  releases/
  build/
```

## How to use

Open PowerShell in your repo root and run:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
./build-release.ps1
```

It will ask for a version number and create:

- `releases/foundrypress-core-vX.Y.Z.zip`
- `releases/foundrypress-pro-vX.Y.Z.zip`

## Notes

The script:
- copies from `core/` and `pro/`
- excludes common dev folders
- excludes `.git`, `.vscode`, logs, and zip files
- removes `pro/` from the core package if it somehow exists there
