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
    <body onload="overviewer.util.initialize()">
        <div id="pannel">
            <header>
                <h2><?php echo $config["TITLE"]; ?></h2>
            </header>
            <section>
                <?php
                    error_reporting(E_ERROR | E_PARSE);

                    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 5, "usec" => 0));
                    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0));

                    echo "<h2>Le serveur est ";
                    if(socket_connect($socket, $config["HOSTNAME"], $config["PORT"]) === false)
                    {
                        echo "<span class=\"red\">fermé</span>.</h2><br>";
                    }
                    else
                    {
                        echo "<span class=\"green\">ouvert</span>.</h2><br>";

                        $handshake = pack("ccca*nccc", 0, 47, strlen($config["HOSTNAME"]), $config["HOSTNAME"], $config["PORT"], 1, 1, 0);
                        $handshake = chr(strlen($handshake) - 2).$handshake;

                        socket_write($socket, $handshake);

                        while(ord(socket_read($socket, 1)) != 0);

                        for($i = 0, $size = 0; ($byte = ord(socket_read($socket, 1))) >= 128; $i++)
                            $size = ($byte - 128) * pow(256, $i);

                        $size += $byte * pow(256, $i);

                        socket_recv($socket, $response, $size, 0);
                        if(substr(response, -2) != "\"}")
                            $response .= "\"}";

                        $info = json_decode($response);

                        echo "Message : <i>".$info->{"description"}."</i><br>";

                        if($info->{"players"}->{"online"} > 0)
                        {
                            foreach($info->{"players"}->{"sample"} as $name)
                                $playerlist .= "<b>".$name->{"name"}."</b> <img src=\"https://crafatar.com/avatars/".$name->{"name"}."?size=16&default=MHF_Steve&overlay\"> | ";

                            echo "<b>".$info->{"players"}->{"online"}."</b> joueurs connectés :<br>".substr($playerlist, 0, -3)."<br>";
                        }
                        else
                            echo "Aucun joueur connecté.<br>";
                    }
                    socket_close($socket);

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