<?php
$result["hero_sides"] = array ();

for ($side = 0; $side < 2; $side++) {
  $result["hero_sides"][$side] = array();

  $sql = "SELECT
            ml.heroid, SUM(1) matches, SUM(NOT m.radiantWin XOR ml.isradiant)/SUM(1) winrate,
            SUM(ml.gpm)/SUM(1) gpm,
            SUM(ml.xpm)/SUM(1) xpm
          FROM matchlines ml JOIN matches m
                ON m.matchid = ml.matchid
          WHERE ml.isRadiant = $side
          GROUP BY ml.heroid
          ORDER BY matches DESC;";
  if ($conn->multi_query($sql) === TRUE) echo "[S] Requested data for HERO SIDES $side.\n";
  else die("[F] Unexpected problems when requesting database.\n".$conn->error."\n");

  $query_res = $conn->store_result();

  for ($row = $query_res->fetch_row(); $row != null; $row = $query_res->fetch_row()) {
    $result["hero_sides"][$side][] = array (
      "heroid" => $row[0],
      "matches"=> $row[1],
      "winrate"=> $row[2],
      "gpm"    => $row[3],
      "xpm"    => $row[4]
    );
  }

  $query_res->free_result();
}
?>
