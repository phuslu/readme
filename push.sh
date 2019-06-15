#!/bin/bash

set -ex

AUTOINDEX_HTML=$(pwd -P)/autoindex.html

for FILE in $(find . -type f -name "*.md" -not -iname "readme.md"); do
	cat ${FILE} markdown.html >${FILE%.*}.html
	git add ${FILE%.*}.html
done

for DIR in . $(ls -l | grep '^d' | awk '{print $NF}'); do
    pushd ${DIR}
    ls -phl --time-style='+%d-%b-%Y %H:%M' --group-directories-first | awk 'BEGIN{print "<title>Index of /</title>"}{printf("<a href=\"%s\">%s</a>\t%s %s %s\n", $8, $8, $6, $7, $5)}' >index.html
    cat /opt/home/phuslu.github.io/autoindex.html >>index.html
    git add index.html
    popd
done

git commit -m "[skip ci] build new index" -s || true

git push origin master

