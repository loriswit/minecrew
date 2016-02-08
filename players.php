<!DOCTYPE html>
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
                        switch($type)
                        {
                            case "km":
                                return $value > 1000000 ?
                                    number_format($value / 100000, 1, ",", "'")." $type" :
                                    number_format($value / 100, 0, ",", "'")." m";
                                    
                            case "h":
                                return ($value > 72000 ? 
                                    number_format($value / 72000, 0, ",", "'")." $type " : "")
                                    .number_format(($value / 1200) % 60, 0, ",", "'")." min";
                                    
                            case "♥":
                                return number_format($value / 2, $value % 2 ? 1 : 0, ",", "'")." $type";
                                
                            default:
                                return number_format($value, 0, ",", "'")." $type";
                        }
                    }
                    
                    // GET CURRENT CATEGORY
                    
                    if(isset($_GET["stat"]))
                        $statCat = $_GET["stat"];
                    else
                        $statCat = "misc";
                        
                    $statFilename = "statlist/".$statCat.".inc";
                    
                    if(!file_exists($statFilename))
                    {
                        echo "Erreur : catégorie introuvable \"$statCat\" !";
                        exit;
                    }
                    
                    // PRINT CATEGORIES
                    
                    echo "<table><tr>\n";
                    $addCategory = function($name, $title)
                    {
                        global $statCat;
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
                    $addCategory("killentity", "Entités tuées");
                    $addCategory("entitykilledby", "Tué par entités");
                    echo "<td id=\"blank\"></td>\n"
                         ."<td id=\"blank\"></td>\n";
                    $addCategory("craftblock", "Blocs fabriqués");
                    $addCategory("useblock", "Blocs utilisés");
                    $addCategory("mineblock", "Blocs minés");
                    
                    echo "</tr></table><br>\n";
                    
                    // FILL PLAYER STATS
                    
                    include $statFilename;
                    
                    $usercache = json_decode(file_get_contents("server/usercache.json"), true);
                    
                    foreach($usercache as $user)
                    {
                        $name = $user["name"];
                        $uuid = $user["uuid"];
                        
                        $json = file_get_contents("server/world/stats/$uuid.json");
                        $stats = json_decode($json);
                        
                        foreach(array_keys($statList) as $key)
                        {
                            if(property_exists($stats, "stat.".$key))
                                $players[$name][$key] = $stats->{"stat.".$key};
                            else
                                $players[$name][$key] = -1;
                        }
                        
                        $total = 0;
                        foreach($players[$name] as $value)
                            if($value > 0)
                                $total += $value;
                            
                        $players[$name]["total"] = $total;
                    }
                    
                    // SORT BY STAT
                    
                    if(!isset($defaultKey))
                        $statList["total"] = array("Total", $statList[array_keys($statList)[0]][1]);
                    
                    if(isset($_GET["sort"]))
                        $sortStat = $_GET["sort"];
                    else if(isset($defaultKey))
                        $sortStat = $defaultKey;
                    else
                        $sortStat = "total";
                        
                    if(!in_array($sortStat, array_keys($statList)))
                    {
                        echo "Erreur : tri impossible, statistique introuvable \"$sortStat\" !";
                        exit;
                    }
                    
                    uasort($players, function($a, $b)
                    {
                        global $sortStat;
                        return $b[$sortStat] - $a[$sortStat];
                    });
                    
                    // SORT BY PLAYER

                    if(isset($defaultKey))
                        $sortPlayer = "";
                        
                    else
                    {
                        if(isset($_GET["name"]))
                        $sortPlayer = $_GET["name"];
                        else
                            $sortPlayer = array_keys($players)[0];
                            
                        if(!in_array($sortPlayer, array_keys($players)))
                        {
                            echo "Erreur : tri impossible, joueur introuvable \"$sortPlayer\" !";
                            exit;
                        }
                        
                        uksort($statList, function($a, $b)
                        {
                            global $players, $sortPlayer;
                            return $players[$sortPlayer][$b] - $players[$sortPlayer][$a];
                        });
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
                             .($sorted || isset($defaultKey) ? "</b>" : "</a>")."</td>";
                        $rank++;
                    }
                    echo "</tr>\n";
                    
                    foreach($statList as $key => $values)
                    {
                        echo "<tr>";
                        
                        $sorted = $key == $sortStat;
                        echo "<th".($sorted ?
                             " class=\"sorted\"><b>" :
                             "><a href=".makeUrl($statCat, $key, $sortPlayer).">")
                             .$values[0].($sorted ? "</b>" : "</a>")."</td>";
                        
                        foreach($players as $name => $player)
                        {
                            $sorted = $name == $sortPlayer || $key == $sortStat;
                            if($player[$key] > 0)
                                echo "<td".($sorted ? " id=\"sorted\">" : ">")
                                     .formatValue($player[$key], $statList[$key][1])."</td>";
                            else
                                echo "<td".($sorted ? " id=\"sorted\">" : ">")."—</td>";
                        }
                        
                        echo "</tr>\n";
                    }
                    
                    echo "</table>";
                ?>
            </section>
            <div id="attribution">
                Accéder au <a target="_blank" href="https://github.com/Olybri/Minecrew">dépôt GitHub</a> | 
                Fournisseur d'avatars : <a target="_blank" href="https://crafatar.com">Crafatar</a></div>
        </div>
        <br>
    </body>
</html>
