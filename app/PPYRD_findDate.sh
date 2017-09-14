#!/bin/bash
logfile=/data/log.txt

# working directory to look for scans
cd /data

	shopt -s nullglob
	for f in ddatum*.pdf
	do
		php /app/ppyrd.php "$f"
	done

