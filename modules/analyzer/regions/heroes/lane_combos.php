<?php

$result["regions_data"][$region]["hero_lane_combos"] = rg_query_lane_combos($conn, $result['pickban'], $result['random']['matches_total'], $limiter_lower, $clusters);

?>