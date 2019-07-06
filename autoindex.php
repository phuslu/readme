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
	$mtime = date('j-M-Y H:i', filemtime($fullname));
	$bytes = filesize($fullname);
	$factor = floor(log($bytes, 1024));
	$units = array('', 'KB', 'MB', 'GB', 'TB', 'PB');
	$size = sprintf("%.0f%s", $bytes / pow(1024, $factor), $units[$factor]);
	return $mtime . ' ' . $size;
}

$files = array_filter(scandir($rootdir . $dirname), function($file) {
    return $file[0] != '.' && $file[0] != '@' && $file != 'autoindex.php';
});

printf("<meta charset='UTF-8'><title>Index of %s</title><hr><pre>\n", $dirname);
foreach (array_filter($files, is_dir) as $file)
	printf("<a href='%s/'>%s/</a> %s\n", $file, $file, human_fileinfo($file));
foreach (array_filter($files, function($k) { return !is_dir($k); }) as $file)
	printf("<a href='%s'>%s</a> %s\n", $file, $file, human_fileinfo($file));
printf("</pre><hr>\n");

readfile($rootdir . '/autoindex.html');
