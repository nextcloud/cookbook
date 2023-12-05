#!/bin/bash

cd "$1"

ret=0

while IFS= read -d '' file
do
	php -l "$file" > /tmp/msg
	retSingle=$?

	if [ $retSingle -eq 0 ]
	then
		cat /tmp/msg | sed 's@^@::debug::@'
	else
		ret=1
		msg="$(cat /tmp/msg)"
		echo "$msg" | sed 's@^\s*$@@' | grep -v '^$' > /tmp/msg
		line=$(cat /tmp/msg | grep -o ' on line [0-9]*' | sed -E 's@ on line ([0-9]*)$@\1@')
		prefix="::error file=$file,line=$line,title=PHP linter failed::"
		sed "s@^@$prefix@" /tmp/msg 
	fi
done < <(find lib -type f -name '*.php' -print0)

exit $ret
