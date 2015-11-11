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
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new StopType($em), $stop);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $stop->setTrip(null);

            if ($stop->getCity()) {
                $em->persist($stop);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'L\'étape a bien été enregistrée.'
                );

                return $this->redirect($this->generateUrl('stop_new'));
            }
            else {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'Choisissez une ville !'
                );
            }
        }
        return $this->render('pages/stop/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/stop/edit/{id}", name="stop_edit")
    */
    public function editAction( $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $stop =  $em->getRepository('AppBundle:Stop')->find($id);
        $form = $this->createForm(new StopType($em), $stop);
        $form->handleRequest($request);

        if ($form->isValid()) {

            if ($stop->getCity()) {
                $em->persist($stop);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'L\'étape a bien été enregistrée.'
                );

                // return $this->redirect($this->generateUrl('stop_new'));
            }
            else {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'Choisissez une ville !'
                );
            }
        }
        return $this->render('pages/stop/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
