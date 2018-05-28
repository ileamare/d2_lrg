<?php
 $dir = scandir("leagues");
 $silent = false;

 foreach($dir as $league) {
   if($league[0] == ".")
       continue;
   $name = str_replace(".json", "", $league);

   echo "[ ]\t Analyzing `$name`\n";

    `php rg_analyzer.php -l$name`;
 }
 ?>
