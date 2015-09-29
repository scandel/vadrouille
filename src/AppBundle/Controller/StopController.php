<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Stop;
use AppBundle\Form\Stop\StopType;

class StopController extends Controller
{
    /**
     * @Route("/stop/new", name="stop_new")
    */
    public function newAction(Request $request)
    {
        $stop = new Stop();
        $form = $this->createForm(new StopType(), $stop);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $stop->setTrip(null);

            // writes the stop to the database
            $em = $this->getDoctrine()->getManager();
            $em->persist($stop);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'L\'étape a bien été enregistrée.'
            );

            return $this->redirect($this->generateUrl('stop_new'));
        }

        return $this->render('pages/stop/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
