<?php

/**
 * Function to calculate element's rank based on
 * Wilson score confidence interval for a Bernoulli parameter
 *
 * Based on: http://www.evanmiller.org/how-not-to-sort-by-average-rating.html
 */

function wilson_rating($positive, $total = 0, $confidence = 0.95) {
  if (!$total) return 0;

  $pnorm = pnormaldist(1-(1-$confidence)/2);

  $percentage = $positive / $total;
  $score = ( $percentage + $pnorm*$pnorm / (2*$total) - $pnorm * sqrt( ( $percentage*(1-$percentage) + $pnorm*$pnorm / (4*$total) )/$total ) ) /
              ( 1 + $pnorm*$pnorm / $total );
  return $score;
}

function pnormaldist($qn) {
  $b = array(
    1.570796288, 0.03706987906, -0.8364353589e-3,
    -0.2250947176e-3, 0.6841218299e-5, 0.5824238515e-5,
    -0.104527497e-5, 0.8360937017e-7, -0.3231081277e-8,
    0.3657763036e-10, 0.6936233982e-12);

  if ($qn < 0.0 || 1.0 < $qn)
    return 0.0;

  if ($qn == 0.5)
    return 0.0;

  $w1 = $qn;

  if ($qn > 0.5)
    $w1 = 1.0 - $w1;

  $w3 = - log(4.0 * $w1 * (1.0 - $w1));
  $w1 = $b[0];

  for ($i = 1;$i <= 10; $i++)
    $w1 += $b[$i] * pow($w3,$i);

  if ($qn > 0.5)
    return sqrt($w1 * $w3);

  return - sqrt($w1 * $w3);
}

function compound_ranking_sort($a, $b, $total_matches) {
  $a_contest = ($a['matches_picked'] + $a['matches_banned'])/$total_matches;
  $b_contest = ($b['matches_picked'] + $b['matches_banned'])/$total_matches;

  $a_oi_rank = ($a['matches_picked']*$a['winrate_picked'] + $a['matches_banned']*$a['winrate_banned']) / $total_matches;
  $b_oi_rank = ($b['matches_picked']*$b['winrate_picked'] + $b['matches_banned']*$b['winrate_banned']) / $total_matches;

  $a_rank = wilson_rating( ($a['matches_picked']*$a['winrate_picked'] + $a['matches_banned']*$a['winrate_banned']/2), ($a['matches_picked'] + $a['matches_banned']/2), 1-$a_contest ) * ($a_oi_rank/4+0.75);
  $b_rank = wilson_rating( ($b['matches_picked']*$b['winrate_picked'] + $b['matches_banned']*$b['winrate_banned']/2), ($b['matches_picked'] + $b['matches_banned']/2), 1-$b_contest ) * ($b_oi_rank/4+0.75);

  if($a_rank == $b_rank) return 0;
  else return ($a_rank < $b_rank) ? 1 : -1;
}

function positions_ranking_sort($a, $b, $total_matches) {
  $a_popularity = $a['matches_s']/$total_matches;
  $b_popularity = $b['matches_s']/$total_matches;

  $a_rank = wilson_rating( $a['matches_s']*$a['winrate_s'], $a['matches_s'], 1-$a_popularity );
  $b_rank = wilson_rating( $b['matches_s']*$b['winrate_s'], $b['matches_s'], 1-$b_popularity );

  if($a_rank == $b_rank) return 0;
  else return ($a_rank < $b_rank) ? 1 : -1;
}

?>
