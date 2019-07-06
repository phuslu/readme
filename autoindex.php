<?php

$rootdir = $_SERVER['DOCUMENT_ROOT'];
$dirname = $_SERVER['REQUEST_URI'];

function human_filemtime($file) {
    return date('j-M-Y H:i', filemtime($GLOBALS['rootdir'] . $GLOBALS['dirname'] . $file));
}

function human_filesize($file) {
    $size = filesize($GLOBALS['rootdir'] . $GLOBALS['dirname'] . $file);
    $factor = floor(log($size, 1024));
    $units = array('', 'KB', 'MB', 'GB', 'TB', 'PB');
    return sprintf("%.0f%s", $size / pow(1024, $factor), $units[$factor]);
}

$files = array();
foreach (scandir($rootdir . $dirname) as $file)
    if ($file[0] != '.' && $file[0] != '@' && $file != 'autoindex.php')
        array_push($files, $file);

printf("<meta charset='UTF-8'><title>Index of %s</title><hr><pre>\n", $dirname);
foreach ($files as $file)
    if (is_dir($file))
        printf("<a href='%s/'>%s/</a> %s %s\n", $file, $file, human_filemtime($file), human_filesize($file));
foreach ($files as $file)
    if (!is_dir($file))
        printf("<a href='%s'>%s</a> %s %s\n", $file, $file, human_filemtime($file), human_filesize($file));
printf("</pre><hr>\n");

readfile($rootdir . '/autoindex.html');

