<?php
/* SETTINGS */

$lrg_use_get = true;
$lrg_get_depth = 6;
$locale = "en";
$locales = [
  "en" => "English",
  "ru" => "Русский"
];

$max_tabs = 12;

$custom_head = "";

$custom_body = "";

$custom_content = "";

$custom_footer = "";

$support_me_block = "asdas";

$ads_block = "";

$ads_block_main = "";

$title_links = array(
  // array( "link" => "",
  //       "title" => "",
  //       "text"
 //)
);

$main_path = "/";

$default_style = "";
$noleague_style = "";

$instance_title = "LRG";
$instance_name = "League Report Generator Instance";

# main page settings
$reports_dir = "reports";
$report_mask = "/(.*)\/?report_(.*)\.json/";
$report_mask_search = ["report_", ".json"];

$cache_file = "res/cachelist.json";
$cats_file = "res/meowslist.json";

$hidden_cat = "hidden";

$index_list = 5; #-1 all, 0 none, other - number of reports on main page

$link_provider = "stratz.com"; //opendota.com dotabuff.com

?>
