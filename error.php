<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="/style.css" />
        <link rel="shortcut icon" href="/images/favicon.ico">
        <title>Erreur <?php echo $_GET["code"] ?></title>
    </head>
    <body>
        <div id="pannel">
            <header>
                <h2>Erreur <?php echo $_GET["code"] ?></h2>
            </header>
            <section>
                <h2><?php
                    switch($_GET["code"])
                    {
                        case 403:
                            echo "Accès interdit.";
                            break;
                        case 404:
                            echo "Page introuvable.";
                            break;
                        case 500:
                            echo "Erreur interne du serveur.";
                            break;
                    }
                ?></h2>
                <a href="/">Retour à l'accueil</a>
            </section>
        </div>
        <br>
    </body>
</html>