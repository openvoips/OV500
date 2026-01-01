@REM check and set Visual Studio environment
CALL msbuild.cmd
echo %msbuild%
cmd /C %msbuild% libks.sln /p:Configuration=Debug /p:Platform=Win32 /t:Build
cmd /C %msbuild% libks.sln /p:Configuration=Debug /p:Platform=x64 /t:Build 
cmd /C %msbuild% libks.sln /p:Configuration=Release /p:Platform=Win32 /t:Build 
cmd /C %msbuild% libks.sln /p:Configuration=Release /p:Platform=x64 /t:Build
echo Done! Packages (zip files) were placed to the "out" folder.