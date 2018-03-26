<?php

namespace DirectokiBundle;


/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class URLTools
{

    public function getListOfURLsInText($text) {
        $out = array();
        foreach(explode(' ', str_replace(["\n","\t"], [' ',' '], $text)) as $possibleURL) {
            $possibleURL = trim($possibleURL);
            if ($possibleURL && filter_var($possibleURL, FILTER_VALIDATE_URL)) {
                $out[] = $possibleURL;
            }
        }
        return $out;
    }

}
