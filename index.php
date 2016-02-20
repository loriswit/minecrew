<!DOCTYPE html>
<html>
    <?php require "config.inc"; ?>
    <head>
        <?php
            if(!isset($_GET["init"]))
                header("Location: ?init#/-163/64/1345/-1/0/0");
        ?>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <title><?php echo TITLE; ?></title>
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
    <body onload="overviewer.util.initialize()">
        <div id="pannel">
            <header>
                <h2><?php echo TITLE; ?></h2>
            </header>
            <section>
                <?php
                    error_reporting(E_ERROR | E_PARSE);

                    $address = gethostbyname(HOSTNAME);

                    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 5, "usec" => 0)); 
                    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0)); 

                    echo "<h2>Le serveur est ";
                    if(socket_connect($socket, $address, PORT) === false)
                    {
                        echo "<span class=\"red\">fermé</span>.</h2><br>";
                    }
                    else
                    {
                        echo "<span class=\"green\">ouvert</span>.</h2><br>";

                        socket_send($socket, "\xFE", 1, 0);
                        socket_recv($socket, $data, 512, 0);
                        $pieces = explode("\x00\xA7", substr($data, 1));
                        echo "Message : <b>$pieces[0]</b><br>"
                            ."Joueurs connectés : <b>$pieces[1]</b> / <b>$pieces[2]</b><br>";
                    }
                    socket_close($socket);

                    echo "<br><form>"
                            ."Adresse du serveur : "
                            ."<input type=\"text\" name=\"address\" value=".HOSTNAME." readonly><br>"
                        ."</form><br>\n";

                    echo "Logs : <a href=\"".LOCATION
                         .(substr(LOCATION, -1) == "/" ? "" : "/")
                         ."logs/latest.log\">latest.log</a><br>\n";

                    if(!empty(BACKUP))
                        echo "Backups : <a href=\"".BACKUP."\">snapshots</a><br>\n";

                    if(!empty(LAUNCHER))
                        echo "Launcher : <a href=\"".LAUNCHER."\">télécharger</a><br>\n";
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