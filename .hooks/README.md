# Usage of git hooks for development

This folder holds some default hooks, you can use while developing.

Before installing make sure, you have no existing hooks installed that might get overwritten.

To install them, you can (under Linux) do from the root folder of the repository
```
ln -sr .hook/* .git/hooks
```

## Security considerations
Please be aware, that this is sort of a security risk to do this. When checking out foreign code (aka `checkout` a branch you want to inspect that is from a foreign source), the foreign user might have introduced arbirtrary malicious code into the hook scripts. This code can then be executed depending on your local settings and command line options. Be aware that this might destroy your data!

One option is to copy instead of link the hooks in the folder. That way, you can be sure the hooks are safe (as far as you trust them at the time of copying). The downside is that the hooks will never be updated automatically once you configure it this way.
```
cp .hook/* .git/hooks
```

When you need to work with untrusted code (for inspection), you could also remove the links in the `.git/hooks` folder. That way you disabled the hooks. After being sure, the hooks are clear of malicious code, you can reenable them.

## Windows users

You will most likly have to copy the hook scripts as indicated in the security section. As at the time of writing this documentation no windows machine was available, this needs to be added later.
