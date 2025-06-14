$serviceName = "NSGMoAQueueWorker"
$displayName = "NSG MoA Queue Worker Service"
$description = "Queue worker service for Nasarawa State Ministry of Agriculture M&E System"
$scriptPath = Join-Path $PSScriptRoot "queue-worker.ps1"

# Check if NSSM is installed
if (!(Get-Command nssm -ErrorAction SilentlyContinue)) {
    Write-Host "NSSM (Non-Sucking Service Manager) is required but not installed."
    Write-Host "Please install NSSM first using: winget install nssm"
    Write-Host "Or download from: https://nssm.cc/"
    exit 1
}

# Remove existing service if it exists
if (Get-Service $serviceName -ErrorAction SilentlyContinue) {
    Write-Host "Removing existing service..."
    nssm remove $serviceName confirm
}

# Install new service
Write-Host "Installing new service..."
nssm install $serviceName powershell.exe
nssm set $serviceName AppParameters "-ExecutionPolicy Bypass -NoProfile -File `"$scriptPath`""
nssm set $serviceName DisplayName $displayName
nssm set $serviceName Description $description
nssm set $serviceName AppDirectory $PSScriptRoot
nssm set $serviceName AppStdout "$PSScriptRoot\storage\logs\queue-worker-service.log"
nssm set $serviceName AppStderr "$PSScriptRoot\storage\logs\queue-worker-service-error.log"
nssm set $serviceName Start SERVICE_AUTO_START

Write-Host "Starting service..."
Start-Service $serviceName

Write-Host "Service installation complete. Status:"
Get-Service $serviceName
