<?php
require_once("settings.php");
require_once("modules/mod.migrate_params.php");

$reports = scandir("leagues");

if (!file_exists("templates/default.json")) die("[F] No default league template found, exitting.");

$lg_settings = json_decode(file_get_contents("templates/default.json"), true);

$old = file_get_contents("leagues/".$lrg_league_tag, "r") or die("[F] Couldn't open league file.\n");
$old = json_decode($old, true);

migrate_params($lg_settings, $old);

echo " OK";

$f = fopen("leagues/".$lrg_league_tag.".json", "w+") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
fwrite($f, json_encode($lg_settings));
fclose($f);

echo " OK\n";
}

 ?>
