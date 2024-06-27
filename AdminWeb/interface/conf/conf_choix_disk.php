<?php
    /////////////////////////////////////////////////////////
    //         SCRIP  CHOIX DISK MACHINE (PAS FINI)        //
    /////////////////////////////////////////////////////////

    // Récupérer les valeurs du formulaire
    $host_name = $_POST['host_name'];
    $ip_address = $_POST['ip_address'];
    $choice = $_POST['choice'];

    // Variables pour le script shell
    $REMOTE_HOST = $ip_address;
    $REMOTE_USER = "root"; // Remplacez par votre utilisateur SSH
    $VMID = "205"; // ID de la machine virtuelle dans Proxmox
    $IP_STOCKAGE = "10.10.62.220";

    // Échapper les caractères spéciaux dans $choice pour éviter les injections
    $escaped_choice = escapeshellarg($choice);

    // Générer le script shell dynamiquement
    $script_content = <<<SCRIPT
    #!/bin/bash

    # Connexion SSH et commande distante
    ssh $REMOTE_USER@$REMOTE_HOST << EOF
        set -e  # Arrêter le script en cas d'erreur

        # Arrêter la machine virtuelle
        sudo qm stop $VMID

        # Supprimer le disque SCSI existant
        sudo qm set $VMID --delete scsi0

        # Monter le stockage distant
        sudo mount $IP_STOCKAGE:/images /mnt/stockage

        # Importer et attacher le nouveau disque
        sudo qm disk import $VMID /mnt/stockage/$escaped_choice local-lvm --format raw
        sudo qm set $VMID --scsi0 local-lvm:vm-$VMID-disk-0

        # Optionnel : configurer l'ordre de démarrage (scsi0 pour le nouveau disque)
        sudo qm set $VMID -boot order='scsi0;net0'

        # Démonter le stockage distant
        sudo umount /mnt/stockage

        # Redémarrer la machine virtuelle
        sudo qm start $VMID

        echo "Remplacement du disque pour la machine virtuelle $VMID terminé."
    EOF

    SCRIPT;

    // Écrire le contenu du script dans un fichier temporaire
    $script_file = tempnam(sys_get_temp_dir(), 'vm_script_');
    file_put_contents($script_file, $script_content);

    // Exécuter le script shell via SSH
    $command = "ssh $REMOTE_USER@$REMOTE_HOST 'bash -s' < $script_file";
    $output = shell_exec($command);

    // Supprimer le fichier temporaire du script
    unlink($script_file);

    // Afficher le résultat de l'exécution
    echo "<pre>$output</pre>";


?>