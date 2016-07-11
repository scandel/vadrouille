<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Person;
use AppBundle\Entity\User;
use AppBundle\Entity\Guest;
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
     * Action called via Ajax : set a session variable saying it is ok
     * to post a trip as guest (so he doesn't see annoying notices again...)
     *
     * @Route("/mode-invite-ok", name="covoiturage_post_as_guest")
     */
    public function postAsGuestAction()
    {
        $isAjax = $this->get('Request')->isXMLHttpRequest();
        if ($isAjax) {
            $this->get('session')->set('post_as_guest',true);
        }
        return new Response();
    }

    /**
     * Creates a new Trip entity.
     *
     * @Route("/new", name="covoiturage_new")
     */
    public function newAction(Request $request)
    {
        $trip = new Trip();

        // If a user is connected, he/she is the owner of the trip
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->getUser();
            $trip->setPerson($user->getPerson());
        }
        // If not, then we create an empty guest which will be the owner of the trip
        else {
            $trip->setPerson(new Person(new Guest()));
        }

        // On ajoute deux arrêts vides pour qu'ils soient affichés
        $stop1 = new Stop();
        $stop1->setDelta(0);
        $trip->getStops()->add($stop1);
        $stop2 = new Stop();
        $stop2->setDelta(1);
        $trip->getStops()->add($stop2);

        // Création du "flow" (formulaire en plusieurs étapes)
        $flow = $this->get('app.form.flow.trip');
        $flow->bind($trip);

        // form of the current step
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // flow finished
                $em = $this->getDoctrine()->getManager();
                $em->persist($trip);

                foreach ($trip->getStops() as $stop) {
                    $stop->setTrip($trip);
                    if ($stop->getLat() == null || $stop->getLng() == null) {
                        // set lat and lng to the ones of the city
                        $stop->setLat($stop->getCity()->getLat());
                        $stop->setLng($stop->getCity()->getLng());
                    }
                    $em->persist($stop);
                }

                $em->flush();

                $flow->reset(); // remove step data from the session

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Votre annonce a bien été créée.'
                );

                return $this->redirect($this->generateUrl(
                    'covoiturage_edit',
                    array('id' => $trip->getId())));
            }
        }

        $flowStep = $flow->getCurrentStepNumber();
        return $this->render('pages/trip/edit-'.$flowStep.'.html.twig', array(
            'form' => $form->createView(),
            'flow' => $flow,
            'trip' => $trip,
        ));
    }

    /**
     * Displays a form to edit an existing Trip entity.
     *
     * @Route("/{id}/edit", name="covoiturage_edit")
      */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $trip = $em->getRepository('AppBundle:Trip')->find($id);

        if (!$trip) {
            throw $this->createNotFoundException('Unable to find Trip entity.');
        }

        // Change l'ordre des étapes : met l'arrivée en deuxième
        $nstops = $trip->getStops()->count();
        if ($nstops > 2) {
            $arr = $trip->getStops()->remove($nstops-1);
            for ($i=$nstops-1; $i>1; $i--) {
                $trip->getStops()->set($i, $trip->getStops()->get($i-1)) ;
            }
            $trip->getStops()->set(1,$arr);
        }

        // Création du "flow" (formulaire en plusieurs étapes)
        $flow = $this->get('app.form.flow.trip');
        $flow->bind($trip);

        // form of the current step
        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {

                // Traite les étapes
                foreach ($trip->getStops() as $stop) {
                    if ($stop->getLat() == 0 || $stop->getLng() == 0) {
                        // set lat and lng to the ones of the city
                        $stop->setLat($stop->getCity()->getLat());
                        $stop->setLng($stop->getCity()->getLng());
                    }
                    // Useful when a new stop is added
                    $stop->setTrip($trip);
                    $em->persist($stop);
                }

                // Enlève de la bse les stops qui ont été enlevés
                // -- les n°s des stops du formulaire
                $stops_form = array();
                foreach ($trip->getStops() as $stop) {
                    if (is_numeric($stop->getId()))
                        $stops_form[] = $stop->getId();
                }

                // -- les n°s des stops dans la base
                $query = $em->createQuery(
                    'SELECT s.id
                    FROM AppBundle:Stop s
                    WHERE s.trip = :trip_id'
                )->setParameter('trip_id', $trip->getId());

                $results = $query->getResult();
                $stops_base = array();
                foreach ($results as $res) {
                    $stops_base[] = $res['id'];
                }

                // -- ceux qu'il faut retirer : ceux qui sont dans la base mais pas dans le formulaire
                $to_remove = array_values(array_diff($stops_base, $stops_form));

                foreach ($to_remove as $stop_id) {
                    $stop = $em->getRepository('AppBundle:Stop')->find($stop_id);
                    $em->remove($stop);
                }

                // Remet dans l'ordre ceux qui restent, et les renumérote
                $trip->orderStops(true);

                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Votre annonce a bien été modifiée.'
                );

                return $this->redirect($this->generateUrl('covoiturage_edit', array('id' => $trip->getId())));
            }
        }

        $flowStep = $flow->getCurrentStepNumber();
        return $this->render('pages/trip/edit-'.$flowStep.'.html.twig', array(
            'form' => $form->createView(),
            'flow' => $flow,
            'trip' => $trip,
        ));
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
     * city1 and city2 parameters are city slugs of the trip stops.
     * id is trip id.
     *
     * @Route("/{city1}/{city2}/{id}", name="covoiturage_view")
     */
    public function viewAction($city1, $city2, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $trip = $em->getRepository('AppBundle:Trip')->find($id);

        /*if (!$trip) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }*/

        return $this->render('pages/trip/view.html.twig', array(
            'trip' => $trip,
        ));
    }

    /**
     * Lists all Trip entities.
     * Paginated (?page=x)
     *
     * @Route("/", name="covoiturage_find")
     */
    public function listAction(Request $request)
    {
        // Page of results
        $page = $request->query->get('page',1);

        $tripSearch = new TripSearch();

        $tripSearchForm =  $this->createForm('app_trip_search', $tripSearch, array(
            'action' => $this->generateUrl('covoiturage_find'),
            'method' => 'POST',
        ));

        $tripSearchForm->handleRequest($request);
        $tripSearch = $tripSearchForm->getData();

        $em = $this->getDoctrine()->getManager();
        $maxTrips = $this->container->getParameter('max_trips_search_page');
        // Paginated results here !
        $trips = $em->getRepository('AppBundle:Trip')->search($tripSearch, $page, $maxTrips);

        $pagination = array(
            'route' => 'covoiturage_find', // Todo : replace by the actual route (ex covoiturage/paris/lyon)
            'route_params' => array(),
            'word' =>'Trajets',
            'total' => count($trips), // total of query results (not only those listed on the page)
            'page' => $page,
            'pages_count' => ceil(count($trips) / $maxTrips),
            'per_page' => $maxTrips
        );

        return $this->render('pages/trip/list.html.twig', array(
            'h1' => "Tous les covoiturages",
            'form' => $tripSearchForm->createView(),
            'trips' => $trips,
            'pagination' => $pagination,
        ));

    }

}
