<?php
  require_once("settings.php");

  $conn = new mysqli($lrg_sql_host, $lrg_sql_user, $lrg_sql_pass, "d2_general_info");

  if ($conn->connect_error) die("[F] Connection to SQL server failed: ".$conn->connect_error."\n");

  $out = array();

  $sql = "SELECT heroID, hero_tag, hero_locale_name FROM heroes;
          SELECT itemID, itemTag FROM items;
          SELECT lane_id, name FROM lanes;
          SELECT modeID, modeName FROM gamemodes;
          SELECT region_id, region_name FROM regions;
          SELECT cluster, region_id FROM clusters;
          SELECT patchid, version FROM versions;";

  if ($conn->multi_query($sql)) {
    $res = $conn->store_result();

    $out['heroes'] = array();
    while ($row = $res->fetch_row()) {
      $out['heroes'][$row[0]] = array(
        "tag"    => $row[1],
        "name"   => $row[2]
      );
    }
    $res->free();

    $conn->next_result();

    $res = $conn->store_result();

    $out['items'] = array();
    while ($row = $res->fetch_row()) {
      $out['items'][$row[0]] = $row[1];
    }
    $res->free();

    $conn->next_result();

    $res = $conn->store_result();

    $out['lanes'] = array();
    while ($row = $res->fetch_row()) {
      $out['lanes'][$row[0]] = $row[1];
    }
    $res->free();

    $conn->next_result();

    $res = $conn->store_result();

    $out['modes'] = array();
    while ($row = $res->fetch_row()) {
      $out['modes'][$row[0]] = $row[1];
    }
    $res->free();

    $conn->next_result();

    $res = $conn->store_result();

    $out['regions'] = array();
    while ($row = $res->fetch_row()) {
      $out['regions'][$row[0]] = $row[1];
    }
    $res->free();

    $conn->next_result();

    $res = $conn->store_result();

    $out['clusters'] = array();
    while ($row = $res->fetch_row()) {
      $out['clusters'][$row[0]] = $row[1];
    }
    $res->free();

    $conn->next_result();

    $res = $conn->store_result();

    $out['versions'] = array();
    while ($row = $res->fetch_row()) {
      $out['versions'][$row[0]] = $row[1];
    }
    $res->free();

    $filename = "res/metadata.json";
    $f = fopen($filename, "w") or die("[F] Couldn't open file to save results. Check working directory for `reports` folder.\n");
    fwrite($f, json_encode($out));
    fclose($f);
  }


 ?>
