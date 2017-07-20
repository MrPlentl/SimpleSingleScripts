<?php
# File:           Apache-PHP-Updater.php   
# Current Ver:    1.0
# Function:       To create an easy to use, light weight utility that can upgrade your Apache and PHP installations.
# Author:         Brandon Plentl (bp)
# Environment:    PhpStorm - Windows 10
# Github URL:     https://github.com/MrPlentl/SimpleSingleScripts
# Code Cleaned:   08/29/2016
# Code Validated: 08/29/2016
# Notes:          It would be nice to add the ability to auto download updates; plus, it would be really nice if I could shutdown the Apache Service from the script if I could get access.
# Fixes Needed:	  None... for now.
# Revisions:      1.0 - 2016-08-29 (bp) - First Release


/* ### SETUP ###
 * Look for the configuration section below
 * EDIT: Set the Apache and PHP locations, also add any configuration files you will want copied to the new installation.
 * NOTE: If this is your first time running the script, set QUICK_INSTALL to FALSE to add sleep() breaks so you can see what is going on.
 * DOWNLOAD: the latest version of [Apache](https://www.apachelounge.com/download/) and [PHP](http://windows.php.net/download/) that matches your current installation.
 * MOVE: the Apache-PHP-Updater.php script and the downloaded Apache/PHP into the same location
 * STOP: the Apache Service and close the Apache Monitor if running. Without loading external dlls, this just didn't seem possible programmatically.
 * NAVIGATE: Open a command line and navigate to the Apache-PHP-Updater.php script location.
 * RUN: php Apache-PHP-Updater.php - PHP must be added to your System Environment Path, otherwise you will need to list the full path to the php.exe
 */


///////////////////////////////////////////////////////////////////////
///////////////////////  CONFIGURATION  ///////////////////////////////
///////////////////////////////////////////////////////////////////////

define("APACHE_LOCATION", "C:\\Apache\\Apache2.4");
define("PHP_LOCATION", "C:\\PHP\\PHP7");
define("APACHE_REUSED_FILES",array(
                                "\\conf\\httpd.conf",
                                "\\conf\\extra\\httpd-vhosts.conf",
                                "\\SHORTCUTS"
                            ));

define("PHP_REUSED_FILES",array(
                                "\\php.ini",
                                "\\php-cli-context-menu.bat",
								"\\ext\\php_pdo_sqlsrv.dll",
								"\\ext\\php_sqlsrv.dll"
                            ));

define("QUICK_INSTALL", FALSE);

///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////


### PROCESS ###
/*
 * Welcome Message
 * Scan Current Directory for "http" and "php" zip files
 * Lists all available Apache / PHP Zip files
 * USER - Chooses Apache / PHP Version to Install
 * MSG - The current versions of the following files will be reused from previous installations:
 * (Reused files are defined in the configuration section at the top of this script)
 * DISPLAY - ReusedFiles Array (This )
 * MSG - Ctrl->C to Quit
 * SLEEP - 5 seconds
 * MSG - Starting the Update Process...
 * RENAME - Old Apache to <DIR NAME>_backup_<timestamp>
 * RENAME - Old PHP to <DIR NAME>_backup_<timestamp>.
 * EXTRACT - Create __processing folder and Extract Apache/PHP to it
 * SCAN - Look for the Config directory to validate Apache/PHP roota
 * MOVE - Move the ReusedFiles from Backup <DIR NAME> to the Processing
 * MOVE - Process Apache/PHP and move to the Previous Apache/PHP Install Directories
 * DELETE __processing folder
 * DONE
 */

### Global Variables ###
$error=array(FALSE,"No Errors");
$subDirectoryCount=0;
$keepGoing=TRUE;
$hasApache=FALSE;
$hasPHP=FALSE;
$apacheUpgradeFile="";
$phpUpgradeFile="";

## END GLOBALS

if (!file_exists("__processing")) {
    mkdir("__processing", 0777, TRUE);
}

### FUNCTIONS ###
function scanForZipFiles($current_directory,$apacheOnly=FALSE,$phpOnly=FALSE)
{
    global $error;
    global $hasApache;
    global $hasPHP;

    $arr_ZipFiles = array();

    ## Scan the $current_directory and create an array list of all of the files and directories
    $arrDocs = array_diff(scandir($current_directory), array('..', '.'));  //Scan the directory and pull the files different than '..', and '.'
    natcasesort($arrDocs);  //Sort the File List

    if ($apacheOnly == FALSE && $phpOnly == FALSE) {
        if (isset($arrDocs) && is_array($arrDocs) && count($arrDocs) > 0 && $error[0] === FALSE) {
            foreach ($arrDocs as $a)               // For each document in the current document array
            {
                // File search and count
                if (is_file($current_directory . "/" . $a) && $a != "." && $a != ".." && substr($a, strlen($a) - 3, 3) != ".db" && pathinfo($a, PATHINFO_EXTENSION) == "zip")      //The "." and ".." are directories.  "." is the current and ".." is the parent
                {
                    if(strstr($a,"http")){
                        echo "## APACHE upgrade available ##" . PHP_EOL;
                        $hasApache = TRUE;
                    } else if (strstr($a,"php")) {
                        echo "## PHP upgrade available ##" . PHP_EOL;
                        $hasPHP = TRUE;
                    }
                    array_push($arr_ZipFiles, $a);
                }
            }
            //return $arr_ZipFiles;
        }
    } else if ($apacheOnly == TRUE && $phpOnly == FALSE) {
        if (isset($arrDocs) && is_array($arrDocs) && count($arrDocs) > 0 && $error[0] === FALSE) {
            foreach ($arrDocs as $a)               // For each document in the current document array
            {
                // File search and count
                if (is_file($current_directory . "/" . $a) && $a != "." && $a != ".." && substr($a, strlen($a) - 3, 3) != ".db" && pathinfo($a, PATHINFO_EXTENSION) == "zip" && strstr($a,"http"))      //The "." and ".." are directories.  "." is the current and ".." is the parent
                {
                    array_push($arr_ZipFiles, $a);
                }
            }
            //return $arr_ZipFiles;
        }

    } else if ($apacheOnly == FALSE && $phpOnly == TRUE) {
        if (isset($arrDocs) && is_array($arrDocs) && count($arrDocs) > 0 && $error[0] === FALSE) {
            foreach ($arrDocs as $a)               // For each document in the current document array
            {
                // File search and count
                if (is_file($current_directory . "/" . $a) && $a != "." && $a != ".." && substr($a, strlen($a) - 3, 3) != ".db" && pathinfo($a, PATHINFO_EXTENSION) == "zip" && strstr($a,"php"))      //The "." and ".." are directories.  "." is the current and ".." is the parent
                {
                    array_push($arr_ZipFiles, $a);
                }
            }
            //return $arr_ZipFiles;
        }
    } else {
        $error = array(TRUE,"FUNCTION scanForZipFiles has both Apache Only and PHP Only scans set to TRUE");
    }

    return $arr_ZipFiles;
}

function unzipAndUpgrade($myZipFile, $program){

    ######### ZIP EXTRACTION   #############
    $zip = new ZipArchive;   // Using the PHP ZipArchive Class. http://php.net/manual/en/class.ziparchive.php
    if ($zip->open($myZipFile) === TRUE) {

        $tempFileName = "temp_" . date("Ymds") . "_" . substr($myZipFile,0,-4);   // This will remove the .zip off the end and it is also the temporary name used while processing
        $extracted_location = "__processing/". $tempFileName;

        $zip->extractTo($extracted_location);   // Extracts the zipfile
        $zip->close();                          // Close opened or created archive

        echo 'Files Extracted to ' . $extracted_location . PHP_EOL;

        if($program=="APACHE"){
            rename($extracted_location."/Apache24",APACHE_LOCATION);
        } else if($program=="PHP"){
            rename($extracted_location,PHP_LOCATION);
        }
    } else {
        echo 'Cannot find '. $myZipFile . " in ". dirname(__FILE__) . PHP_EOL;
    }
}

# This Method simply runs the full process to DELETE a Directory; includes all files and sub-directories
function cleanAndDelete($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") cleanAndDelete($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

###  END FUNCTIONS  ###
echo PHP_EOL;

#### START ####
echo PHP_EOL;
echo "#####################################################################################". PHP_EOL;
echo "#### WARNING: Make sure to STOP the Apache Service before continuing any further ####". PHP_EOL;
echo "#####################################################################################". PHP_EOL;
echo PHP_EOL;

sleep(8);

// Welcome Message
echo "--------------------------------" . PHP_EOL . "Starting the APACHE/PHP Updater" . PHP_EOL . "--------------------------------" . PHP_EOL;
if(QUICK_INSTALL===FALSE) echo "QUICK_INSTALL IS TURNED OFF" . PHP_EOL;
if(QUICK_INSTALL===FALSE) sleep(5);

// Scan Current Directory for "http" and "php" zip files
echo "Scanning for Apache and PHP Zip Files in " . dirname(__FILE__) . PHP_EOL;
$arr_ZipFilesFound = scanForZipFiles(dirname(__FILE__));

if(count($arr_ZipFilesFound)>0){
    echo "Possible Upgrades found!" . PHP_EOL;

    foreach ( $arr_ZipFilesFound as $choice => $file ) {
        fwrite(STDOUT, "- $file".PHP_EOL);
    }
} else{
    $error = array(TRUE, "");
}

echo PHP_EOL;
//var_dump($arr_ZipFilesFound);

### APACHE ###
// List all available Apache Zip files
if ($hasApache==TRUE){

    echo "APACHE || SELECT the Version you want to UPDATE: " . PHP_EOL;
    $arr_ZipFilesFound = scanForZipFiles(dirname(__FILE__),TRUE,FALSE);

    foreach ( $arr_ZipFilesFound as $choice => $file ) {
        fwrite(STDOUT, "\t$choice) $file\n");
    }
    fwrite(STDOUT, "\tq) Quit\n" . PHP_EOL . "=> ");

// USER - Choose Apache Version to Install
    // Loop until they enter 'q' for Quit
    do {

        // A character from STDIN, ignoring whitespace characters
        do {
            $selection = fgetc(STDIN);
        } while ( trim($selection ) == '' );

        if($selection=="q"){
            $keepGoing=FALSE;
            echo "Quiting.....";
            sleep(2);
            die();
        }

        if ( array_key_exists($selection,$arr_ZipFilesFound) ) {

            fwrite(STDOUT, "Apache will be upgraded using {$arr_ZipFilesFound[$selection]}".PHP_EOL.PHP_EOL);

            if(strstr($arr_ZipFilesFound[$selection],"http")){
                $apacheUpgradeFile = $arr_ZipFilesFound[$selection];
                $keepGoing = FALSE;
            }
        }
    } while ( $keepGoing == TRUE);
}

if($hasPHP==TRUE){

    echo "PHP || SELECT the Version you want to UPDATE: " . PHP_EOL;
    $arr_ZipFilesFound = scanForZipFiles(dirname(__FILE__),FALSE,TRUE);

    foreach ( $arr_ZipFilesFound as $choice => $file ) {
        fwrite(STDOUT, "\t$choice) $file\n");
    }
    fwrite(STDOUT, "\tq) Quit\n" . PHP_EOL . "=> ");

// USER - Choose PHP Version to Install
    // Loop until they enter 'q' for Quit
    do {

        // A character from STDIN, ignoring whitespace characters
        do {
            $selection = fgetc(STDIN);
        } while ( trim($selection ) == '' );

        if($selection=="q"){
            $keepGoing=FALSE;
            echo "Quiting.....";
            sleep(2);
            die();
        }

        if ( array_key_exists($selection,$arr_ZipFilesFound) ) {

            fwrite(STDOUT, "PHP will be upgraded using {$arr_ZipFilesFound[$selection]}".PHP_EOL.PHP_EOL);

            if(strstr($arr_ZipFilesFound[$selection],"php")){
                $phpUpgradeFile = $arr_ZipFilesFound[$selection];
                $keepGoing = FALSE;
            }
        }

    } while ( $keepGoing == TRUE);
}

if($error[0]===FALSE) {
    echo "UPDATER is ready to upgrade the following:" . PHP_EOL;
    if(isset($apacheUpgradeFile)&&$apacheUpgradeFile!="") echo "\t" . "APACHE: ". $apacheUpgradeFile. PHP_EOL;
    if(isset($phpUpgradeFile)&&$phpUpgradeFile!="") echo "\t". "PHP: " . $phpUpgradeFile. PHP_EOL;
    echo PHP_EOL;
} else{
    echo $error[1];
    die();
}

echo "Ctrl->C to Quit at anytime".PHP_EOL;
if(QUICK_INSTALL===FALSE) sleep(5);

echo "Reading in the USER DEFINED variables:".PHP_EOL;
if(isset($apacheUpgradeFile)&&$apacheUpgradeFile!=""){
    echo "Apache Install Location: ".APACHE_LOCATION . PHP_EOL;
    if(count(APACHE_REUSED_FILES)>0){
        echo "\tFiles to reuse: " . PHP_EOL;
        foreach(APACHE_REUSED_FILES as $a){
            echo "\t :: ".$a . PHP_EOL;
        }
    } else{
        echo "WARNING: No Files were DEFINED to be reused. Config files will load the updated defaults.";
    }

    # VALIDATE PREVIOUS INSTALL
    echo "APACHE: Validating Previous Install || ";

if(QUICK_INSTALL===FALSE) sleep(5);

    if(is_file(APACHE_LOCATION."\\conf\\httpd.conf")){
        echo "FOUND". PHP_EOL;
    } else{
        $error=array(TRUE,"APACHE INSTALL NOT FOUND");
        echo "ERROR: APACHE INSTALL NOT FOUND IN " . APACHE_LOCATION . PHP_EOL;
        echo "--------------------".PHP_EOL."Fix APACHE_LOCATION in the Configuration section at the top of the script and rerun.".PHP_EOL."--------------------".PHP_EOL;
        sleep(2);
        die();
    }
}

echo PHP_EOL;

if(isset($phpUpgradeFile)&&$phpUpgradeFile!="") {
    echo "PHP Install Location: ".PHP_LOCATION . PHP_EOL;
    if(count(PHP_REUSED_FILES)>0){
        echo "\tFiles to reuse: " . PHP_EOL;
        foreach(PHP_REUSED_FILES as $a){
            echo "\t :: ".$a . PHP_EOL;
        }
    } else{
        echo "WARNING: No Files were DEFINED to be reused. Config files will load the updated defaults.";
    }

    # VALIDATE PREVIOUS INSTALL
    echo "PHP: Validating Previous Install || ";

if(QUICK_INSTALL===FALSE) sleep(5);

    if(is_file(PHP_LOCATION."\\php.ini")){
        echo "FOUND". PHP_EOL;
    } else{
        $error=array(TRUE,"PHP INSTALL NOT FOUND");
        echo "ERROR: PHP INSTALL NOT FOUND IN " . PHP_LOCATION. PHP_EOL;
        echo "--------------------".PHP_EOL."Fix PHP_LOCATION in the Configuration section at the top of the script and rerun.".PHP_EOL."--------------------".PHP_EOL;
        sleep(1);
        die();
    }
}
$keepGoing=TRUE;
if(QUICK_INSTALL===FALSE) sleep(5);
echo "--------------------".PHP_EOL." Beginning Upgrade ".PHP_EOL."--------------------".PHP_EOL;
if(QUICK_INSTALL===FALSE) sleep(1);
if ($hasApache==TRUE) {
    echo "###  Upgrading APACHE  ###" . PHP_EOL;
    echo "RENAMING " . APACHE_LOCATION . " to " . APACHE_LOCATION . "_backup" . PHP_EOL;
# RENAME - Old Apache to backup_<DIR NAME>
    if (file_exists(APACHE_LOCATION . "_backup")) rename(APACHE_LOCATION . "_backup", APACHE_LOCATION . "_backup_" . date("Ymds"));
    rename(APACHE_LOCATION, APACHE_LOCATION . "_backup");
    echo "EXTRACTING Apache... " . PHP_EOL;
# EXTRACT - Apache to Processing Folder
    if (isset($apacheUpgradeFile) && $apacheUpgradeFile != "") unzipAndUpgrade($apacheUpgradeFile, "APACHE");
    foreach (APACHE_REUSED_FILES as $a) {
        echo "Copying " . $a . " FROM " . APACHE_LOCATION . "_backup" . " TO " . APACHE_LOCATION . PHP_EOL;
		
		if(is_dir(APACHE_LOCATION . "_backup" . $a)){
			rename(APACHE_LOCATION . "_backup" . $a, APACHE_LOCATION . $a);
			mkdir(APACHE_LOCATION . "_backup" . $a . "_moved");
		} else {
			copy(APACHE_LOCATION . "_backup" . $a, APACHE_LOCATION . $a);
		}
    }

    echo "------------------" . PHP_EOL . " Apache Upgrade Complete " . PHP_EOL . "------------------" . PHP_EOL;

    if (QUICK_INSTALL === FALSE) sleep(5);
}

# RENAME - Old PHP to backup_<DIR NAME>
echo "RENAMING ". PHP_LOCATION . " to " . PHP_LOCATION."_backup" . PHP_EOL;
if(file_exists (PHP_LOCATION."_backup")) rename(PHP_LOCATION."_backup", PHP_LOCATION."_backup_" . date("Ymds"));
rename(PHP_LOCATION,PHP_LOCATION."_backup");
# EXTRACT - PHP to Processing Folder
echo "EXTRACTING PHP... ".PHP_EOL;
if(isset($phpUpgradeFile)&&$phpUpgradeFile!="") unzipAndUpgrade($phpUpgradeFile,"PHP");
foreach (PHP_REUSED_FILES as $a){
    echo "Copying " . $a . " FROM " . PHP_LOCATION."_backup" . " TO " .PHP_LOCATION . PHP_EOL;
	
	if(is_dir(PHP_LOCATION . "_backup" . $a)){
		rename(PHP_LOCATION . "_backup" . $a, PHP_LOCATION . $a);
		mkdir(PHP_LOCATION . "_backup" . $a . "_moved");
	} else {
		copy(PHP_LOCATION . "_backup" . $a, PHP_LOCATION . $a);
	}
		
    copy(PHP_LOCATION."_backup".$a,PHP_LOCATION.$a);
}

echo "------------------".PHP_EOL." PHP Upgrade Complete ".PHP_EOL."------------------".PHP_EOL;

cleanAndDelete("__processing");

echo PHP_EOL;
echo "######################################". PHP_EOL;
echo "#### YOU CAN NOW RESTART APACHE ######". PHP_EOL;
echo "######################################". PHP_EOL. PHP_EOL;