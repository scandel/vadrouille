<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Form\City\CityType;

/**
 * Class CityController
 * @package AppBundle\Controller
 *
 * @Route("/ville")
 *
 */
class CityController extends Controller
{
    /**
     * Returns a list of cities which name (one of its names) begins with
     * first letters given
     *
     * @Route("/completer/{firstLetters}", name="city_complete")
    */
    public function completeAction($firstLetters)
    {
        $em = $this->getDoctrine()->getManager();
        // Normalise first letters
        $firstLetters =  $this->get('app.slug')->genericSlug($firstLetters);
        // Search
        $cities = $em->getRepository('AppBundle:City')->searchByFirstLetters($firstLetters);
        $locale = $this->get('translator')->getLocale();

        $json = array();
        foreach($cities as $city) {

            $cityName = $city->getMainName($locale)->getName() ;
            $countryCode = $city->getCountry()->getCode();
            if ($countryCode == "FR") {
                $json[] = array(
                    "id" => $city->getId(),
                    "name" => $cityName,
                    "postcode" => $city->getPostCode(),
                    "country" => $countryCode,
                );
            }
            else {
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


    /**
     * @Route("/chercher", name="city_search")
     */
    public function findAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new CityType($em));
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form['city']->getData() != null) {
                $city = $form['city']->getData();
                if ($city) {
                    $locale = $this->get('translator')->getLocale();
                    $this->get('session')->getFlashBag()->add(
                        'success',
                        'Ville trouvée : ' . $city->getMainName($locale)->getName()
                    );
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'danger',
                        'Ville non trouvée !'
                    );
                }
            }
            else {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'Choisissez une ville !'
                );
            }
        }

        return $this->render('pages/basic/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
