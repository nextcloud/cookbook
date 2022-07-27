#! /bin/bash -e

cat << EOF
The run-locally.sh script was replaced by the run-locally.py python script.
The latter is simpler to read and to structure.

Also the implementation was adopted to the documentation.

Please migrate your scripts.
EOF

sleep 2

cd "$(dirname "$0")"

if [ -d venv ]; then
	. venv/bin/activate
fi

./run-locally.py "$@"
ret=$?

echo "Do not forget to migrate your scripts to the python version"

exit $ret
