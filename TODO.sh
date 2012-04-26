#!/bin/bash
#
# This program was written by David Allen in order to grab all TO-DOs for a certain file type so what we can 
# easily view all notes that we want.
#
echo -n "Please enter a file type and press [ENTER]:"
read filetype
echo -n "Please enter a string to search, or press [ENTER] to search for 'TODO':"
read search
echo $search
if [[ -z "${search}" ]]; then
	find . -name "*.$filetype" -exec grep -Hin TODO {} \;
else
	find . -name "*.$filetype" -exec grep -Hin $search {} \;
fi



