#!/usr/bin/env python
# coding:utf-8

import sys
import os
import re
import time
import logging
import select
import errno
import subprocess

def getstatusoutput(cmd, input='', callback=None, timeout=86400*2, **subprocess_args):
    """getstatusoutput implemented by subprocess, works with gevent/eventlet. Author: @phuslu, LICENSE: public domain"""
    timeout_at = time.time() + timeout
    interval = 0.1
    bat = ''
    if os.name == 'nt' and len(cmd) >= (8000 if sys.getwindowsversion() >= (5, 1) else 2000):
        # http://support.microsoft.com/kb/830473
        try:
            bat = tempfile.mktemp(prefix='getstatusoutput_', suffix='.bat')
            with open(bat, 'wb') as fp:
                fp.write('%s\r\n' % cmd)
        except OSError as e:
            logging.exception('mktemp %r error: %r', bat, e)
            return 0x7f, str(e)
    pipe = subprocess.Popen(bat or cmd, shell=True, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, close_fds=os.name!='nt', **subprocess_args)
    if input:
        try:
            pipe.stdin.write(input)
        except IOError as e:
            if e.errno != errno.EPIPE and e.errno != errno.EINVAL:
                raise
        pipe.stdin.close()
    if timeout:
        try:
            pipe_fd = pipe.stdout.fileno()
            if os.name == 'nt':
                import msvcrt
                pipe_fd = msvcrt.get_osfhandle(pipe_fd)
            else:
                import fcntl
                fcntl.fcntl(pipe_fd, fcntl.F_SETFL, os.O_NONBLOCK | fcntl.fcntl(pipe_fd, fcntl.F_GETFL))
            output = b''
            while time.time() < timeout_at:
                if pipe.poll() is not None:
                    output += pipe.stdout.read()
                    pipe.stdin.close()
                    pipe.stdout.close()
                    return pipe.returncode, output
                else:
                    data = ''
                    if os.name == 'nt':
                        import ctypes.wintypes
                        c_avail = ctypes.wintypes.DWORD()
                        ctypes.windll.kernel32.PeekNamedPipe(pipe_fd, None, 0, None, ctypes.byref(c_avail), None)
                        if c_avail.value:
                            data = pipe.stdout.read(min(c_avail.value, 1024))
                        else:
                            time.sleep(interval)
                            interval = min(interval+0.1, 1)
                    else:
                        rlist, _, _ = select.select([pipe_fd], [], [], 1)
                        if rlist:
                            try:
                                data = pipe.stdout.read(1024)
                            except IOError as e:
                                if e[0] != errno.EAGAIN:
                                    raise
                                sys.exc_clear()
                    if callable(callback):
                        callback(data)
                    else:
                        output += data
            pipe.stdin.close()
            pipe.stdout.close()
            try:
                pipe.kill()
                pipe.wait()
            except OSError as e:
                logging.error('kill pipe=%r pid=%r failed: %r', pipe, pipe.pid, e)
            return 0x7f, 'timed out'
        except Exception as e:
            logging.exception('subporcess cmd=%r poll failed: %r', cmd, e)
            return 0x7f, str(e)
        finally:
            if bat and os.path.isfile(bat):
                try:
                    os.remove(bat)
                except OSError as e:
                    logging.error('os.remove(%r) failed: %r', bat, e)
    else:
        pipe.wait()
        return pipe.returncode, pipe.stdout.read()


if __name__ == '__main__':
    print(getstatusoutput('ver 2>/dev/null || uname -a'))

