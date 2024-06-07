#!/bin/bash

# Variables de connexion SSH
HOST="10.10.62.10"
USER="nom_utilisateur"
PRIVATE_KEY="/chemin/vers/votre/clé_privée"  # Par exemple: /home/user/.ssh/id_rsa

# Chemin du fichier sur la machine distante
REMOTE_FILE="/chemin/vers/le/fichier_sur_la_machine_distante"

# Ligne à rechercher et changer dans le fichier
SEARCH_STRING="texte_de_la_ligne_a_changer"
NEW_LINE="nouvelle_ligne_modifiée"

# Connexion SSH et modification de la ligne dans le fichier
ssh -i "$PRIVATE_KEY" "$USER@$HOST" "sed -i 's/$SEARCH_STRING/$NEW_LINE/' $REMOTE_FILE"
