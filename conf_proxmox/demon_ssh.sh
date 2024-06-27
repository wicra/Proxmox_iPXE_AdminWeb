
#!/bin/bash

# Chemin vers le dossier contenant les fichiers nommés avec des adresses IP
IP_DIR="client_ip"
SSH_USER="root"
SCRIPT="/var/www/html/proxmox/configenNFS_v1.sh"

# Vérifie que le dossier existe et est lisible
if [ ! -d "$IP_DIR" ]; then
    echo "Erreur: Le dossier $IP_DIR n'existe pas ou n'est pas accessible."
    exit 1
fi

while true; do
    IP_FILES=("$IP_DIR"/*)
    
    # Vérifie si le dossier est vide
    if [ -z "$(ls -A $IP_DIR)" ]; then
        echo "Le dossier est vide. Attente de 1 minute avant de réessayer..."
        sleep 60
        continue
    fi
    
    # Lire chaque fichier (chaque adresse IP) dans le dossier
    for IP_FILE in "$IP_DIR"/*; do
        # Extraire le nom du fichier qui est l'adresse IP
        SSH_IP=$(basename "$IP_FILE")

        echo "Vérification de la connexion SSH vers $SSH_USER@$SSH_IP..."

        # Tentative de connexion SSH avec gestion des options de sécurité
        if ssh -q -o BatchMode=yes -o ConnectTimeout=5 "$SSH_USER@$SSH_IP" exit; then
            echo "Connexion SSH établie avec succès vers $SSH_USER@$SSH_IP"

            # Transfert du fichier via scp et exécution du script distant via ssh
            if scp "$SCRIPT" "$SSH_USER@$SSH_IP:/$SSH_USER/" && ssh "$SSH_USER@$SSH_IP" "bash /$SSH_USER/$(basename $SCRIPT)"; then
                echo "Script exécuté avec succès sur $SSH_USER@$SSH_IP"

                # Supprime le fichier après succès
                rm -f "$IP_FILE"
            else
                echo "Échec de l'exécution du script sur $SSH_USER@$SSH_IP"
            fi
        else
            echo "Connexion SSH vers $SSH_USER@$SSH_IP échouée"
        fi
    done

    # Attendre un moment avant de vérifier à nouveau
    sleep 10
done

echo "Fin du traitement des adresses IP."