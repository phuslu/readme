#!/usr/bin/env python
# pylint: disable=too-many-statements, line-too-long, W0703

import os
import sys
import time


def _get_date(filename):
    """Readable file time"""
    return time.strftime('%d-%b-%Y %H:%M', time.localtime(os.path.getmtime(filename)))


def _get_size(filename, suffix='o'):
    """Readable file size"""
    filesize = os.path.getsize(filename)
    for unit in ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z']:
        if abs(filesize) < 1024.0:
            return "%3.1f %s%s" % (filesize, unit, suffix)
        filesize /= 1024.0
    return "%.1f%s%s" % (filesize, 'Yi', suffix)


def index():
    """generate index.html for current folder"""
    with open('autoindex.html', 'rb') as file:
        autoindex_html = file.read().decode('utf-8')
    for root, dirs, files in os.walk(u'.', topdown=True):
        html = u'<meta charset="UTF-8"><title>Index of /{}</title>\n'.format(root[1:].strip('\\/'))
        for name in dirs:
            if name.startswith(('.', '@')):
                continue
            html += u'<a href="{0}/">{0}/</a> {1} 0\n'.format(name, _get_date(os.path.join(root, name)))
        for name in files:
            if name in ('index.html', 'autoindex.py'):
                continue
            fullname = os.path.join(root, name)
            html += u'<a href="{0}">{0}</a> {1} {2}\n'.format(name, _get_date(fullname), _get_size(fullname))
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
