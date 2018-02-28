# Dota 2 League Stats Fetcher and Report Generator
## Current version: 1.1.2-dev

**D2LRG** (for short) is a tool for fetching stats for dota matches and forming fancy stats pages with every data you may need.

### Features
* Fetching data from OpenDota
* Getting player information and Ranked All Pick from Stratz (for non-tournament matches)
* Botmatches support
* Custom leagues (you form list of matches manually)
* Advanced positions stats for both players and heroes
* Advanced draft stats
* Individual teams stats
* Interactive meta graphs
* Mix-tournaments and leagues support

Thanks to custom leagues, it can be used as a tool for teams as well. It is possible to fetch data for enemy teams before tournaments, as well as forming stats reports for your own team to make analysis for that.

Working prototype: http://spectralalliance.ru/reports/

## How does it work

D2LRG is made of four scripts, divided into smaller modules:
- `init` creates a .league file with parameters and a database on MySQL-based server. It also creates .matchlist file and requests matches from Steam API or just leaves it blank
- `fetcher` reads matches from .matchlist and requests OpenDota analysis data for every match in it. After that every match is added to database
- `analyzer` makes series of SQL requests to get ready-to-use data and saves it as .report file as JSON data. It can be transferred to another D2LRG instance from now on and not linked to MySQL anymore
- `view` is standalone module that reads .report file and allows easy to use basic web interface for it

### But why PHP?

Just because. There's no reasoning behind it. I just wanted to do so. Don't judge me.

### What do I need to use it?

MySQL database server and PHP interpreter. Nothing really special, you can use regular XAMPP for it.

I also recommend using PHPMyAdmin for manual data change.

It would be nice to have bash on your system. For Windows it's recommended to have git bash or cygwin with ConEmu.

## Dependencies:
* PHP
* MySQL
* cURL

After getting D2LRG code to your computer, run `php setup.php`. It will install the rest of dependencies:
* Vis.js
* Graph.js
* PHP OpenDota SDK

## How to use

LRG is made of these modules:
* `rg_init` - Initialises new league database and other resources.
* `rg_fetcher.php` - Fetches matchdata from OpenDota, adds it to MySQL database (there's no special reason to use it as well).
* `rg_analyzer.php` - Generates precached report file based on matchdata.
* `rg_report` - Generates fancy (or not so fancy) report page for rg_analyzer output. Can be used on your webserver as well.

Beforehand you should use `setup.php` to initialise settings (like database credentials or Steam API key) file and download dependencies.

### rg_init

Creates database from template and saves leaguefile.

Parameters:
* `-l / --league=` - required, says what will be the tag of your database, league and report file
* `-T / --template=` - use a template of a league as a reference (default: none)
* `-I / --id=` - use league ID to fetch matches and record it to league information (default: null)
* `-N / --name=` - specify league's name (if empty it will be asked anyway)
* `-D / --desc=` - specify league's description (if empty it will be asked anyway)
* `-S / --settings=` - set specific parameters for the league. You can check default template to see all the parameters that you can change

### rg_fetcher

Fetches data for matches in matchlist.

Parameters:
* `-l / --league=` - required, the tag of your database, league and report file

### rg_analyzer

Analyzes and requests data, forms report file.

Parameters:
* `-l / --league=` - required, the tag of your database, league and report file

### rg_report_web

This file should be put on your webserver, as well as "reports/" folder with all the reports in it and "res/".

Settings you can change in it:
* `$lrg_use_get` - Use GET parameters for opening league files and generating modules, `true` by default
* `$lrg_get_depth` - Sets module link depth for GET parameters, `2` by default. Modules deeper than this will be fully generated
* `$locale` - Translation file you will use for your reportm, `"en"` by default


It can also be used from command line as "php rg_report_web.php > index.html" with following parameters:
* `-lVALUE` - Open report for league VALUE
* `-f` - Generate full report (all-in-one HTML file, equivalent of `$lrg_get_depth = 0`)
* `-dVALUE` - Force module depth, force-sets `$lrg_get_depth` to VALUE
* `-mVALUE` - Generate module VALUE, equivalent to `?mod=VALUE` in GET request

## Tools

Tools are additional scripts that can be used for specific things. All of them should be executed from the main directory with command similar to `php tools/%TOOL%.php %PARAMETERS%`.

* `remove_match` - removes match from a league's database. Uses only `-l%TAG%` and `-m%MATCH%` parameters. Example: `php tools/remove_match.php -ltestleague -m1234567891`
* `replay_request` - sends API command to OpenDota, requesting every match from a file. Accepts only one arguement: `-f%FILENAME%`. You can use failed matches dump files (from fetcher) with it
* `clear_database` - removes all data from a league's database. Args: `-l%LEAGUETAG%`
* `update_league` - updates league parameters to new D2LRG API/leaguefile format. Accepts only `-l%LEAGUETAG%`
