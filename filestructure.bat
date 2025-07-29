@echo off
REM This batch file creates the folder and file structure for the Simple POS System.

REM --- Create main directories ---
echo Creating main directories...
mkdir pos_system
mkdir pos_system\includes
mkdir pos_system\data
mkdir pos_system\css
mkdir pos_system\js

REM --- Create PHP files in the root directory ---
echo Creating PHP files...
type nul > pos_system\index.php
type nul > pos_system\auth.php
type nul > pos_system\products.php
type nul > pos_system\sales.php
type nul > pos_system\reports.php

REM --- Create PHP files in the 'includes' directory ---
echo Creating include files...
type nul > pos_system\includes\data_handler.php
type nul > pos_system\includes\ui_components.php
type nul > pos_system\includes\helpers.php

REM --- Create JSON data files in the 'data' directory ---
echo Creating JSON data files...
type nul > pos_system\data\products.json
type nul > pos_system\data\sales.json
type nul > pos_system\data\users.json

REM --- Create CSS files in the 'css' directory ---
echo Creating CSS files...
type nul > pos_system\css\style.css

REM --- Create JavaScript files in the 'js' directory ---
echo Creating JavaScript files...
type nul > pos_system\js\script.js

REM --- Create optional .htaccess file ---
echo Creating optional .htaccess file...
type nul > pos_system\.htaccess

echo.
echo Folder and file structure created successfully in the 'pos_system' directory.
echo.
pause