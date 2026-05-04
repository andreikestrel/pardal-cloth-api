#!/usr/bin/env pwsh
# Starts Laravel (server + queue + logs) and Vite in a single terminal.
# Usage: .\dev.ps1
Set-Location $PSScriptRoot
composer run dev
