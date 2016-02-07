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
                <a href="index.php">Retour</a>
                <br>
                <br>
            </header>
            <section>
                <?php
                    // HELPERS
                
                    function makeUrl($stat, $sort = "", $name = "")
                    {
                        return "\"".$_SERVER["PHP_SELF"]."?stat=$stat".(empty($sort) ? "" : "&sort=$sort").(empty($name) ? "" : "&name=$name")."\"";
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
                                return ($value > 72000 ? number_format($value / 72000, 0, ",", "'")." $type " : "")
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
                        
                    $statFilename = "statlist/".$statCat.".php";
                    
                    if(!file_exists($statFilename))
                    {
                        echo "Erreur : statistiques introuvables \"$statCat\"";
                        exit;
                    }
                    
                    // PRINT CATEGORIES
                    
                    echo "<table><tr>\n";
                    $addCategory = function($name, $title)
                    {
                        global $statCat;
                        echo "<th".($statCat == $name ? " id=\"sorted\"><strong>" : "><a href=".makeUrl($name).">");
                        echo $title;
                        echo ($statCat == $name ? "</strong>" : "</a>")."</th>\n";
                    };
                    $addCategory("misc", "Divers");
                    $addCategory("distance", "Déplacements");
                    $addCategory("interaction", "Interactions");
                    $addCategory("craftitem", "Objets fabriqués");
                    $addCategory("useblock", "Blocs utilisés");
                    echo "\n</tr><tr>\n";
                    $addCategory("useitem", "Objets utilisés");
                    $addCategory("breakitem", "Objets épuisés");
                    $addCategory("mineblock", "Blocs minés");
                    $addCategory("killentity", "Entités tuées");
                    $addCategory("entitykilledby", "Tué par entités");
                    echo "</tr></table><br>\n";
                        
                    include $statFilename;
                    
                    // FILL PLAYER STATS
                    
                    include "uuid.php";
                    foreach($uuid as $name => $id)
                    {
                        $json = file_get_contents("server/world/stats/$id.json");
                        $stat = json_decode($json);
                        
                        foreach(array_keys($statList) as $key)
                        {
                            if(property_exists($stat, "stat.".$key))
                                $players[$name][$key] = $stat->{"stat.".$key};
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
                        $sortBy = $_GET["sort"];
                    else if(isset($defaultKey))
                        $sortBy = $defaultKey;
                    else
                        $sortBy = "total";
                        
                    if(!in_array($sortBy, array_keys($statList)))
                    {
                        echo "Erreur : tri impossible, élément introuvable \"$sortBy\"";
                        exit;
                    }
                    
                    uasort($players, function($a, $b)
                    {
                        global $sortBy;
                        return $b[$sortBy] - $a[$sortBy];
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
                            echo "Erreur : tri impossible, joueur introuvable \"$sortPlayer\"";
                            exit;
                        }
                        $oldList = $statList;
                        
                        uasort($statList, function($a, $b)
                        {
                            global $players, $oldList, $sortPlayer;
                            return $players[$sortPlayer][array_search($b, $oldList)] - $players[$sortPlayer][array_search($a, $oldList)];
                        });
                    }
                    
                    // PRINT TABLE
                    
                    echo "<table>";
                        
                    echo "<tr><td id=\"corner\">Statistique :</th>\n";
                    $rank = 1;
                    foreach(array_keys($players) as $name)
                    {
                        $sorted = $name == $sortPlayer;
                        echo "<th ".($sorted ? " class=\"sorted\"><strong>" : ">")."$rank. ";
                        echo $sorted || isset($defaultKey) ? "" : "<a href=".makeUrl($statCat, $sortBy, $name).">";
                        echo "$name <img src=\"https://crafatar.com/avatars/$name?size=16&default=MHF_Steve&overlay\">".($sorted ? "</strong>" : "</a>")."</td>";
                        $rank++;
                    }
                    echo "</tr>\n";
                    
                    foreach($statList as $key => $values)
                    {
                        echo "<tr>";
                        
                        $sorted = $key == $sortBy;
                        echo "<th".($sorted ? " class=\"sorted\"><strong>" : "><a href=".makeUrl($statCat, $key, $sortPlayer).">").$values[0].($sorted ? "</strong>" : "</a>")."</td>";
                        
                        foreach($players as $playerKey => $player)
                        {
                            $sorted = $playerKey == $sortPlayer || $key == $sortBy;
                            if($player[$key] == -1)
                                echo "<td".($sorted ? " id=\"sorted\">" : ">")."—</td>";
                            else
                                echo "<td".($sorted ? " id=\"sorted\">" : ">")."".formatValue($player[$key], $statList[$key][1])."</td>";
                        }
                        
                        echo "</tr>\n";
                    }
                    
                    echo "</table>";
                ?>
            </section>
            <div id="attribution">Thank you to <a target="_blank" href="https://crafatar.com">Crafatar</a> for providing avatars.</div>
        </div>
        <br>
    </body>
</html>
