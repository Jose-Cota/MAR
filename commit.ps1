param(
    [Parameter(Mandatory=$true)]
    [string]$mensaje
)

git add .
git commit -m $mensaje
git push

Write-Host "✅ Commit enviado: $mensaje" -ForegroundColor Green