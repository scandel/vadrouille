<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Entity\Car;
use AppBundle\Entity\CarBrand;
use AppBundle\Entity\CarModel;
use AppBundle\Form\Car\CarType;

/**
 * Class CarController
 * @package AppBundle\Controller
 *
 * @Route("/membres/voiture")
 */
class CarController extends Controller
{

    /**
     * List all cars
     *
     * @Route("/", name="car_list")
     */
    public function listAction()
    {
        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $cars = $this->getDoctrine()
            ->getRepository('AppBundle:Car')
            ->findByUser($user);

        return $this->render('pages/car/list.html.twig',
            array('cars' => $cars));
    }


    /**
     * @Route("/nouvelle", name="car_new")
     */
    public function newAction(Request $request)
    {
        $car = new Car();
        $form = $this->createForm(new CarType(), $car);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Adds user relationship
            $user = $this->getUser();
            if (!is_object($user)) {
                throw new AccessDeniedException('This user does not have access to this section.');
            }
            $car->setUser($user);

            // writes the car to the database
            $em = $this->getDoctrine()->getManager();
            $em->persist($car);
            $em->flush();

            $name = $car->getBrand()->getName().' '.$car->getModel()->getName();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Votre voiture <strong>"'.$name.'"</strong> a bien été enregistrée.'
            );

            return $this->redirect($this->generateUrl('car_list'));
        }

        return $this->render('pages/car/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/modifier", name="car_edit")
     * @param $id
     */
    public function editAction($id, Request $request)
    {
        $car = $this->getDoctrine()
            ->getRepository('AppBundle:Car')
            ->find($id);

        if (!$car) {
            throw $this->createNotFoundException('Pas de voiture avec l\'id : '.$id);
        }

        // Check if current user owns this car
        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($car->getUser() != $user) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }


        $form = $this->createForm(new CarType(), $car);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // writes the activity to the database
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Vos changements ont bien été sauvegardés.'
            );

            return $this->redirect($this->generateUrl('car_list'));
        }

        return $this->render('pages/car/edit.html.twig', array(
            'form' => $form->createView(),
        ));

    }

    /**
     * @Route("/{id}/supprimer", name="car_delete")
     * @param $id
     */
    public function deleteAction($id)
    {
        $car = $this->getDoctrine()
            ->getRepository('AppBundle:Car')
            ->find($id);

        if (!$car) {
            throw $this->createNotFoundException('Pas de voiture avec l\'id : '.$id);
        }

        // Check if current user owns this car
        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if ($car->getUser() != $user) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $name = $car->getBrand()->getName().' '.$car->getModel()->getName();

        $em = $this->getDoctrine()->getManager();
        $em->remove($car);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            "La voiture <strong>\"$name\"</strong> a été supprimée."
        );

        return $this->redirect($this->generateUrl('car_list'));
    }


}
