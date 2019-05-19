<?php
function GetLanguageCodeISO6391() {
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return 'en';
    $hi_code = "";
    $hi_quof = 0;
    $langs = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach($langs as $lang) {
        if(strpos($lang, ";"))
            list($codelang, $quoficient) = explode(";",$lang);
        else
            list($codelang, $quoficient) = [ $lang, NULL ];

        if($quoficient == NULL) $quoficient = 1;
        if($quoficient > $hi_quof) {
            $hi_code = substr($codelang,0,2);
            $hi_quof = $quoficient;
        }
    }
    return $hi_code;
}
?>
