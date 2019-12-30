<?php
$result["teams"][$id]["players_draft"] = rg_query_player_draft($conn, null, $id);

# SUPPORTING: pickban context for teams

$result['teams'][$id]["players_draft_pb"] = rg_query_player_draft_pickban($conn, $id);
