param(
    [string]$Version = "",
    [string]$ProjectRoot = (Get-Location).Path
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Remove-IfExists {
    param([string]$PathToRemove)
    if (Test-Path $PathToRemove) {
        Remove-Item -Path $PathToRemove -Recurse -Force
    }
}

function Ensure-Folder {
    param([string]$PathToCreate)
    if (-not (Test-Path $PathToCreate)) {
        New-Item -ItemType Directory -Path $PathToCreate | Out-Null
    }
}

function Copy-FilteredTree {
    param(
        [string]$Source,
        [string]$Destination
    )

    $excludeDirNames = @(
        ".git",
        ".github",
        ".vscode",
        "node_modules",
        ".idea"
    )

    $excludeFileNames = @(
        "Thumbs.db",
        ".DS_Store"
    )

    $excludePatterns = @(
        "*.log",
        "*.zip"
    )

    $allItems = Get-ChildItem -Path $Source -Recurse -Force

    foreach ($item in $allItems) {
        $relative = $item.FullName.Substring($Source.Length).TrimStart('\','/')
        if ([string]::IsNullOrWhiteSpace($relative)) { continue }

        $parts = $relative -split '[\\/]'
        $skip = $false

        foreach ($part in $parts) {
            if ($excludeDirNames -contains $part) {
                $skip = $true
                break
            }
        }

        if ($skip) { continue }

        if (-not $item.PSIsContainer) {
            if ($excludeFileNames -contains $item.Name) { continue }

            foreach ($pattern in $excludePatterns) {
                if ($item.Name -like $pattern) {
                    $skip = $true
                    break
                }
            }

            if ($skip) { continue }
        }

        $targetPath = Join-Path $Destination $relative

        if ($item.PSIsContainer) {
            Ensure-Folder -PathToCreate $targetPath
        } else {
            $targetParent = Split-Path $targetPath -Parent
            Ensure-Folder -PathToCreate $targetParent
            Copy-Item -Path $item.FullName -Destination $targetPath -Force
        }
    }
}

function Remove-UnwantedCoreFiles {
    param([string]$CoreStage)

    $pathsToRemove = @(
        ".gitignore",
        "FoundryPress.code-workspace",
        "REPO_NOTES.md"
    )

    foreach ($path in $pathsToRemove) {
        $full = Join-Path $CoreStage $path
        Remove-IfExists -PathToRemove $full
    }

    $proPath = Join-Path $CoreStage "pro"
    Remove-IfExists -PathToRemove $proPath
}

function Remove-UnwantedProFiles {
    param([string]$ProStage)

    $pathsToRemove = @(
        ".gitignore",
        "FoundryPress.code-workspace",
        "REPO_NOTES.md"
    )

    foreach ($path in $pathsToRemove) {
        $full = Join-Path $ProStage $path
        Remove-IfExists -PathToRemove $full
    }
}

function New-ZipFromFolder {
    param(
        [string]$SourceFolder,
        [string]$ZipPath
    )

    if (Test-Path $ZipPath) {
        Remove-Item $ZipPath -Force
    }

    Compress-Archive -Path (Join-Path $SourceFolder '*') -DestinationPath $ZipPath -CompressionLevel Optimal
}

$root = (Resolve-Path $ProjectRoot).Path
$coreSource = Join-Path $root "core"
$proSource = Join-Path $root "pro"
$releasesDir = Join-Path $root "releases"
$buildDir = Join-Path $root "build"

if (-not (Test-Path $coreSource)) {
    throw "Could not find core/ at: $coreSource"
}

if (-not (Test-Path $proSource)) {
    throw "Could not find pro/ at: $proSource"
}

if ([string]::IsNullOrWhiteSpace($Version)) {
    $Version = Read-Host "Enter release version (example: 1.0.0)"
}

$coreStage = Join-Path $buildDir "core-stage"
$proStage = Join-Path $buildDir "pro-stage"

$coreZip = Join-Path $releasesDir ("foundrypress-core-v{0}.zip" -f $Version)
$proZip = Join-Path $releasesDir ("foundrypress-pro-v{0}.zip" -f $Version)

Write-Step "Preparing folders"
Ensure-Folder -PathToCreate $releasesDir
Remove-IfExists -PathToRemove $coreStage
Remove-IfExists -PathToRemove $proStage
Ensure-Folder -PathToCreate $coreStage
Ensure-Folder -PathToCreate $proStage

Write-Step "Copying core files"
Copy-FilteredTree -Source $coreSource -Destination $coreStage
Remove-UnwantedCoreFiles -CoreStage $coreStage

Write-Step "Copying pro files"
Copy-FilteredTree -Source $proSource -Destination $proStage
Remove-UnwantedProFiles -ProStage $proStage

Write-Step "Creating zip archives"
New-ZipFromFolder -SourceFolder $coreStage -ZipPath $coreZip
New-ZipFromFolder -SourceFolder $proStage -ZipPath $proZip

Write-Step "Release complete"
Write-Host "Core ZIP: $coreZip" -ForegroundColor Green
Write-Host "Pro ZIP:  $proZip" -ForegroundColor Green
