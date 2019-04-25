#!/usr/bin/env python
# https://gist.github.com/4404510
"""
adev()
{
    export ANDROID_SERIAL=$(/usr/bin/env python ~/adev.py $1)
    if [ -z "$ANDROID_SERIAL" ]; then
        unset ANDROID_SERIAL
    fi
}
"""

__version__ = '1.0'
__author__ = 'phus.lu@gmail.com'

import sys
import os
import re
import difflib

def list_devices():
    return [x.split()[0] for x in os.popen('adb devices').read().strip().splitlines()[1:]]

def print_devices(current_device=None):
    lines = os.popen('adb devices').read().strip().splitlines()[1:]
    for i, line in enumerate(lines, 1):
        if current_device and current_device in line:
            output = '\033[92m * %d. %s \033[0m' % (i, line)
        else:
            output = '   %d. %s' % (i, line)
        sys.stderr.write(output+'\n')

def pre_start():
    current_device = os.environ.get('ANDROID_SERIAL', '')
    try:
        if not os.popen('adb version').read().strip().startswith('Android Debug'):
            sys.stderr.write('\033[41mPlease add android SDK to PATH.\033[0m\n')
            sys.exit(-1)
        devices = list_devices()
        if not devices:
            sys.stderr.write('\033[41mNO android devices connected.\033[0m\n')
            sys.exit(-1)
    except Exception as e:
        pass
    finally:
        pass

def parse_device_from_args():
    device  = None
    devices = list_devices()
    if len(devices) == 1:
        device = devices[0]
    elif len(sys.argv) > 1:
        arg = sys.argv[1] 
        if arg in '123456789' and int(arg) <= len(devices):
            device = devices[int(sys.argv[1])-1]
        elif len(arg) >= 2:
            matches = difflib.get_close_matches(arg, devices, n=1, cutoff=0.1)
            device = matches[0] if matches else os.environ.get('ANDROID_SERIAL', '')
    return device

def main():
    pre_start()
    current_device = os.environ.get('ANDROID_SERIAL', '')
    devices = list_devices()
    device = parse_device_from_args()
    if len(devices) >= 2:
        if device:
            print_devices(device)
            sys.stdout.write(device)
        else:
            try:
                print_devices(current_device)
                sys.stderr.write('\nselect device number: ')
                n = int(raw_input(''))
                device = devices[n-1]
                sys.stderr.write('\033[92m * %s \033[0m been selected.\n' % device)
            except (IndexError, ValueError, KeyboardInterrupt):
                device = current_device
            finally:
                sys.stdout.write(device)
    else:
        print_devices(None)
        sys.stdout.write('')

if __name__ == '__main__':
    main()

