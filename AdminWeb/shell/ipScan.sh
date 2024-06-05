#!/bin/bash
sudo nmap -Pn 10.10.62.10-150 | grep "Nmap scan report" | sed 's/Nmap scan report for //g' > /var/www/html/AdminWeb/shell/ipScan.txt