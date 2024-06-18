#!/bin/bash
sudo ls /images/*.raw | sed 's|.*/||' > /var/www/html/AdminWeb/shell/diskScan.txt