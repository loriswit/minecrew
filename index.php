<!DOCTYPE html>
<html>
    <head>
        <?php
            if(!isset($_GET["init"]))
                header('Location: ?init#/-163/64/1345/-1/0/0');
        ?>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <title>Minecrew Server</title>
        <link rel="shortcut icon" href="images/favicon.ico">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="generator" content="Minecraft-Overviewer 0.12.82 (6843033)" />

        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>

        <script type="text/javascript" src="map/underscore.js"></script>
        <script type="text/javascript" src="map/backbone.js"></script>
        <script type="text/javascript" src="map/overviewerConfig.js"></script>
        <script type="text/javascript" src="map/overviewer.js"></script>
        <script type="text/javascript" src="map/baseMarkers.js"></script>
    </head>
    <body onload="overviewer.util.initialize()">
        <div id="pannel">
            <header>
                <h2>Minecrew</h2>
            </header>
            <section>
                <?php
                    error_reporting(E_ERROR | E_PARSE);
                    
                    function test_server($hostname, $info)
                    {
                        echo "<h2>Le serveur ";
                        $service_port = 25565;
                        $address = gethostbyname($hostname);
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 5, "usec" => 0)); 
                        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0)); 
                        if(socket_connect($socket, $address, $service_port) === false)
                        {
                            echo "est <span class=\"red\">fermé</span>.</h2><br>";
                        }
                        else
                        {
                            echo "est <span class=\"green\">ouvert</span>.</h2><br>";

                            socket_send($socket, "\xFE", 1, 0);
                            $length = socket_recv($socket, $data, 512, 0);
                            $pieces = explode("\x00\xA7", substr($data, 1));
                            echo "Nom : <strong>".$pieces[0]."</strong><br>Joueurs connectés : <strong>"
                                 .$pieces[1]."</strong> / <strong>".$pieces[2]."</strong><br>";
                        }
                        socket_close($socket);
                    }
                    
                    test_server("olybri.ddns.net", "");
                ?>
                <br>
                <form>
                    Adresse du serveur : 
                    <input type="text" name="address" value="olybri.ddns.net" readonly><br>
                </form>
                <br>
                Logs : <a href="server/logs/latest.log">latest.log</a><br>
                Backups : <a href="https://drive.google.com/folderview?id=0B9TlCoTkawLrUFNwS3NlcG9VWTQ&usp=sharing#list">snapshots</a><br>
                Launcher : <a href="https://dl.dropboxusercontent.com/u/109130039/Shared/Shiginima%20Launcher%20SE%20v3.000.zip">télécharger</a><br>
                <h2><a href="players.php">Comparer les joueurs</a></h2>
                <br>
            </section>
            <div id="attribution">
                Retrouver le <a target="_blank" href="https://github.com/Olybri/Minecrew">projet sur GitHub</a> | 
                Rendu de la carte : <a target="_blank" href="https://overviewer.org/">The Overviewer</a>
            </div>
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