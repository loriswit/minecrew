<?php

$config = require "config.inc";

// HELPERS

function makeUrl($stat, $sort = "", $name = "")
{
    return '"'.filter_input(INPUT_SERVER, "PHP_SELF")."?stat=$stat"
           .(empty($sort) ? "" : "&sort=$sort")
           .(empty($name) ? "" : "&name=$name").'"';
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

        case "bool":
            return "Visité";
            
        default:
            return number_format($value, 0, ",", "'")." $type";
    }
}

// GET CURRENT CATEGORY

$statCat = isset($_GET["stat"]) ?
    $_GET["stat"] :
    "misc";
    
$statFilename = "statlist/$statCat.inc";

if(!file_exists($statFilename))
{
    $pieces = explode("_", $statCat);
    if(count($pieces) > 1)
    {
        $statFilename = "statlist/$pieces[1].inc";
        if(!file_exists($statFilename))
        {
            echo "Erreur : type invalide \"$pieces[1]\" !";
            exit;
        }
        $prefix = "stat.$pieces[0]";
        $showUnlisted = false;
    }

    else
    {
        echo "Erreur : catégorie introuvable \"$statCat\" !";
        exit;
    }
}
else
    $showUnlisted = true;

include $statFilename;

// PRINT CATEGORIES

echo "<table><tr>\n";
$addCategory = function($name, $title) use($statCat)
{
    echo "<th onclick='printStats(".makeUrl($name).")'".($name == "misc" ? " rowspan='2'" : "").($statCat == $name ? " class='sorted'>" : "class='unsorted'>")
         .$title."</th>\n";
};
$addCategory("misc", "Divers");
$addCategory("biomes", "Biomes");
$addCategory("distance", "Déplacements");
$addCategory("killentity", "Créatures tuées");
echo "<td class='blank'></td>\n";
$addCategory("craftItem_items", "Objets fabriqués");
$addCategory("useItem_items", "Objets utilisés");
$addCategory("breakItem_items", "Objets épuisés");
$addCategory("pickup_items", "Objets ramassés");
$addCategory("drop_items", "Objets lâchés");
echo "\n</tr><tr>\n";
$addCategory("interaction", "Interactions");
$addCategory("achievement", "Trophées");
$addCategory("entitykilledby", "Tué par créatures");
echo "<td class='blank'></td>\n";
$addCategory("craftItem_blocks", "Blocs fabriqués");
$addCategory("useItem_blocks", "Blocs utilisés");
$addCategory("mineBlock_blocks", "Blocs minés");
$addCategory("pickup_blocks", "Blocs ramassés");
$addCategory("drop_blocks", "Blocs lâchés");

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
{
    $dates = json_decode(file_get_contents($datesFilename));

    $daysSinceFirstSeen = function($playerName) use($dates)
    {
        return (time() - $dates->{"firstSeen"}->{$playerName}) / 86400;
    };
}

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
        if($statCat == "biomes")
            $players[$name][$key] = in_array($key, $stats->{$prefix}->{"progress"}) ?
                1 : 0;

        else if($key == "exploreAllBiomes")
             $players[$name][$key] = $stats->{$prefix.".".$key}->{"value"};

        else if($key != "average")
            $players[$name][$key] = property_exists($stats, "$prefix.$key") ?
                $stats->{"$prefix.$key"} :
                0;

        else if(isset($dates))
            $players[$name]["average"] = $stats->{"$prefix.playOneMinute"} / $daysSinceFirstSeen($name);
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

if($statCat != "biomes")
    foreach($players as $items)
        foreach($items as $key => $value)
        {
            if(!isset($players["Total"][$key]))
                $players["Total"][$key] = $value;
            else
                $players["Total"][$key] += $value;
        }

if(!isset($defaultKey))
    $statList["total"] = array("Total", $statCat == "biomes" ?
        "" :
        $statList[array_keys($statList)[0]][1]);

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

if($statCat  == "biomes")
{
    unset($statList["average"]);
    unset($statList["everyHour"]);
}

// PRINT TABLE

echo "<table>";
    
echo "<tr><td class='corner'>Statistique :</td>\n";

$rank = 1;
foreach(array_keys($players) as $name)
{
    $sorted = $name == $sortPlayer;
    echo "<th ".($sorted || isset($defaultKey) ? "" : "onclick='printStats(".makeUrl($statCat, $sortStat, $name).")'")
         .(!isset($defaultKey) && $sorted ? " class='sorted'>" : (!isset($defaultKey) && !$sorted ? " class='unsorted'>" : ">"))
         .($name == "Total" ? "" : "$rank. ")
         ."$name "
         .($name == "Total" ? "" : "<img src='https://crafatar.com/avatars/$name?size=16&default=MHF_Steve&overlay'>")
         ."</td>";
    if($name != "Total")
        $rank++;
    else
        echo "<td class='blank'></td>";
}
echo "</tr>\n";

foreach($statList as $key => $values)
{
    if(!$config["UNUSED_STATS"] && !in_array($key , array("total", "average", "everyHour")) && array_sum(array_column($players, $key)) <= 0)
    {
        if(!isset($unlisted))
            $unlisted = "<b>Non-listés :</b> ";

        $unlisted .= $values[0]." | ";
        continue;
    }
        
    echo "<tr>";
    
    $sorted = $key == $sortStat;
    echo "<th "
         .(in_array($key , array("total", "average", "everyHour")) ? "" : "title='$key'")
         ."onclick='printStats(".makeUrl($statCat, $key, $sortPlayer).");' "
         .($sorted ? " class='sorted'>" : "class='unsorted'>")
         ."$values[0]</td>";
    
    foreach($players as $name => $player)
    {
        $sorted = $name == $sortPlayer || $key == $sortStat;
        echo "<td".($sorted ? " class='sorted'>" : ">")
             .formatValue($player[$key], $statList[$key][1])."</td>";
        if($name == "Total")
            echo "<td class='blank'></td>";
    }
    
    echo "</tr>\n";

    if($key == "everyHour" || ($statCat == "biomes" && $key == "total"))
        echo "<tr><td class='blank'></td></tr>\n";
}

echo "</table>";

if($showUnlisted)
    echo isset($unlisted) ? "<br>".substr($unlisted, 0, -3) : "";

?>