<?php

return array
(
    // Titre du serveur
    "TITLE" => "Minecrew",

    // Le répertoire contenant le serveur Minecraft
    "LOCATION" => "server/",

    // Lien vers un launcher personnalisé (laisser vide pour aucun)
    "LAUNCHER" => "download/launcher.zip",

    // Lien vers un dossier de sauvegardes (laisser vide pour aucun)
    "BACKUP" => "backups/",

    // L'adresse du serveur Minecraft
    "HOSTNAME" => "monserveur.com",

    // Le port du serveur (par défaut : 25565)
    "PORT" => "25565",

    // Affiche ou non les statistiques inutilisées (que personne n'a atteintes)
    "UNUSED_STATS" => false,

    // Liste des joueurs à ne pas afficher
    "HIDDEN_PLAYERS" => array
    (
        "test",
        "banned",
    )
)

?>