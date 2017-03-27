<?php
    $config = require "config.inc";
    error_reporting(E_ERROR | E_PARSE);

    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 5, "usec" => 0));
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 5, "usec" => 0));

    echo "<h2>Le serveur est ";
    if(socket_connect($socket, $config["HOSTNAME"], $config["PORT"]) === false)
    {
        echo "<span class='red'>fermé</span>.</h2><br>";
    }
    else
    {
        echo "<span class='green'>ouvert</span>.</h2><br>";

        $handshake = pack("ccca*nccc", 0, 107, strlen($config["HOSTNAME"]), $config["HOSTNAME"], $config["PORT"], 1, 1, 0);
        $handshake = chr(strlen($handshake) - 2).$handshake;

        socket_write($socket, $handshake);

        while(ord(socket_read($socket, 1)) != 0);

        for($i = 0, $size = 0; ($byte = ord(socket_read($socket, 1))) >= 128; $i++)
            $size = ($byte - 128) * pow(128, $i);

        $size += $byte * pow(128, $i);

        socket_recv($socket, $response, $size, MSG_WAITALL);

        $info = json_decode($response);

        echo "Message : <i>".$info->{"description"}->{"text"}."</i><br>";

        $playerlist = "";
        if($info->{"players"}->{"online"} > 0)
        {
            foreach($info->{"players"}->{"sample"} as $name)
                $playerlist .= "<b>".$name->{"name"}."</b> <img src='https://crafatar.com/avatars/".$name->{"name"}."?size=16&default=MHF_Steve&overlay'> | ";

            echo "<b>".$info->{"players"}->{"online"}."</b> joueurs connectés :<br>".substr($playerlist, 0, -3)."<br>";
        }
        else
            echo "Aucun joueur connecté.<br><br>";
        
        echo "<img src='".$info->{"favicon"}."'><br>";
        
        $uptime = file_get_contents($config["LOCATION"]."/stamp");
        echo "<span class='hidden' id='stamp'>$uptime</span><br>";
        echo "<span id='uptime'>Uptime : <img src='images/loader.gif'></span>";
    }
    socket_close($socket);
?>