<?php
function check_directory($dir) {
    echo("[ ] Checking directory `$dir`\n");
    if ( !is_dir($dir) ) {
        if ( file_exists($dir) )
            die("[F] File named `$dir` exists in working directory. You should move or rename it.\n");
        mkdir($dir);
        echo("[S] Created directory `$dir`\n");
        return false;
    } else {
        echo("[ ] Directory `$dir` exists\n");
        return true;
    }
}
?>
