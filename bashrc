
# curl https://phuslu.github.io/bashrc | grep ^# | tail -n +2 | cut -b3- | grep ^curl | bash -xe && . ~/.bashrc
# curl https://phuslu.github.io/bashrc | tee -a ~/.bashrc
# curl -fo ~/.z.sh https://raw.githubusercontent.com/rupa/z/master/z.sh
# curl -fo ~/.git-prompt.sh https://raw.githubusercontent.com/git/git/master/contrib/completion/git-prompt.sh
# curl -fo ~/.git-completion.bash https://raw.githubusercontent.com/git/git/master/contrib/completion/git-completion.bash
# curl -fo ~/.vimrc https://phuslu.github.io/vimrc && sudo cp ~/.vimrc /root
# curl -fo /tmp/vimcat https://raw.githubusercontent.com/vim-scripts/vimcat/master/vimcat && sudo mv /tmp/vimcat /usr/bin && sudo chmod +x /usr/bin/vimcat
# curl -L https://github.com/BurntSushi/ripgrep/releases/download/0.10.0/ripgrep-0.10.0-x86_64-unknown-linux-musl.tar.gz | sudo tar xvz -C /usr/bin/ --strip-components=1 --wildcards --no-anchored rg
# sudo env $(hash yum && echo yum || echo apt) install -y bash-completion jq htop ngrep
if [ "${HOME%/*}" = "/Users" ]; then alias ls='ls -G' ;else alias ls='ls -p --color=auto'; fi
alias ll='ls -lF'
alias rm='rm -i'
alias mv='mv -i'
alias cp='cp -i'
alias tailf='tail -F'
export LC_ALL=en_US.UTF-8
export HISTTIMEFORMAT="%Y-%m-%d %T "
export HISTCONTROL=ignoreboth
export HISTSIZE=100000
export HISTFILESIZE=2000000
export PS1='\[\e[1;32m\]\u@\h\[\e[0;33m\] \w \[\e[1;34m\]\$\[\e[0m\] '
#export PROMPT_COMMAND="history -a; history -c; history -r; $PROMPT_COMMAND"
export PATH=~/.local/bin:$GOPATH/bin:$GOROOT/bin:$PATH

if [ "${SHELL##*/}" = "bash" ]; then if [[ "xterm-256color xterm-color xterm screen rxvt cygwin" == *"$TERM"* ]]; then
    eval $(SHELL=/bin/bash $(type -p dircolors))
    bind '"\e[B": history-search-forward'
    bind '"\e[A": history-search-backward'
    set bell-style none
    set show-all-if-ambiguous on
    set completion-ignore-case on
    shopt -s checkwinsize histappend
    export PS1='\[\e]0;\h:\w\a\]\n\[\e[1;32m\]\u@\h\[\e[0;33m\] \w \[\e[0m[\D{%H:%M:%S}]\n\[\e[1;$((31+3*!$?))m\]\$\[\e[0m\] '
    if grep --version >/dev/null 2>&1 ; then alias grep='grep --color'; fi
    for f in /usr/share/bash-completion/bash_completion ~/.z.sh ~/.git-completion.bash ~/.git-prompt.sh; do if [ -f $f ]; then source $f; fi; done
    if type -p __git_ps1; then export PS1='\[\e]0;\h:\w\a\]\n\[\e[1;32m\]\u@\h\[\e[0;33m\] \w$(__git_ps1 " (%s)") \[\e[0m[\D{%H:%M:%S}]\n\[\e[1;$((31+3*!$?))m\]\$\[\e[0m\] '; fi
fi fi

