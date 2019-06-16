#!/usr/bin/env python
# coding: utf-8

"""
usage: python httpsserver.py 443
"""

import sys
import ssl
import SocketServer
import BaseHTTPServer
import SimpleHTTPServer

# openssl req -new -x509 -days 365 -nodes -out cert.pem -keyout cert.pem
CERT_FILE = './cert.pem'

class ThreadingSimpleServer(SocketServer.ThreadingMixIn, BaseHTTPServer.HTTPServer):
    def get_request(self):
        conn, addr = self.socket.accept()
        sconn = ssl.wrap_socket(conn, server_side=True,
                                     certfile=CERT_FILE, keyfile=CERT_FILE,
                                     ssl_version=ssl.PROTOCOL_SSLv23)
        return (sconn, addr)

if __name__ == '__main__':
    SimpleHTTPServer.test(ServerClass=ThreadingSimpleServer)
