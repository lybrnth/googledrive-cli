#! /bin/bash
composer install
chmod +x "`pwd`/googledrive"
ln -s "`pwd`/googledrive" "/usr/local/bin/googledrive"
