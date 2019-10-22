yum install -y epel-release
yum install -y wget vim rsync ngrep jq htop chrony tmux lrzsz bash-completion
curl https://phuslu.github.io/sysctl.conf | tee /etc/sysctl.d/10-phuslu.conf
sed -i -e 's/SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config
sudo systemctl stop firewalld
sudo systemctl disable firewalld
echo -e "* soft nofile 1048576\n* hard nofile 1048576" | tee /etc/security/limits.d/99-phuslu.conf
curl myip.ipip.net | grep 中国 && (
	cat <<EOF | sudo tee /etc/profile.d/pip.sh
export PIP_INDEX_URL=https://pypi.tuna.tsinghua.edu.cn/simple/
export PIPENV_PYPI_MIRROR=https://pypi.tuna.tsinghua.edu.cn/simple/
EOF
	sudo chmod +x /etc/profile.d/pip.sh
)
test -d /home/centos || (
	adduser centos
	echo 'centos    ALL=(ALL)    NOPASSWD:ALL' | tee -a /etc/sudoers
	mkdir /home/centos/.ssh
	echo 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQD4vtgNW8w0ir8rA41s18hCPAa53zQa3dwgGRQf6dfsQhaXekLDefuv5tmIW7UPUgkUKq744a+AobWh5j31Mp98Sg5PbabiJQdNkEk4Jf0ln8ImbNlUlAub/RYy1CNxQfatRxSORu+zM0qG5Ul9NuOFN7QrEp8R4cmsQ4ZJOGcIa1CRz9ZR+mIZuJnk9sHpujZnUJJDjgsv5YL1NFB3cfDrToQCfHZY3qIPAwAKB+q4u+oyjEw2ZPYZ9WKei6ccaYoRZXtJyRd3Y4Xa4CTira+JcIz9M1lsdT/BFt9YaQ8IN77j0Lk2Cpvc/yh2Fs9JkqfohCY5INJbfFAvl9IbYcQz centos@adx-dbwriter-1' | tee /home/centos/.ssh/authorized_keys
	chown -R centos:centos /home/centos/.ssh
	chmod g-w /home/centos/.ssh
	chmod 0600 /home/centos/.ssh/*
)
su centos -c 'cd; curl https://phuslu.github.io/bashrc | grep ^# | tail -n +2 | cut -b3- | grep ^curl | bash -xe'
