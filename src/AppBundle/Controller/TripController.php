<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Trip;
use AppBundle\Entity\Stop;
use AppBundle\Entity\TripSearch;
use AppBundle\Form\Trip\TripType;
use AppBundle\Form\Trip\TripSearchType;


/**
 * Trip controller.
 *
 * @Route("/covoiturage")
 */
class TripController extends Controller
{

    /**
     * Creates a new Trip entity.
     *
     * @Route("/new", name="covoiturage_new")
     */
    public function newAction(Request $request)
    {
        $entity = new Trip();

        // On ajoute deux arrêts vides pour qu'ils soient affichés
        $stop1 = new Stop();
        $entity->getStops()->add($stop1);
        $stop2 = new Stop();
        $entity->getStops()->add($stop2);

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);

            // Remet les étapes dans l'ordre (Départ - Etapes 1..n - Arrivée)
            $nstops = $entity->getStops()->count();
            if ($nstops > 2) {
                $arr = $entity->getStops()->remove(1);
                $entity->getStops()->add($arr);
            }

            $delta = 1;
            foreach ($entity->getStops() as $stop) {
                $stop->setTrip($entity);
                $stop->setDelta($delta);
                $delta++;
                if ($stop->getLat() == null || $stop->getLng() == null) {
                    // set lat and lng to the ones of the city
                    $stop->setLat($stop->getCity()->getLat());
                    $stop->setLng($stop->getCity()->getLng());
                }
                $em->persist($stop);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Votre annonce a bien été créée.'
            );

            return $this->redirect($this->generateUrl(
                'covoiturage_edit',
                array('id' => $entity->getId())));
        }

        return $this->render('pages/trip/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Creates a form to create a Trip entity.
     *
     * @param Trip $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Trip $entity)
    {
        $form = $this->createForm(new TripType(), $entity, array(
            'action' => $this->generateUrl('covoiturage_new'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Poster votre covoiturage'));

        return $form;
    }


    /**
     * Displays a form to edit an existing Trip entity.
     *
     * @Route("/{id}/edit", name="covoiturage_edit")
      */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Trip')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Trip entity.');
        }

        // Change l'ordre des étapes : met l'arrivée en deuxième
        $nstops = $entity->getStops()->count();
        if ($nstops > 2) {
            $arr = $entity->getStops()->remove($nstops-1);
            for ($i=$nstops-1; $i>1; $i--) {
                $entity->getStops()->set($i, $entity->getStops()->get($i-1)) ;
            }
            $entity->getStops()->set(1,$arr);
        }

        $form = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        $form->handleRequest($request);

        if ($form->isValid()) {
            // Remet les étapes du formulaire dans l'ordre (Départ - Etapes 1..n - Arrivée)
            $nstops = $entity->getStops()->count();
            if ($nstops > 2) {
                $arr = $entity->getStops()->remove(1);
                $entity->getStops()->add($arr);
            }

            $delta = 1;
            foreach ($entity->getStops() as $stop) {
                $stop->setTrip($entity);
                $stop->setDelta($delta);
                $delta++;
                if ($stop->getLat() == 0 || $stop->getLng() == 0) {
                    // set lat and lng to the ones of the city
                    $stop->setLat($stop->getCity()->getLat());
                    $stop->setLng($stop->getCity()->getLng());
                }
                // $em->persist($stop);
            }

            // Enlève de la bse les stops qui ont été enlevés
            // -- les n°s des stops du formulaire
            $stops_form = array();
            foreach ($entity->getStops() as $stop) {
                if (is_numeric($stop->getId()))
                    $stops_form[] = $stop->getId();
            }

            // -- les n°s des stops dans la base
            $query = $em->createQuery(
                'SELECT s.id
                FROM AppBundle:Stop s
                WHERE s.trip = :trip_id'
            )->setParameter('trip_id',$entity->getId());

            $results = $query->getResult();
            $stops_base = array();
            foreach($results as $res) {
                $stops_base[] = $res['id'];
            }

            // -- ceux qu'il faut retirer : ceux qui sont dans la base mais pas dans le formulaire
            $to_remove = array_values(array_diff($stops_base, $stops_form));

            foreach($to_remove as $stop_id) {
                $stop = $em->getRepository('AppBundle:Stop')->find($stop_id);
                $em->remove($stop);
            }

            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'Votre annonce a bien été modifiée.'
            );

            return $this->redirect($this->generateUrl('covoiturage_edit', array('id' => $entity->getId())));
        }

        return $this->render('pages/trip/edit.html.twig', array(
            'form' => $form->createView()
        ));
     }

    /**
    * Creates a form to edit a Trip entity.
    *
    * @param Trip $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Trip $entity)
    {
        $form = $this->createForm(new TripType(), $entity, array(
            'action' => $this->generateUrl('covoiturage_edit', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Modifier votre covoiturage'));

        return $form;
    }
    /**
     * Deletes a Trip entity.
     *
     * @Route("/{id}/delete", name="covoiturage_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Trip')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Trip entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('covoiturage_find'));
    }

    /**
     * Finds and displays a Trip entity.
     *
     * @Route("/{id}", name="covoiturage_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Trip')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Trip entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Lists all Trip entities.
     *
     * @Route("/", name="covoiturage_find")
     */
    public function listAction(Request $request)
    {
        $tripSearch = new TripSearch();

        $tripSearchForm =  $this->createForm('app_trip_search', $tripSearch, array(
            'action' => $this->generateUrl('covoiturage_find'),
            'method' => 'POST',
        ));

        $tripSearchForm->handleRequest($request);
        $tripSearch = $tripSearchForm->getData();

        $em = $this->getDoctrine()->getManager();
        $trips = $em->getRepository('AppBundle:Trip')->search($tripSearch);

        return $this->render('pages/trip/list.html.twig', array(
            'h1' => "Tous les covoiturages",
            'trips' => $trips,
            'form' => $tripSearchForm->createView()
        ));

    }

}
