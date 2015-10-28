<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class CityController extends Controller
{
    /**
     * Returns a list of cities which name (one of its names) begins with
     * first letters given
     *
     * @Route("/city/complete/{firstLetters}", name="city_complete")
    */
    public function completeAction($firstLetters)
    {
        $em = $this->getDoctrine()->getManager();
        $cities = $em->getRepository('AppBundle:City')->searchByFirstLetters($firstLetters);
        $locale = $this->get('translator')->getLocale();

        $json = array();
        foreach($cities as $city) {

            $cityName = $city->getMainName($locale)->getName() ;
            $countryCode = $city->getCountry()->getCode();
            if ($countryCode == "FR")
            {
                $json[] = array(
                    "id" => $city->getId(),
                    "name" => $cityName,
                    "postcode" => $city->getPostCode(),
                    "country" => $countryCode,
                );
            }
            else {
                // Todo : complete when not in France

                /* $req = "SELECT * FROM Countries WHERE code = '$cocode'";
                $city_country = $db->objetSuivant($db->execRequete($req))->french_name;
                if ($city->french_name != "" && $city->french_name != $city->name)
                    $par = $city->name.', '.$city_country.', '.$city->postal_code;
                else
                    $par = $city_country.', '.$city->postal_code; */
                $json[] = array(
                    "id" => $city->getId(),
                    "name" => $cityName,
                    "postcode" => $city->getCountry()->getName().', '.$city->getPostCode(),
                    "country" => $countryCode,
                );
            }
        }

        return new JsonResponse($json);
    }
}
