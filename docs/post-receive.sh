#!/bin/sh

#
# post-receive script for git deploy
#

GIT_WORK_TREE=/web/app/plymouth-webapp git checkout -f

umask 0002

while read oldrev newrev refname ; do
	echo $(date "+%Y-%m-%dT%H:%M:%S") $oldrev $newrev $refname >>/web/app/logs/plymouth-webapp.pushlog
done
