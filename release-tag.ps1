param(
    [string]$Version = ""
)

$ErrorActionPreference = "Stop"

function Write-Step {
    param([string]$Message)
    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

if ([string]::IsNullOrWhiteSpace($Version)) {
    $Version = Read-Host "Enter version (example: 1.0.0)"
}

$tag = "v$Version"

Write-Step "Creating git tag"
git tag $tag

Write-Step "Pushing tag to GitHub"
git push origin $tag

Write-Step "Release checklist"

Write-Host ""
Write-Host "✔ Upload these files to Cloudflare/CDN:" -ForegroundColor Green
Write-Host "  - releases/foundrypress-core-v$Version.zip"
Write-Host "  - releases/foundrypress-pro-v$Version.zip"

Write-Host ""
Write-Host "✔ Verify:" -ForegroundColor Green
Write-Host "  - Core installs clean on new domain"
Write-Host "  - License activates"
Write-Host "  - Pro installs over core"
Write-Host "  - SEO saves + renders"

Write-Host ""
Write-Host "✔ Update sales site:" -ForegroundColor Green
Write-Host "  - Download links"
Write-Host "  - Version references"
Write-Host "  - Changelog page"

Write-Host ""
Write-Host "Release $tag complete!" -ForegroundColor Yellow
