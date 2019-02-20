# docker run --tmpfs /tmp --tmpfs /run -v /sys/fs/cgroup:/sys/fs/cgroup:ro -v /data:/data --privileged --rm -it ubuntu:14.04 bash -c 'cat </dev/console >/dev/null 2>&1 & exec /sbin/init'
# A VM like docker container based on alpine
# see https://hub.docker.com/r/phuslu/alpine/

FROM alpine:3.6
RUN \
  sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories && \
  apk update && \
  apk upgrade && \
  apk add --update --no-cache \
    bash \
    curl \
    dcron \
    dropbear \
    iproute2 \
    logrotate \
    openrc \
    openssh-client \
    openssh-sftp-server \
    openssl \
    procps \
    rsyslog \
    tzdata \
    xz && \
  rm -rf /var/cache/apk/* && \
  # add lastlog
  touch /var/log/lastlog && \
  # fake glibc for go binaries
  mkdir /lib64 && \
  ln -s /lib/libc.musl-x86_64.so.1 /lib64/ld-linux-x86-64.so.2 && \
  # add /etc/init.d/timezone
  echo $'#!/sbin/openrc-run\n\
description="Sets the timezone of of the machine"\n\
start()\n\
{\n\
	if test -n "$TZ" ; then\n\
		ln -sf /usr/share/zoneinfo/$TZ /etc/localtime\n\
		echo "$TZ" >  /etc/timezone\n\
	fi\n\
	return 0\n\
}\n'\
> /etc/init.d/timezone && \
  chmod +x /etc/init.d/timezone && \
  # hack openrc for docker
  sed -i 's/#rc_sys=""/rc_sys="docker"/g' /etc/rc.conf && \
  sed -i 's/^#\(rc_logger="YES"\)$/\1/' /etc/rc.conf && \
  echo 'rc_provide="loopback net"' >> /etc/rc.conf && \
  echo 'rc_env_allow="*"' >>/etc/rc.conf && \
  sed -i '/tty/d' /etc/inittab && \
  echo 'null::respawn:/usr/bin/tail -f /dev/null' >> /etc/inittab && \
  sed -i 's/hostname $opts/# hostname $opts/g' /etc/init.d/hostname && \
  sed -i 's/mount -t tmpfs/# mount -t tmpfs/g' /lib/rc/sh/init.sh && \
  sed -i 's/cgroup_add_service /# cgroup_add_service /g' /lib/rc/sh/openrc-run.sh && \
  rm -f /etc/init.d/hwclock \
        /etc/init.d/hwdrivers \
        /etc/init.d/modules \
        /etc/init.d/modules-load \
        /etc/init.d/modloop && \
  # add auto-start services
  rc-update add timezone default && \
  rc-update add rsyslog default && \
  rc-update add dcron default && \
  rc-update add dropbear default && \
  # root user settings
  sed -i 's#root:/bin/ash#root:/bin/bash#' /etc/passwd && \
  curl -f https://phuslu.github.io/bashrc >/root/.bash_profile && \
  curl -f https://raw.githubusercontent.com/rupa/z/master/z.sh >/root/.z.sh && \
  # set root password for ssh
  echo root:toor | chpasswd

CMD ["init"]
