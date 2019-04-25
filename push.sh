#!/bin/bash

set -ex

AUTOINDEX_HTML=$(pwd -P)/autoindex.html

for FILE in $(find . -type f -name "*.md" -not -iname "readme.md"); do
	cat ${FILE} markdown.html >${FILE%.*}.html
	git add ${FILE%.*}.html
done

for DIR in . $(ls -l | grep '^d' | awk '{print $NF}'); do
    pushd ${DIR}
    ls -phl --time-style='+%d-%b-%Y %H:%M' | awk 'NR>1' | grep -v -w 'index.html' | grep -v -w 'push.sh' | sort -k1,1 -k8,8 | awk 'BEGIN{print "<html><head><title>Index of /</title></head><body><pre>"} {printf("<a href=\"%s\">%s</a>\t%s %s %s\n", $8, $8, $6, $7, $5)} END{print "</pre></body></html>"}' >index.html
    cat ${AUTOINDEX_HTML} >>index.html
	git add index.html
    popd
done

git commit -m "[skip ci] build new index" -s || true

git push origin master

