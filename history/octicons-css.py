#!/usr/bin/env python
# coding:utf-8

import sys
import os
import re
import time
import json
import urllib2

PRIMER_OCTICONS_URL = 'https://raw.githubusercontent.com/primer/octicons/master/lib/'


def get_icon_list():
    data = urllib2.urlopen(PRIMER_OCTICONS_URL + 'data.json').read()
    info = json.loads(data)
    icon_list = info.keys()
    return icon_list


def get_icon_svg(name, minify=True):
    data = urllib2.urlopen(PRIMER_OCTICONS_URL + 'svg/%s.svg' % name).read()
    if minify:
        lines = data.splitlines()
        for line in lines:
            line = line.strip()
            if line.startswith('<svg '):
                svg_line = line
            elif line.startswith('<path '):
                data_line = line
            elif line.startswith('<polygon '):
                data_line = line
            else:
                pass
        data_line = re.sub(r'id="Shape"></.+$', 'fill="#7D94AE" />', data_line)
        data = svg_line + data_line + '</svg>'
    return data


def render(template, vars):
    return re.sub(r'(?is){{ (\w+) }}', lambda m: str(vars.get(m.group(1), '')), template)


def convert_svg_to_css(name, svg):
    TEMPLATE = '''.octicon-{{ name }} {
background-position: center left;
background-repeat: no-repeat;
background-image: linear-gradient(transparent,transparent),url("data:image/svg+xml;utf8,{{ svg }}");
padding-left: 16px;
}'''
    svg = svg.replace('"', "'")
    return render(TEMPLATE, locals())


def main():
    icon_list = get_icon_list()
    for name in icon_list:
        svg = get_icon_svg(name)
        print convert_svg_to_css(name, svg)


if __name__ == '__main__':
    main()

