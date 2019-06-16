#!/bin/bash

path=$(pwd -P)/$(basename $0)
grep -q $path /etc/pam.d/sshd || patch -d / -p 0 <<EOF
--- /etc/pam.d/sshd	2017-02-03 11:32:40.931916700 +0800
+++ /etc/pam.d/sshd	2017-02-03 11:34:28.584575056 +0800
@@ -51,5 +51,6 @@ session    required     pam_env.so user_
 # to run in the user's context should be run after this.
 session [success=ok ignore=ignore module_unknown=ignore default=bad]        pam_selinux.so open
 
+session    optional     pam_exec.so $path
 # Standard Un*x password updating.
 @include common-password
EOF

grep -q "GatewayPorts yes" /etc/ssh/sshd_config || echo "GatewayPorts yes" >> /etc/ssh/sshd_config

if [ "${PAM_USER}" = "rdp" ]; then
    pkill -u "${PAM_USER}"
fi

exit 0
