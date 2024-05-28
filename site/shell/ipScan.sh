#!/bin/bash
sudo nmap -Pn 10.10.62.160-209 | grep "Nmap scan report" | sed "s/Nmap scan report for \/\/g" > ipScan.txt

