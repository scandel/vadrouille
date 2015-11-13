<?php

namespace AppBundle\Entity;

class TripSearch
{
     /**
     * @var City : departure city
     */
    private $depCity = null;

    /**
     * @var City  : arrival city
     */
    private $arrCity = "";

    /**
     * Set depCity
     *
     * @param City $depCity
     * @return TripSearch
     */
    public function setDepCity($depCity)
    {
        $this->depCity = $depCity;
        return $this;
    }

    /**
     * Get depCity
     *
     * @return City
     */
    public function getDepCity()
    {
        return $this->depCity;
    }

    /**
     * Set arrCity
     *
     * @param City $arrCity
     * @return TripSearch
     */
    public function setArrCity($arrCity)
    {
        $this->arrCity = $arrCity;

        return $this;
    }

    /**
     * Get arrCity
     *
     * @return City
     */
    public function getArrCity()
    {
        return $this->arrCity;
    }
}
