<?php

$array_ignored_files = array(
    ".",
    "..",
    ".DS_Store",
    basename(__FILE__)
);

$DocDirectory = "./.";   //Directory to be scanned
$arrDocs = array_diff(scandir($DocDirectory), array('..', '.'));  //Scan the $DocDirectory and create an array list of all of the files and directories
natcasesort($arrDocs);
if( isset($arrDocs) && is_array($arrDocs) )
{
    foreach( $arrDocs as $a )   //For each document in the current document array
    {
        $fileName = pathinfo( $a, PATHINFO_FILENAME );
        $fileExt = pathinfo($a, PATHINFO_EXTENSION);
        $newFileExt = "php";

        // Directory search and count
        if( is_file($DocDirectory . "/" . $a) && !in_array($a,$array_ignored_files) && substr($a,strlen($a)-3,3) != ".db" && pathinfo($a, PATHINFO_EXTENSION) == "html")      //The "." and ".." are directories.  "." is the current and ".." is the parent
        {
            $oldname = $fileName . '.' . $fileExt;
            $newname = $fileName . '.blade.' . $newFileExt;
            echo "Renaming " . $oldname . " to " . $newname . "\n";
            rename($oldname, $newname);
        }
    }
}

?>