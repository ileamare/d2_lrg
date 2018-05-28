# 1.3.1

It's the first major release since 1.1.0. Initially I had plans to release 1.2.0 with a bunch of fixes, but changes kind of added up.
This release is essentially version 1.2.0 with a bunch of fixes, so it's nothing special that there's no version 1.2.0.

## Changelog

### General
- Added installation script (setup.php)
- OpenDota API functionality now uses Simple OpenDota API for PHP
- Moved a lot of functions to separate files in modules/functions folder
- Metadata now has information only about spicific patch numbers
- Added proper README
- Implemented support of OpenDota API key
- minor changes

### Tools
- Added replay_request tool (for OpenDota)
- Fixes to remove_match tool
- Added remove_matches tool

### Fetcher
- Updated get_patchid() commentaries

### Analyzer
- Added hero diversity metric
- Added hero pairs expectations
- Limiters are now using median in their formulas

### Web View
- Displayed patch number is now dynamically generated
- Support for minor patch versions (using literals)
- Initial split of web view module (right now only affects records)
- Graphs: all settings were moved to specific variable
- Graphs: edges now change colors depending on winrate
- Graphs: nodes now have hero portraits
- Replaced `duration` with `avg_match_len` for teams averages
- Reworked player combo graphs
- Added "Outcome Impact" metric
- Added Hero Pairs Expectation and Divergence metrics
- Table sorting is now Descending by default
- New custom styles: WESG 2017, PGL bucharest major
- Optimization to resources

### Fixes
- Small sorting fixes for overview page (web view)
- Improved roaming detection (fetcher)
- Improved role/lane detection (fetcher)
- Fixes to draft bans display (web view)
- Dire winrate fix for teams (web view)

## Current Goals

- Remove unnesessary compatibility with older versions of LRG. It's not like it has much sense
- Continue to split code up into separate modules
- Rework web view module to use MVC-like system

# 1.1.1

* Move Steam API key to separate file
* Fix remove match tool
* Add automatic locale detection from user agent (pretty basic)
* Fix legacy locale bug with wrong string name for radiant winrates on team's page (ana, web)
* Fix team's draft page using global bans amount (web)
