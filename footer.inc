<?php

function printFooter($attribution, $title, $link)
{
    echo
    "<footer>"
        ."Propulsé par <a target=\"_blank\" href=\"https://github.com/Olybri/Minecrew\">Minecrew</a>"
        ." | "
        ."$attribution : <a  target=\"_blank\" href=\"$link\">$title</a>"
    ."</footer>";
}

?>