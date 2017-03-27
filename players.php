<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <title>Comparaison des joueurs</title>
        <link rel="shortcut icon" href="images/favicon.ico">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script>
            function printStats(url)
            {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function()
                {
                    if (xhttp.readyState == 4 && xhttp.status == 200)
                        document.getElementById("stats").innerHTML = xhttp.responseText;
                };
                xhttp.open("GET", url, true);
                xhttp.send();
            }
        </script>
    </head>    
    <body onload="printStats('stats.php')">
        <div id="pannel_player">
            <header>
                <h2>Comparaison des joueurs</h2>
                <h5>Joueurs ayant joué plus d'une heure</h5>
                <a href="/">Retour</a>
                <br>
                <br>
            </header>
            <section id='stats'>
                Chargement <img src="images/loader.gif">
            </section>
            <?php
                include "footer.inc";
                printFooter("Fournisseur d'avatars", "Crafatar", "https://crafatar.com");
            ?>
        </div>
        <br>
    </body>
</html>
