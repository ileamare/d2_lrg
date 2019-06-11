# Dota 2 League Report Generator (D2-LRG-lrg2)

**IMPORTANT**

This is the **lrg2** version that won't be updated any further.

For new releases look for leamare/D2-LRG-Simon repository or issue #102.

D2-LRG-Simon will feature major performance improvements, new features, API, WAY BETTER code that will be rewritten from scratch and with all the features in mind. This version was written as I was going on with the idea so it was initially just a shitty PHP script and it got bigger eventually. There are a lot of relics of that and it's pretty hard to work with all this stuff.

If you're reading this before LRG-Simon was finished - the first working version should be ready sometime in September.

## Current version: 2.4.0

**D2-LRG** (for short) is a tool for fetching stats for dota matches and forming fancy stats pages with every data you may need.

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
* Regions stats

Thanks to custom leagues, it can be used as a tool for teams as well. It is possible to fetch data for enemy teams before tournaments, as well as forming stats reports for your own team to make analysis for that.

Working prototype: http://stats.spectral.gg/

## How does it work

D2LRG is made of four scripts, divided into smaller modules:
- `init` creates a .league file with parameters and a database on MySQL-based server. It also creates .matchlist file and requests matches from Steam API or just leaves it blank
- `fetcher` reads matches from .matchlist and requests OpenDota analysis data for every match in it. After that every match is added to database
- `analyzer` makes series of SQL requests to get ready-to-use data and saves it as .report file as JSON data. It can be transferred to another D2LRG instance from now on and not linked to MySQL anymore
- `view` is standalone module that reads .report file and allows easy to use basic web interface for it

### Why PHP and Why migrate to LRG-Simon

1. The project was created as a proof-of-concept collection of scripts. Initially it should be working on a cheapest web server possible with minimal effort. It became much bigger than intended, but it still can be used that way.
2. PHP5 and PHP7 are easy and fast tools to work with
3. LRG-Simon (LRG3) will be rebuilt from scratch. A lot of features and ideas will be backported from LRG2, but the code will be much easier to read and to work with. It will also be harder to work with, but it will be more powerful too.

### What do I need to use it?

MySQL database server and PHP interpreter. Nothing really special, you can use regular XAMPP for it.

I also recommend using PHPMyAdmin for manual data change.

It would be nice to have bash on your system. For Windows it's recommended to have git bash or cygwin with ConEmu.

## Dependencies:
* PHP 7+
* php mbstring
* MySQL
* cURL

After getting D2LRG code to your computer, run `php setup.php`. It will install the rest of dependencies:
* Vis.js
* Graph.js
* jQuery
* jquery-tablesorter
* Simple PHP OpenDota SDK
* D2-LRG-Metadata

## Setup

Use `php setup.php` to install, setup and update all the things.

You can also specify special parameters:
* `-l` - skip dependency updates
* `-s` - skip settings update
* `-d` - skip directory check

It's recommended to use `php setup.php -ds` to update.

## How to use

LRG is made of these modules:
* `rg_init.php` - Initialises new league database and other resources.
* `rg_fetcher.php` - Fetches matchdata from OpenDota, adds it to MySQL database (there's no special reason to use it as well).
* `rg_analyzer.php` - Generates precached report file based on matchdata.
* `rg_report.php` - Generates fancy (or not so fancy) report page for rg_analyzer output. Can be used on your webserver as well.

Beforehand you should use `setup.php` to initialise settings (like database credentials or Steam API key) file and download dependencies. Just run it and follow instructions.

You can also use `setup.php` to update dependencies or settings. It has special parameters to skip unnecessary parts:
* `-l` - skip downloading/updating libraries
* `-s` - skip updating settings
* `-d` - skip directories check

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
* `-l` - required, the tag of your database, league and report file
* `-K` - ignore OpenDota API key
* `-F` - add match to database even if there's no replay analysis available
* `-cDIRECTORY` - Use DIRECTORY to store and check cached data (./cache by default)
* `-R` - Automatically request a match to parse and try again later
* `-S` - Strongly require STRATZ (for Ranked All Pick and Random Draft pick order and non-tournament matches player names), skip it STRATZ doesn't respond. In combination with -R reschedules match and tries again later
* `-s` - Softly require STRATZ (works just like -S, but after failure just continues without STRATZ response)
* `-Z` - Use full match data request from Stratz (called when can't get data using shortcuts)
* `-w123` - Specify number of seconds to wait before requesting scheduled matches again (default: 60)
* `-A` - Force await flag, forces awaiting for awailability of replay data (for cases when you pass data to the script and don't want it to end on EOF)

Fetcher has two modes: "listen" mode and the regular one. Fetcher is using matchlist from `matchlists` follder by default, listen mode changes it to STDIN. It's not async because of time limitation on OpenDota side (basically you can't be too fast with your requests anyway, so there's no need to be asynchronous).

Matchlist is basically a line delimited text file, each line is a new match string. It's using following rules:
* Lines starting with `#` symbol or empty lines are skipped
* If a line consists of a number, it's used as a match ID
* If a line is a matchrule string, it's being parsed by `processRules()`

Listen mode accepts lines in the same format.

Fetcher matchrule string is a string matching following format: `{matchid}::{rule_type}:{search}:{value}::...rules`, where {search} is replaced by {value} following specific {rule_type} rules. Any matchrule string may have an unlmited number of rules.

Rules types:

* `player` - replaces one player steam ID with another (`player:1234:2345`)
* `pslot` - replaces player steam ID of a player in a given slot (`player:0:1234`)
* `team` - replaces one team ID with another (`team:1:2`)
* `side` - replaces team ID of a team based on it's side, given sides are either `radiant_team` or `dire_team` (`side:dire_team:1234`)

And some things about the listen mode:

* it ends on EOF
* it keeps going on forever otherwise
* it's not async and it looks horrifying; I was thinking about implementing some kind of setTimeout() thingy from JS, but it's not compatible with all OSes
* it may or may not hang up when you're passing matches in force mode and it's not a big amount of matches. Basically the script will be waiting for your input and then will continue working. Or maybe not. I'm not entirely sure.

### rg_analyzer

Analyzes and requests data, forms report file.

Parameters:
* `-l / --league=` - required, the tag of your database, league and report file
* `-K` - ignore OpenDota API key
* `-T` - merge settings with a template
* `-oFILENAME` - save result to FILENAME

### rg_report_web

This file (and rg_report_out_settings.php) should be put on your webserver, as well as "reports/" folder with all the reports in it and "res/", "modules/functions" and "modules/view". You will also need to use .htaccess to disable access to modules and settings files.

Settings you can change in it:
* `$lrg_use_get` - Use GET parameters for opening league files and generating modules, `true` by default
* `$lrg_get_depth` - Sets module link depth for GET parameters, `2` by default. Modules deeper than this will be fully generated
* `$locale` - Translation file you will use for your report, `"en"` by default
* `$locales` - List of locales, available for choosing by user. Every value is recorded in format `"locale tag" => "Locale name"`
* `$max_tabs` - Maximum amount of links shown before it gets replaced by `<select>`
* `$custom_head` - Custom text and tags that will be used in `<head>`
* `$custom_body` - Custom text and tags that will be used in `<body>`
* `$custom_content` - Custom text that's placed before actual generated content
* `$custom_footer` - Additional text that will be added to `<footer>`
* `$title_links` - List of links in title bar. Values are arrays `[ "link" => "http...", "title" => "Lorem Ipsum", "text" => "Text" ]`
* `$main_path` - Link to an adress that should be opened by clicking top left icon in title bar
* `$default_style` - Custom style used by default
* `$noleague_style` - Custom style used if no report is selected
* `$instance_title` - Instance title
* `$instance_name` - Instance description
* `$reports_dir` - Path to reports' directory
* `$report_mask` - Mask for finding report files in directory
* `$report_mask_search` - Left and right parts of default report's filename
* `$cache_file` - Path to cache file
* `$cats_file` - Path to categories file
* `$hidden_cat` - Category that will be used as "hidden": reports that apply for it will not be shown in main list
* `$index_list` - Number of reports on main page. `-1` is equal to showing all reports, positive number is equal to showing a chosen number of reports, zero is equal for not showing any reports

If cache file is specified, it will be generated at first page load and then every time any report changes. While generating, it opens every report and creates a descriptor for it, so it may take a while at first.

GET parameters:
* `loc` - Force use of locale
* `stow` - Force use of custom style
* `mod` - Module link
* `league` - Current league report

It can use custom styles. To use them you need to put a .css file to "res/custom_styles" directory. You can also get some additional information from custom styles library repository: https://github.com/leamare/D2-LRG-Custom-Styles

Categories file is a simple JSON configuration file. If it is specified and it exists, categories will be loaded and then applied to every report on instance. There is an example of categories config available, but essentially it looks like this:
```json
{
  "category_tag": {
    "name": "Category Name",
    "names_locales": { "ru": "Category name for a specific locale" },
    "desc": "Category Description",
    "desc_locales": { "ru": "Category description for a specific locale" },
    "filters": [
      [ [ 0, "value" ] ]
    ],
    "custom_style": "custom style",
    "custom_logo": "custom logo"
  }
}
```

All category filters types are listed in `modules/view/functions/check_filters.php`. First level filters are applied with logical OR, second level filters are applied with logical AND.

## Tools

Tools are additional scripts that can be used for specific things. All of them should be executed from the main directory with command similar to `php tools/%TOOL%.php %PARAMETERS%`.

* `remove_match` - removes match from a league's database. Uses only `-l%TAG%` and `-m%MATCH%` parameters. Example: `php tools/remove_match.php -ltestleague -m1234567891`
* `remove_matches` - removes matches from a league's database. Uses only `-l%TAG%` and `-f%FNAME%` parameters. Example: `php tools/remove_match.php -ltestleague -flist`
* `replay_request` - sends API commands to OpenDota and Stratz, requesting every match from a file. Accepts only one arguement: `-f%FILENAME%`. You can use failed matches dump files (from fetcher) with it
* `replay_request_od` - same as `replay_request`, but strictly requesting match analysis with OpenDota
* `replay_request_stratz` - same as `replay_request`, but strictly requesting match analysis with Stratz
* `clear_database` - removes all data from a league's database. Args: `-l%LEAGUETAG%`
* `update_league` - updates league parameters to new D2LRG API/leaguefile format. Accepts only `-l%LEAGUETAG%`
* `remove_cached` - removes cached matches listed in `-f%FNAME%` file.
* `update_all_reports` - updates all reports
* `backport_matchlist` - generates full matchlist based on league's database
* `update_rosters` - updates official rosters for all teams in report. Args: `-l%LEAGUETAG%`
