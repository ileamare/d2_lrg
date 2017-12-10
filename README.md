Dota 2 League Stats Fetcher and Report Generator

**Q: But why PHP?**

Just because. There's no reasoning behind it. I just wanted to do so. Don't judge me.

**Q: What does it do?**

* rg_fetcher - Fetches matchdata from OpenDota, adds it to MySQL database (there's no special reason to use it as well).
* rg_analyzer - Generates precached report file based on matchdata.
* (TODO) rg_report - Generates fancy (or not so fancy) report page for rg_analyzer output. Can be used on your webserver as well.
* (TODO) rg_init - Initialises new league database and other resources.

=== settings.php

You can specify your Steam API key and MySQL credentials, as well as change MySQL database prefix.

This module is included by rg_init, rg_fetcher and rg_analyzer

=== rg_init

=== rg_fetcher

=== rg_analyzer

=== rg_report_web

This file should be put on your webserver, as well as "reports/" folder with all the reports in it and "res/".

Settings you can change in it:
* $lrg_use_get -- Use GET parameters for opening league files and generating modules, `true` by default
* $lrg_get_depth -- Sets module link depth for GET parameters, `2` by default. Modules deeper than this will be fully generated
* $locale -- Translation file you will use for your reportm, `"en"` by default


It can also be used from command line as "php rg_report_web.php > index.html" with following parameters:
* -lVALUE -- Open report for league VALUE
* -f -- Generate full report (all-in-one HTML file, equivalent of `$lrg_get_depth = 0`)
* -dVALUE -- Force module depth, force-sets `$lrg_get_depth` to VALUE
* -mVALUE -- Generate module VALUE, equivalent to `?mod=VALUE` in GET request


**What do I need to use it?**

MySQL database server and PHP interpreter. Nothing really special, you can use regular XAMPP for it.

I also recommend using PHPMyAdmin for manual data change.
