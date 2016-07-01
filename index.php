<!DOCTYPE html>
<?php $config = require "config.inc"; ?>
<html>
    <head>
        <?php
            if(!isset($_GET["init"]))
                header("Location: ?init#/-163/64/1345/-1/0/0");
        ?>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <title><?php echo $config["TITLE"]; ?></title>
        <link rel="shortcut icon" href="images/favicon.ico">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="generator" content="Minecraft-Overviewer 0.12.82 (6843033)" />

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>

        <script type="text/javascript" src="extern/underscore.js"></script>
        <script type="text/javascript" src="extern/backbone.js"></script>
        <script type="text/javascript" src="extern/overviewerConfig.js"></script>
        <script type="text/javascript" src="extern/overviewer.js"></script>
        <script type="text/javascript" src="extern/baseMarkers.js"></script>
    </head>
    <body onload="init()">
        <div id="pannel">
            <header>
                <h2><?php echo $config["TITLE"]; ?></h2>
            </header>
            <section>
                <div id='status'>
                    <h2>Le serveur est <span class="red"><img src="images/loader.gif"> </span></h2><br>
                </div>
                <script>
                    function init()
                    {
                        overviewer.util.initialize();
                        var xhttp = new XMLHttpRequest();
                        xhttp.onreadystatechange = function()
                        {
                            if (xhttp.readyState == 4 && xhttp.status == 200)
                                document.getElementById("status").innerHTML = xhttp.responseText;
                        };
                        xhttp.open("GET", "connect.php", true);
                        xhttp.send();
                    }
                </script>
                <?php
                    echo "<br><form>"
                            ."Adresse du serveur : "
                            ."<input type=\"text\" name=\"address\" value=".$config["HOSTNAME"]." readonly><br>"
                        ."</form><br>\n";

                    echo "Logs : <a href=\"".$config["LOCATION"]
                         .(substr($config["LOCATION"], -1) == "/" ? "" : "/")
                         ."logs/latest.log\">latest.log</a><br>\n";

                    if(!empty($config["BACKUP"]))
                        echo "Backups : <a href=\"".$config["BACKUP"]."\">snapshots</a><br>\n";

                    if(!empty($config["LAUNCHER"]))
                        echo "Launcher : <a href=\"".$config["LAUNCHER"]."\">télécharger</a><br>\n";
                ?>
                <h2><a href="players.php">Comparer les joueurs</a></h2>
                <br>
            </section>
            <?php
                include "footer.inc";
                printFooter("Rendu de la carte", "The Overviewer", "https://overviewer.org/");
            ?>
        </div>
        <br>
        <div id="mcmap">
             <div id="NoJSWarning" style="color:white; background-color:black">
                 If you can see this message, there is likely a problem loading the Overviewer javascript components.
                 Check the javascript console for error messages.
             </div>
         </div>
        <br>
    </body>
</html>