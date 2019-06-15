#!/bin/bash

set -ex

ROOT="$(cd "$(dirname "$0")"; pwd -P)"

function index() {
	ls -phl --time-style='+%d-%b-%Y %H:%M' --ignore index.html --group-directories-first |
	awk 'BEGIN {print "<title>Index of /</title>"}
		{printf("<a href=\"%s\">%s</a>\t%s %s %s\n", $8, $8, $6, $7, $5)}' >index.html
	cat ${ROOT}/autoindex.html >>index.html
}

function gen() {
	find . -type d -not -path '*/\.*' -exec bash -c "cd {} && $ROOT/$0 index" \;
}

function push() {
	git commit -m "[skip ci] build new index" -s -a || true
	git push origin master
}

$1
