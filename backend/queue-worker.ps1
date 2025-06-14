$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$logFile = Join-Path $scriptPath "storage\logs\queue-worker.log"

# Create log directory if it doesn't exist
$logDir = Split-Path -Parent $logFile
if (!(Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force
}

while ($true) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    try {
        # Start queue worker
        Write-Output "$timestamp - Starting queue worker..." | Out-File -Append $logFile
        & php "$scriptPath\artisan" queue:work database --sleep=3 --tries=3 --max-time=3600 2>&1 | Out-File -Append $logFile
    }
    catch {
        Write-Output "$timestamp - Error: $_" | Out-File -Append $logFile
    }
    
    # Wait before restarting
    Write-Output "$timestamp - Queue worker stopped. Restarting in 5 seconds..." | Out-File -Append $logFile
    Start-Sleep -Seconds 5
}
