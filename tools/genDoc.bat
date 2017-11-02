@ECHO OFF
set BIN_TARGET=%~dp0/cli/index.php

:: Scan source controller file path
set source=%~dp0/../app/api/controllers
:: Document file output path
set output=%~dp0/../htdocs/doc
:: API form submit address
set apiHost=//127.0.0.1/skeleton/htdocs/api
:: Asset server
set assetServer=

php %BIN_TARGET% genDoc:index source=%source% output=%output% apiHost=%apiHost% assetServer=%assetServer%
