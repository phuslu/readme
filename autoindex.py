#!/usr/bin/env python
# pylint: disable=too-many-statements, line-too-long, W0703

import os
import sys
import time


def _get_date_size(filename):
    """return file date and size"""
    stat_result = os.stat(filename)
    date = time.strftime('%d-%b-%Y %H:%M', time.localtime(stat_result.st_mtime))
    size = stat_result.st_size
    for unit in ['', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB']:
        if abs(size) < 1024.0:
            break
        size /= 1024.0
    return date, '{:d}{}'.format(int(size), unit)


def index():
    """generate index.html for current folder"""
    with open('autoindex.html', 'rb') as file:
        autoindex_html = file.read().decode('utf-8')
    for root, dirs, files in os.walk(u'.', topdown=True, followlinks=True):
        html = u'<meta charset="UTF-8"><title>Index of /{}</title>\n'.format(root[1:].strip('\\/'))
        if os.path.basename(root).startswith(('.', '@')):
            continue
        for name in sorted(dirs):
            if name.startswith(('.', '@')):
                continue
            html += u'<a href="{0}/">{0}/</a> {1} {2}\n'.format(name, *_get_date_size(os.path.join(root, name)))
        for name in sorted(files):
            if name.startswith('.') or name in ('index.html', 'autoindex.py'):
                continue
            html += u'<a href="{0}">{0}</a> {1} {2}\n'.format(name, *_get_date_size(os.path.join(root, name)))
        html += autoindex_html
        with open(os.path.join(root, 'index.html'), 'wb') as file:
            file.write(html.encode('utf-8'))


def push():
    """main function"""
    index()
    os.system('&&'.join([
        'git add -A .',
        'git commit -m "[skip ci] build new index" -s -a',
        'git push origin master',
    ]))


if __name__ == '__main__':
    globals()[sys.argv[1]]()
