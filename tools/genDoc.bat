@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/cli/index.php
php "%BIN_TARGET%" genDoc:index source=%~dp0/../app/api/controllers output=%~dp0/../htdocs/doc apiHost=
