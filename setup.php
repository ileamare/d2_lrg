<?php
function check_directory($dir) {
    echo("[ ] Checking directory `$dir`\n");
    if ( !is_dir($dir) ) {
        if ( file_exists($dir) )
            die("[F] File named `$dir` exists in working directory. You should move or rename it.\n");
        mkdir($dir);
        echo("[S] Created directory `$dir`\n");
        return false;
    } else {
        echo("[ ] Directory `$dir` exists\n");
        return true;
    }
}

if(!function_exists("readline")) {
    function readline($prompt = null){
        if($prompt){
            echo $prompt;
        }
        $fp = fopen("php://stdin","r");
        $line = rtrim(fgets($fp, 1024));
        return $line;
    }
}

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
}
# Downloading libs via Git

if ( $check_libs ) {
    # TODO simple stratz php
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
}

# Setting things up
    
if ( $update_settings ) {
    echo("[ ] Updating settings\n");
    if ( file_exists("rg_settings.json") ) {
        echo("[ ] Loading current settings\n");
        $settings = json_decode(file_get_contents("rg_settings.json"), true);
        echo("[ ] Enter updated settings (empty line for old values)\n");
        
        echo("[I] Steam API Key: ");
        $line = readline();
        if ( !empty($line) )  $settings['steamapikey'] = $line;
        
        echo("[I] MySQL host: ");
        $line = readline();
        if ( !empty($line) )  $settings['mysql_host'] = $line;
        
        echo("[I] MySQL user: ");
        $line = readline();
        if ( !empty($line) )  $settings['mysql_user'] = $line;
        
        echo("[I] MySQL password: ");
        $line = readline();
        if ( !empty($line) )  $settings['mysql_pass'] = $line;
        
        echo("[I] MySQL database name prefix: ");
        $line = readline();
        if ( !empty($line) )  $settings['mysql_prefix'] = $line;
    } else {
        echo("[ ] Enter your settings\n");
    
        echo("[I] Steam API Key: ");
        do {
            $settings['steamapikey'] = readline();
        } while (empty($settings['steamapikey']));
        
        echo("[I] MySQL host: ");
        do {
            $settings['mysql_host'] = readline();
        } while (empty($settings['mysql_host']));
        
        echo("[I] MySQL user: ");
        do {
            $settings['mysql_user'] = readline();
        } while (empty($settings['mysql_user']));
        
        echo("[I] MySQL password: ");
        $settings['mysql_pass'] = readline();
        
        echo("[I] MySQL database name prefix: ");
        $line = readline();
        if ( !empty($line) )  $settings['mysql_prefix'] = $line;
        else {
            $settings['mysql_prefix'] = "d2_report";
            echo "    Using default prefix (`d2_report`)\n";
        }
    }
    
    echo("[ ] Saving settings to `rg_settings.json`...");
    file_put_contents("rg_settings.json", json_encode($settings)) or die("ERROR\n");
    echo("OK\n");
}
?>
