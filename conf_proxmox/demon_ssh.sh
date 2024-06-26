#!/bin/bash

# Chemin vers le fichier contenant les adresses IP
IP_FILE="client_ip.txt"
SSH_USER="root"
SCRIPT="/var/www/html/proxmox/configenNFS_v1.sh"

# Vérifie que le fichier IP existe et est lisible
if [ ! -f "$IP_FILE" ]; then
    echo "Erreur: Le fichier $IP_FILE n'existe pas ou n'est pas accessible."
    exit 1
fi

while true; do
    # Lire chaque ligne (chaque adresse IP) du fichier
    while IFS= read -r SSH_IP || [ -n "$SSH_IP" ]; do
        echo "Vérification de la connexion SSH vers $SSH_USER@$SSH_IP..."

        # Tentative de connexion SSH avec gestion des options de sécurité
        if ssh -q -o BatchMode=yes -o ConnectTimeout=5 "$SSH_USER@$SSH_IP" exit; then
            echo "Connexion SSH établie avec succès vers $SSH_USER@$SSH_IP"

            # Transfert du fichier via scp et exécution du script distant via ssh
            if scp "$SCRIPT" "$SSH_USER@$SSH_IP:/$SSH_USER/" && ssh "$SSH_USER@$SSH_IP" "bash /$SSH_USER/$(basename $SCRIPT)"; then
                echo "Script exécuté avec succès sur $SSH_USER@$SSH_IP"

                # Supprime l'IP du fichier après succès
                sed -i "/$SSH_IP/d" "$IP_FILE"
            else
                echo "Échec de l'exécution du script sur $SSH_USER@$SSH_IP"
            fi
        else
            echo "Connexion SSH vers $SSH_USER@$SSH_IP échouée"
        fi

    done < "$IP_FILE"
done

echo "Fin du traitement des adresses IP."
