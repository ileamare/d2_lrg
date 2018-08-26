# Dota 2 League Stats Fetcher and Report Generator

## Current version: 2.0.0

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
* Regions stats

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
* `-l / --league=` - required, the tag of your database, league and report file
* `-K` - ignore OpenDota API key

### rg_analyzer

Analyzes and requests data, forms report file.

Parameters:
* `-l / --league=` - required, the tag of your database, league and report file
* `-K` - ignore OpenDota API key
* `-T` - merge settings with a template

### rg_report_web

This file (and rg_report_out_settings.php) should be put on your webserver, as well as "reports/" folder with all the reports in it and "res/", "modules/functions" and "modules/view". You will also need to use .htaccess to disable access to modules and settings files.

Settings you can change in it:
* `$lrg_use_get` - Use GET parameters for opening league files and generating modules, `true` by default
* `$lrg_get_depth` - Sets module link depth for GET parameters, `2` by default. Modules deeper than this will be fully generated
* `$locale` - Translation file you will use for your report, `"en"` by default
* `$locales` - List of locales, available for choosing by user. Every value is recorded in format `"locale tag" => "Locale name"`
* `$max_tabs` - Maximum amount of links shown before it gets replaced by <select>
* `$custom_head` - Custom text and tags that will be used in <head>
* `$custom_body` - Custom text and tags that will be used in <body>
* `$custom_content` - Custom text that's placed before actual generated content
* `$custom_footer` - Additional text that will be added to <footer>
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
* `replay_request` - sends API command to OpenDota, requesting every match from a file. Accepts only one arguement: `-f%FILENAME%`. You can use failed matches dump files (from fetcher) with it
* `clear_database` - removes all data from a league's database. Args: `-l%LEAGUETAG%`
* `update_league` - updates league parameters to new D2LRG API/leaguefile format. Accepts only `-l%LEAGUETAG%`
* `remove_cached` - removes cached matches listed in `-f%FNAME%` file.
* `update_all_reports` - updates all reports
* `backport_matchlist` - generates full matchlist based on league's database 
