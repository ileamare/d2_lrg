<?php

$visjs_settings = "physics:{
  barnesHut:{
    avoidOverlap:1,
    centralGravity:0.3,
    springLength:95,
    springConstant:0.005,
    gravitationalConstant:-900
  },
  timestep: 0.1,
}, nodes: {
   borderWidth:3,
   shape: 'dot',
   font: {color:'#ccc', background: 'rgba(0,0,0,0.5)',size:12},
   shadow: {
     enabled: true
   },
   scaling:{
     label: {
       min:8, max:20
     }
   }
 }";

$level_codes = array(
  # level => array( class-postfix, class-level )
  0 => array ( "", "higher-level" ),
  1 => array ( "sublevel", "lower-level" ),
  2 => array ( "level-3", "level-3" ),
  3 => array ( "level-4", "level-4" )
);

$charts_colors = array( "#6af","#f66","#fa6","#66f","#62f","#a6f","#6ff","#6fa","#2f6","#6f2","#ff6","#f22","#f6f","#666" );

?>
