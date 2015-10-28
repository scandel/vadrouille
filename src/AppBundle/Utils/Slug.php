<?php

namespace AppBundle\Utils;

/**
 * Utilities to slug names : Cities, GeoZones, Trips...
 */
class Slug
{
    /**
     * Performs a generic slug :
     * - strip accents,
     * - replaces spaces and non-alphanumeric characters by hyphens '-'
     *
     * @param $str
     * @return string
     */
    public function genericSlug($str, $c='-'){
        return $this->stripBlanks(
                 strtolower(
                   $this->stripAccents(
                     trim(
                       $str
                     )
                   )
                 ),$c);
    }

    /**
     * Replaces spaces, hyphens, underscores, and quotes by $c (default hyphen)
     *
     * @param $str
     * @param string $c
     * @return string
     */
    public function stripBlanks($str,$c='-'){
        return strtr($str,' _-\'',"$c$c$c$c");
    }

    /**
     * Replaces accents in $str by non-accentued characters
     * src: http://stackoverflow.com/a/3542748/2761700
     * && http://stackoverflow.com/q/1017599/2761700
     *
     * @param $str
     * @return string
     */
    public function stripAccents($str){
        return iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    }

}