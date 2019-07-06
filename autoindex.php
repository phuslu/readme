<?php
/*
autoindex.php for apache

.htaccess

  AddDefaultCharset utf-8
  RewriteEngine on
  RewriteRule ^.+/$ autoindex.php
  RewriteRule ^$ autoindex.php

*/

$rootdir = $_SERVER['DOCUMENT_ROOT'];
$dirname = $_SERVER['REQUEST_URI'];

function human_fileinfo($file) {
    $fullname = $GLOBALS['rootdir'] . $GLOBALS['dirname'] . $file;
    $bytes = filesize($fullname);
    $factor = floor(log($bytes, 1024));
    $units = array('', 'KB', 'MB', 'GB', 'TB', 'PB');
    $mtime = date('j-M-Y H:i', filemtime($fullname));
    $size = sprintf("%.0f%s", $bytes / pow(1024, $factor), $units[$factor]);
    return $mtime . ' ' . $size;
}

$files = array();
foreach (scandir($rootdir . $dirname) as $file)
    if ($file[0] != '.' && $file[0] != '@' && $file != 'autoindex.php')
        array_push($files, $file);

printf("<meta charset='UTF-8'><title>Index of %s</title><hr><pre>\n", $dirname);
foreach ($files as $file)
    if (is_dir($file))
        printf("<a href='%s/'>%s/</a> %s\n", $file, $file, human_fileinfo($file));
foreach ($files as $file)
    if (!is_dir($file))
        printf("<a href='%s'>%s</a> %s\n", $file, $file, human_fileinfo($file));
printf("</pre><hr>\n");

readfile($rootdir . '/autoindex.html');

