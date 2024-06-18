#!/bin/bash
# Deplacer le nouveau disk vers repertroir images
sudo mv /var/www/html/AdminWeb/upload_new_disk_tmp/* /images/
#Pas tres secure d'ajouter la possibilité de chown sur le site
sudo chown  modele:modele /image/*.raw