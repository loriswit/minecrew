<!DOCTYPE html>
<?php $config = require "config.inc"; ?>
<html>
    <head>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
        <title>Comparaison des joueurs</title>
        <link rel="shortcut icon" href="images/favicon.ico">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>    
    <body>
        <div id="pannel_player">
            <header>
                <h2>Comparaison des joueurs</h2>
                <a href="/">Retour</a>
                <br>
                <br>
            </header>
            <section>
                <?php
                    // HELPERS
                
                    function makeUrl($stat, $sort = "", $name = "")
                    {
                        return "\"".filter_input(INPUT_SERVER, "PHP_SELF")."?stat=$stat"
                               .(empty($sort) ? "" : "&sort=$sort")
                               .(empty($name) ? "" : "&name=$name")."\"";
                    }
                    
                    function formatValue($value, $type)
                    {
                        if($value <= 0)
                            return "—";

                        switch($type)
                        {
                            case "km":
                                return $value > 1000000 ?
                                    number_format($value / 100000, 1, ",", "'")." $type" :
                                    number_format($value / 100, 0, ",", "'")." m";
                                    
                            case "h":
                                return ($value > 72000 ? 
                                    number_format(floor($value / 72000), 0, ",", "'")." $type " : "")
                                    .number_format(($value / 1200) % 60, 0, ",", "'")." min";
                                    
                            case "♥":
                                return number_format($value / 2, $value % 2 ? 1 : 0, ",", "'")." $type";
                                
                            default:
                                return number_format($value, 0, ",", "'")." $type";
                        }
                    }

                    // GET CURRENT CATEGORY

                    $statCat = isset($_GET["stat"]) ?
                        $_GET["stat"] :
                        "misc";
                        
                    $statFilename = "statlist/".$statCat.".inc";
                    
                    if(!file_exists($statFilename))
                    {
                        echo "Erreur : catégorie introuvable \"$statCat\" !";
                        exit;
                    }
                    include $statFilename;

                    // PRINT CATEGORIES
                    
                    echo "<table><tr>\n";
                    $addCategory = function($name, $title) use($statCat)
                    {
                        echo "<th".($statCat == $name ? " id=\"sorted\"><b>" : "><a href=".makeUrl($name).">")
                             .$title.($statCat == $name ? "</b>" : "</a>")."</th>\n";
                    };
                    $addCategory("misc", "Divers");
                    $addCategory("distance", "Déplacements");
                    $addCategory("interaction", "Interactions");
                    echo "<td id=\"blank\"></td>\n";
                    $addCategory("craftitem", "Objets fabriqués");
                    $addCategory("useitem", "Objets utilisés");
                    $addCategory("breakitem", "Objets épuisés");
                    echo "\n</tr><tr>\n";
                    $addCategory("killentity", "Créatures tuées");
                    $addCategory("entitykilledby", "Tué par créatures");
                    $addCategory("achievement", "Trophées");
                    echo "<td id=\"blank\"></td>\n";
                    $addCategory("craftblock", "Blocs fabriqués");
                    $addCategory("useblock", "Blocs utilisés");
                    $addCategory("mineblock", "Blocs minés");
                    
                    echo "</tr></table><br>\n";
                    
                    // FILL PLAYER STATS
                    
                    $usercacheFilename = $config["LOCATION"]."/usercache.json";
                    if(!file_exists($usercacheFilename))
                    {
                        echo "Erreur : usercache introuvable !<br>Veuillez vérifier le fichier <i>config.inc</i>.";
                        exit;
                    }
                    $usercache = json_decode(file_get_contents($usercacheFilename), true);

                    $datesFilename = $config["LOCATION"]."/dates.json";
                    if(file_exists($datesFilename))
                        $dates = json_decode(file_get_contents($datesFilename));

                    $daysSinceFirstSeen = function($playerName) use($dates)
                    {
                        return (time() - $dates->{"firstSeen"}->{$playerName}) / 86400;
                    };
                    
                    foreach($usercache as $user)
                    {
                        if(in_array($user["name"], $config["HIDDEN_PLAYERS"]))
                            continue;

                        $name = $user["name"];
                        $uuid = $user["uuid"];
                        
                        $json = file_get_contents($config["LOCATION"]."/world/stats/$uuid.json");
                        $stats = json_decode($json);

                        $prefix = isset($prefix) ?
                            $prefix : "stat";
                        
                        foreach(array_keys($statList) as $key)
                        {
                            if($key != "average")
                                $players[$name][$key] = property_exists($stats, $prefix.".".$key) ?
                                    $stats->{$prefix.".".$key} :
                                    0;

                            else
                                $players[$name]["average"] = $stats->{$prefix.".playOneMinute"} / $daysSinceFirstSeen($name);
                        }
                        
                        $total = 0;
                        foreach($players[$name] as $value)
                            if($value > 0)
                                $total += $value;
                            
                        $players[$name]["total"] = $total;

                        if($statCat != "misc")
                        {
                            if(isset($dates))
                            {
                                if(property_exists($dates->{"firstSeen"}, $name))
                                    $players[$name]["average"] = $total / $daysSinceFirstSeen($name);

                                else
                                    $players[$name]["average"] = 0;
                            }

                            $players[$name]["everyHour"] = $total / ($stats->{"stat.playOneMinute"} / 72000);
                        }
                    }

                    if(!isset($defaultKey))
                        $statList["total"] = array("Total", $statList[array_keys($statList)[0]][1]);

                    if($statCat != "misc")
                    {
                        if(isset($dates))
                            $statList["average"] = array("Moyenne par jour", $statList[array_keys($statList)[0]][1]);

                        $statList["everyHour"] = array("Chaque heure de jeu", $statList[array_keys($statList)[0]][1]);
                    }
                    else
                        $statList["average"] = array("Temps de jeu par jour", "h");

                    // SORT BY STAT

                    $sortStat = isset($_GET["sort"]) ?
                        $_GET["sort"] :
                        (isset($defaultKey) ? $defaultKey : "total");
                        
                    if(!in_array($sortStat, array_keys($statList)))
                    {
                        echo "Erreur : tri impossible, statistique introuvable \"$sortStat\" !";
                        exit;
                    }
                    
                    uasort($players, function($a, $b) use($sortStat)
                    {
                        return $b[$sortStat] - $a[$sortStat];
                    });
                    
                    // SORT BY PLAYER

                    if(isset($defaultKey))
                        $sortPlayer = "";
                        
                    else
                    {

                        $sortPlayer = isset($_GET["name"]) ?
                            $_GET["name"] :
                            array_keys($players)[0];
                            
                        if(!in_array($sortPlayer, array_keys($players)))
                        {
                            echo "Erreur : tri impossible, joueur introuvable \"$sortPlayer\" !";
                            exit;
                        }
                        
                        uksort($statList, function($a, $b) use($players, $sortPlayer)
                        {
                            return $players[$sortPlayer][$b] - $players[$sortPlayer][$a];
                        });

                        $statList = array("total" => $statList["total"])
                                  + array("average" => $statList["average"])
                                  + array("everyHour" => $statList["everyHour"])
                                  + $statList;
                    }
                    
                    // PRINT TABLE
                    
                    echo "<table>";
                        
                    echo "<tr><td id=\"corner\">Statistique :</td>\n";

                    $rank = 1;
                    foreach(array_keys($players) as $name)
                    {
                        $sorted = $name == $sortPlayer;
                        echo "<th ".($sorted ? " class=\"sorted\"><b>" : ">")
                             ."$rank. "
                             .($sorted || isset($defaultKey) ? "" : "<a href=".makeUrl($statCat, $sortStat, $name).">")
                             ."$name <img src=\"https://crafatar.com/avatars/$name?size=16&default=MHF_Steve&overlay\">"
                             .($sorted || isset($defaultKey) ? "</b>" : "</a>")
                             ."</td>";
                        $rank++;
                    }
                    echo "</tr>\n";

                    $unlisted = "<b>Non-listés :</b> ";
                    
                    foreach($statList as $key => $values)
                    {
                        if(!$config["UNUSED_STATS"] && !in_array($key , array("average", "everyHour")) && array_sum(array_column($players, $key)) <= 0)
                        {
                            $unlisted .= $values[0]." | ";
                            continue;
                        }
                            
                        echo "<tr>";
                        
                        $sorted = $key == $sortStat;
                        echo "<th".($sorted ?
                             " class=\"sorted\"><b>" :
                             "><a href=".makeUrl($statCat, $key, $sortPlayer).">")
                             .$values[0].($sorted ? "</b>" : "</a>")."</td>";
                        
                        foreach($players as $name => $player)
                        {
                            $sorted = $name == $sortPlayer || $key == $sortStat;
                            echo "<td".($sorted ? " id=\"sorted\">" : ">")
                                 .formatValue($player[$key], $statList[$key][1])."</td>";
                        }
                        
                        echo "</tr>\n";

                        if($key == "everyHour")
                            echo "<tr><td id=\"blank\"></td></tr>\n";
                    }
                    
                    echo "</table><br>".substr($unlisted, 0, -3);

                ?>
            </section>
            <?php
                include "footer.inc";
                printFooter("Fournisseur d'avatars", "Crafatar", "https://crafatar.com");
            ?>
        </div>
        <br>
    </body>
</html>
