<?php

namespace AppBundle\Twig;


/**
 * Twig extension.
 * Defines the following filters :
 * day : convert number of day (1,2,...7) to its name in default locale (lundi, mardi, etc...).
 * dayjoin :
 *
 * Class AppExtension
 * @package AppBundle\Twig
 */
class AppExtension extends \Twig_Extension {

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('day', array($this, 'dayFilter')),
            new \Twig_SimpleFilter('dayjoin', array($this, 'dayJoinFilter')),
        );
    }

    /**
     * Returns day of week name based on number of day (1..7)
     *
     * @param $number
     * @param bool|false $plural : use plural ?
     * @return string
     */
    public static function dayFilter($number, $plural=false)
    {
        $daysOfWeek = array(
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
            7 => 'dimanche',
        );

        if ($number < 1 || $number > 7) {
            return '';
        }

        return $daysOfWeek[$number] . ($plural ? 's' : '');
    }

    /**
     * Returns a list of days of weeks glued by $glue.
     *
     * @param $days : array of days of week numbers.
     * @param string $glue
     * @return string
     */
    public static function dayJoinFilter($days, $glue=',', $plural=false)
    {
        $days = array_map('AppBundle\Twig\AppExtension::dayFilter',$days, array_fill(0,count($days),$plural) );
        return implode($glue, $days);
    }

    public function getName()
    {
        return 'app_extension';
    }
}
