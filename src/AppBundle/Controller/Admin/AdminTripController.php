<?php

namespace AppBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Trip;
use AppBundle\Form\Trip\TripSearchType;


/**
 * Admin Trip controller.
 *
 * @Route("admin/covoiturage")
 */
class AdminTripController extends Controller
{

    /**
     * Refresh all the nextDepDatetimes .
     *
     * @Route("/refresh", name="admin_covoiturage_refresh")
     */
    public function refreshAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $currentTrips = $em->getRepository('AppBundle:Trip')->findByCurrent(true);

        foreach($currentTrips as $trip) {
            $trip->computeNextDateTime();
            $em->flush();
        }

        return new Response(
            count($currentTrips) . " trajets traités."
        );
    }

}
