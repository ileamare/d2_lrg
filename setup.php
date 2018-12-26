<?php
require_once("modules/commons/check_directory.php");
require_once("modules/commons/readline.php");

# Initialising
$check_libs = true;
$update_settings = true;
$check_folders = true;

if(isset($argv)) {
    $options = getopt("sld");

    if(isset($options['l'])) {
        $check_libs = false;
    }
    if(isset($options['s'])) {
        $update_settings = false;
    }
    if(isset($options['d'])) {
        $check_folders = false;
    }
}
# TODO
# get vis.js and chart.js from CDN
# get hero portraits from Valve CDN?


# Creating directories
if ( $check_folders ) {
    check_directory("cache");
    check_directory("backups");
    check_directory("leagues");
    check_directory("matchlists");
    check_directory("reports");
    check_directory("libs");
    check_directory("tmp");
}
# Downloading libs via Git

if ( $check_libs ) {
    # LRG Metadata
    if ( check_directory("metadata") ) {
        chdir("metadata");
        echo("[ ] Git: Pulling updates for LRG Metadata\n");
        `git pull`;
        chdir("..");
    } else {
        echo("[ ] Git: Downloading LRG Metadata\n");
        `git clone https://github.com/leamare/D2-LRG-Metadata.git metadata`;
    }

    # TODO simple stratz php

    # Simple OpenDota PHP
    if ( check_directory("libs/simple-opendota-php") ) {
        chdir("libs/simple-opendota-php");
        echo("[ ] Git: Pulling updates for Simple OpenDota API for PHP\n");
        `git pull`;
        chdir("../..");
    } else {
        chdir("libs");
        echo("[ ] Git: Downloading Simple OpenDota API for PHP\n");
        `git clone https://github.com/leamare/simple-opendota-php.git`;
        chdir("..");
    }

    # jQuery and tablesorter
    if ( !file_exists("res/dependencies/jquery.min.js") ) {
      echo "[ ] Pulling jQuery 3.3.1 slim\n";
      file_put_contents(
        "res/dependencies/jquery.min.js",
        file_get_contents("https://code.jquery.com/jquery-3.3.1.slim.min.js")
      );
    }
    if ( !file_exists("res/dependencies/jquery.tablesorter.min.js") ) {
      echo "[ ] Pulling jQuery Tablesorter plugin\n";
      file_put_contents(
        "res/dependencies/jquery.tablesorter.min.js",
        file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js")
      );
    }

    # Vis.JS
    if ( !file_exists("res/dependencies/vis.min.js") ) {
      echo "[ ] Pulling vis.js 4.21.0 min.js\n";
      file_put_contents(
        "res/dependencies/vis.min.js",
        file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.js")
      );
    }
    if ( !file_exists("res/dependencies/vis.min.css") ) {
      echo "[ ] Pulling vis.js 4.21.0 min.css\n";
      file_put_contents(
        "res/dependencies/vis.min.css",
        file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis.min.css")
      );
    }
    if ( !file_exists("res/dependencies/vis-network.min.css") ) {
      echo "[ ] Pulling vis.js 4.21.0 network.min.css\n";
      file_put_contents(
        "res/dependencies/vis-network.min.css",
        file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-network.min.css")
      );
    }
    if ( !file_exists("res/dependencies/vis-network.min.js") ) {
      echo "[ ] Pulling vis.js 4.21.0 network.min.js\n";
      file_put_contents(
        "res/dependencies/vis-network.min.js",
        file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/vis/4.21.0/vis-network.min.js")
      );
    }

    # Chart.JS
    if ( !file_exists("res/dependencies/Chart.bundle.min.js") ) {
      echo "[ ] Pulling chart.js 2.7.2 bundle\n";
      file_put_contents(
        "res/dependencies/Chart.bundle.min.js",
        file_get_contents("https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js")
      );
    }
}

# Setting things up

if ( $update_settings ) {
    echo("[ ] Updating settings\n");
    if ( file_exists("rg_settings.json") ) {
        echo("[ ] Loading current settings\n");
        $settings = json_decode(file_get_contents("rg_settings.json"), true);
        echo("[ ] Enter updated settings (empty line for old values)\n");

        echo("[I] Steam API Key: ");
        $line = readline_rg();
        if ( !empty($line) )  $settings['steamapikey'] = $line;

        echo("[I] OpenDota API Key (enter `n` for none): ");
        $line = readline_rg();
        if ( !empty($line) )  {
          if ($line == "n")
            $settings['odapikey'] = "";
          else
            $settings['odapikey'] = $line;
        }

        echo("[I] MySQL host: ");
        $line = readline_rg();
        if ( !empty($line) )  $settings['mysql_host'] = $line;

        echo("[I] MySQL user: ");
        $line = readline_rg();
        if ( !empty($line) )  $settings['mysql_user'] = $line;

        echo("[I] MySQL password: ");
        $line = readline_rg();
        if ( !empty($line) )  $settings['mysql_pass'] = $line;

        echo("[I] MySQL database name prefix: ");
        $line = readline_rg();
        if ( !empty($line) )  $settings['mysql_prefix'] = $line;
    } else {
        echo("[ ] Enter your settings\n");

        echo("[I] Steam API Key: ");
        do {
            $settings['steamapikey'] = readline_rg();
        } while (empty($settings['steamapikey']));

        echo("[I] OpenDota API Key (empty for none): ");
        do {
            $settings['odapikey'] = readline_rg();
        } while (empty($settings['odapikey']));

        echo("[I] MySQL host: ");
        do {
            $settings['mysql_host'] = readline_rg();
        } while (empty($settings['mysql_host']));

        echo("[I] MySQL user: ");
        do {
            $settings['mysql_user'] = readline_rg();
        } while (empty($settings['mysql_user']));

        echo("[I] MySQL password: ");
        $settings['mysql_pass'] = readline_rg();

        echo("[I] MySQL database name prefix: ");
        $line = readline_rg();
        if ( !empty($line) )  $settings['mysql_prefix'] = $line;
        else {
            $settings['mysql_prefix'] = "d2_report";
            echo "    Using default prefix (`d2_report`)\n";
        }
    }

    echo("[ ] Saving settings to `rg_settings.json`...");
    file_put_contents("rg_settings.json", json_encode($settings, JSON_PRETTY_PRINT)) or die("ERROR\n");
    echo("OK\n");
}
?>
