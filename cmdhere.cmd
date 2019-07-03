net session >nul 2>&1 || (echo Please run as Administrator && pause>null && exit /b 0)

reg add "HKLM\SYSTEM\CurrentControlSet\Control\Session Manager\Memory Management" /v "SwapfileControl" /t REG_DWORD /d 0 /f
reg add "HKLM\SOFTWARE\Microsoft\Command Processor" /v "DisableUNCCheck" /t REG_DWORD /d 1 /f
reg add "HKLM\SOFTWARE\Microsoft\Command Processor" /v "PathCompletionChar" /t REG_DWORD /d 64 /f
reg add "HKLM\SOFTWARE\Microsoft\Command Processor" /v "CompletionChar" /t REG_DWORD /d 9 /f

reg add "HKCU\Console" /v "ScreenBufferSize" /t REG_DWORD /d 65536100 /f
reg add "HKCU\Console" /v "WindowSize" /t REG_DWORD /d 2097252 /f
reg add "HKCU\Console" /v "QuickEdit" /t REG_DWORD /d 1 /f

ver | findstr /r "5\." && (
    reg add "HKCR\Folder\shell\DOS" /v "" /t REG_SZ /d "@shell32.dll,-22022" /f
    reg add "HKCR\Folder\shell\DOS\command" /v "" /t REG_SZ /d "cmd.exe /d /s /k ver" /f
)
ver | findstr /r "6\." && (
    reg add "HKCR\Directory\Background\shell\runas" /v "" /t REG_SZ /d "@shell32.dll,-22022" /f
    reg add "HKCR\Directory\Background\shell\runas" /v "Icon" /t REG_SZ /d "%windir%\System32\imageres.dll,-78" /f
    echo %~d0 | findstr ":" && (
        reg add "HKCR\Directory\Background\shell\runas\command" /v "" /t REG_SZ /d "cmd.exe /d /s /k ver && cd /d %%v" /f
    ) || (
        reg add "HKCR\Directory\Background\shell\runas\command" /v "" /t REG_SZ /d "cmd.exe /d /s /k ver && cd /d %v" /f
    )
)
ver | findstr /r "10\." && (
    reg add "HKCU\Console" /v CodePage /t REG_DWORD /d 65001 /f
    reg add "HKCU\Console" /v FaceName /t REG_SZ /d "Consolas" /f
    reg add "HKCU\Console\%SystemRoot^%_system32_cmd.exe" /v CodePage /t REG_DWORD /d 65001
    reg add "HKCR\Directory\Background\shell\runas" /v "" /t REG_SZ /d "@shell32.dll,-22022" /f
    reg add "HKCR\Directory\Background\shell\runas" /v "HasLUAShield" /t REG_SZ /d "" /f
    echo %~d0 | findstr ":" && (
        reg add "HKCR\Directory\Background\shell\runas\command" /v "" /t REG_SZ /d "cmd.exe /d /s /k ver && cd /d %%v" /f
    ) || (
        reg add "HKCR\Directory\Background\shell\runas\command" /v "" /t REG_SZ /d "cmd.exe /d /s /k ver && cd /d %v" /f
    )
)

pause
