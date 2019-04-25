#!/usr/bin/env python
# coding:utf-8

import sys, os, re, time
import random

def random_word(length):
    return u''.join(random.choice(('bcdfghjklmnpqrstvwxyz','aeiou')[x&1]) for x in xrange(length))

def random_words(count, minlength, maxlength):
    return u'-'.join(random_word(random.randint(minlength,maxlength)) for x in xrange(count))

def test():
    maxcount = 10
    maxlength = 10
    lines = 10
    print 'config: word maxcount=%d, maxlength=%d, samples=%d' % (maxcount, maxlength, lines)
    urls = [u'http://%s.google.com' % random_words(random.randint(2, maxcount), 3, maxlength) for i in xrange(lines) ]
    print '\n'.join(urls)

if __name__ == '__main__':
    test()