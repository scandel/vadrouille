<?php

namespace AppBundle\Entity;

class TripSearch
{
     /**
     * @var string : departure city name
     */
    private $depCityName = "";

    /**
     * @var string  : arrival city name
     */
    private $arrCityName = "";

    /**
     * Set depCityName
     *
     * @param string $depCityName
     * @return TripSearch
     */
    public function setDepCityName($depCityName)
    {
        $this->depCityName = $depCityName;

        return $this;
    }

    /**
     * Get depCityName
     *
     * @return string 
     */
    public function getDepCityName()
    {
        return $this->depCityName;
    }

    /**
     * Set arrCityName
     *
     * @param string $arrCityName
     * @return TripSearch
     */
    public function setArrCityName($arrCityName)
    {
        $this->arrCityName = $arrCityName;

        return $this;
    }

    /**
     * Get arrCityName
     *
     * @return string 
     */
    public function getArrCityName()
    {
        return $this->arrCityName;
    }
}
