package main

import (
	"flag"
	"io"
	"net"
	"os/exec"
	"sync"

	"github.com/phuslu/glog"
)

const (
	BUFSZ = 256 * 1024
)

var (
	bufpool = sync.Pool{
		New: func() interface{} {
			return make([]byte, BUFSZ)
		},
	}
)

func forward(lconn net.Conn, raddr string) {
	if err := exec.Command("wakeonlan", "18:66:DA:17:A2:95").Run(); err != nil {
		glog.Warningf("exec wakeonlan error: %+v", err)
	}

	glog.Infof("try connect to %+v for %+v forwarding", raddr, lconn.RemoteAddr())
	rconn, err := net.Dial("tcp", raddr)
	if err != nil {
		glog.Errorf("net.Dial(%#v) error: %v", raddr, err)
		return
	}

	glog.Infof("forward %+v to %+v", lconn.RemoteAddr(), rconn.RemoteAddr())
	go func() {
		buf := bufpool.Get().([]byte)
		defer bufpool.Put(buf)
		defer rconn.Close()
		defer lconn.Close()
		io.CopyBuffer(rconn, lconn, buf)
	}()
	go func() {
		buf := bufpool.Get().([]byte)
		defer bufpool.Put(buf)
		defer lconn.Close()
		defer rconn.Close()
		io.CopyBuffer(lconn, rconn, buf)
	}()
}

func main() {
	var laddr, raddr string
	flag.StringVar(&laddr, "laddr", "0.0.0.0:13389", "local address")
	flag.StringVar(&raddr, "raddr", "192.168.1.3:3389", "remote address")

	flag.Parse()

	ln, err := net.Listen("tcp", laddr)
	if err != nil {
		glog.Fatalf("net.Listen(%+v) error: %+v", laddr, err)
		return
	}
	defer ln.Close()

	glog.Infof("Serving on %+v", ln.Addr())
	for {
		conn, err := ln.Accept()
		if err != nil {
			glog.Fatalf("%+v Accept error: %+v", ln.Addr(), err)
		}
		go forward(conn, raddr)
	}
}

