#!/bin/bash

set -ex

ROOT="$(cd "$(dirname "$0")"; pwd -P)"

function index() {
	(
		echo '<title>Index of /</title>'
		find -L . -maxdepth 1 -type d -not -path '*/\.*' -not -path . -not -name '@*' -printf '<a href="%f/">%f/</a> %Td-%Tb-%TY %TH:%TM %s\n'
		find -L . -maxdepth 1 -type f -not -name index.html -printf '<a href="%f">%f</a> %Td-%Tb-%TY %TH:%TM %s\n'
		cat ${ROOT}/autoindex.html
	) >index.html
}

function gen() {
	find . -type d -not -path '*/\.*' -exec bash -c "cd {} && $ROOT/$0 index" \;
}

function push() {
	bash $0 gen
	find . -type d -not -path '*/\.*' -exec bash -c "git add {}/index.html" \;
	git commit -m "[skip ci] build new index" -s -a || true
	git push origin master
}

$1
