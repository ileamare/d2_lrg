<?php
require_once("head.php");
require_once("modules/mod.migrate_params.php");

$reports = scandir("leagues");

if (!file_exists("templates/default.json")) die("[F] No default league template found, exitting.");

$def_settings = json_decode(file_get_contents("templates/default.json"), true);

migrate_params($def_settings, $lg_settings);

echo " OK";

$f = fopen("leagues/".$lrg_league_tag.".json", "w+") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
fwrite($f, json_encode($def_settings));
fclose($f);

echo " OK\n";
}

 ?>
