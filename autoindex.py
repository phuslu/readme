#!/usr/bin/env python
# pylint: disable=too-many-statements, line-too-long, W0703

__version__ = '1.0'

import os
import sys
import time


def index():
    """generate index.html for current folder"""
    getdate = lambda x: time.strftime('%d-%b-%Y %H:%M', time.localtime(os.path.getmtime(x)))
    with open('autoindex.html', 'rb') as file:
        autoindex_html = file.read().decode()
    for root, dirs, files in os.walk(".", topdown=True):
        html = '<title>Index of /{}</title>\n'.format(root[1:].strip('\\/'))
        for name in dirs:
            if name.startswith(('.', '@')):
                continue
            html += '<a href="{0}/">{0}/</a> {1} 0\n'.format(name, getdate(os.path.join(root, name)))
        for name in files:
            if name in ('index.html', 'autoindex.py'):
                continue
            fullname = os.path.join(root, name)
            html += '<a href="{0}">{0}</a> {1} {2}\n'.format(name, getdate(fullname), os.path.getsize(fullname))
        html += autoindex_html
        with open(os.path.join(root, 'index.html'), 'wb') as file:
            file.write(html.encode())

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
