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
use Symfony\Component\Validator\Constraints\DateTime;


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

        return $this->redirect($this->generateUrl('covoiturage_all'));
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

        if (!$trip) {
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        // Retrieve dep and arr Cities and stop.delta
        $depCityName = $em->getRepository('AppBundle:CityName')->findOneBySlug($city1);
        $arrCityName = $em->getRepository('AppBundle:CityName')->findOneBySlug($city2);

        $depDelta = $arrDelta = null;
        foreach ($trip->getStops() as $stop) {
            if ($stop->getCity()->getId() == $depCityName->getCity()->getId()) {
                $depDelta = $stop->getDelta();
            }
            else if ($stop->getCity()->getId() == $arrCityName->getCity()->getId()) {
                $arrDelta = $stop->getDelta();
            }
        }

        if (is_null($depDelta) || is_null($arrDelta)) {
            // Vague message for not showing indications to bots
            throw $this->createNotFoundException('Covoiturage non trouvé.');
        }

        return $this->render('pages/trip/view.html.twig', array(
            'trip' => $trip,
            'depDelta' => $depDelta,
            'arrDelta' => $arrDelta,
            'depCityName' => $depCityName,
            'arrCityName' => $arrCityName,
        ));
    }

    /**
     * Lists trip entities based on search query,
     * parameters passed in url.
     * Paginated (?page=x)
     *
     * URL forms :
     * /covoiturage/slug-dep_city/slug-arr-city
     * /covoiturage/depart/slug-dep_city
     * /covoiturage/arrivee/slug-arr-city
     * /covoiturage  --> all trips
     *
     * Parameters :
     * ?date=YYYYmmdd (default '')
     * ?page=n (default 1)
     *
     * @Route("/{city1}/{city2}", name="covoiturage_from_to")
     * @Route("/depart/{city1}", defaults={"city2" = ""}, name="covoiturage_from")
     * @Route("/arrivee/{city2}", defaults={"city1" = ""}, name="covoiturage_to")
     * @Route("/", defaults={"city1" = "", "city2" = ""}, name="covoiturage_all")
     */
    public function listAction($city1, $city2, Request $request)
    {
        // Page of results (query parameter)
        $page = $request->query->get('page',1);

        // Date (query parameter)
        $dateString = $request->query->get('date','');
        $date = ($dateString) ? new \DateTime($dateString) : '';

        // Dep and arr cities: fill the TripSearch object
        $em = $this->getDoctrine()->getManager();

        $tripSearch = new TripSearch();

        if ($city1 == 'depart' && !empty($city2)) {
            $depCityName = $em->getRepository('AppBundle:CityName')->findOneBySlug($city2);
            if ($depCityName) {
                $tripSearch->setDepCity($depCityName->getCity());
                $h1 = "Covoiturages depuis " . $depCityName->getName();
            }
            else {
                // todo : message flash d'erreur
            }
        }
        else if ($city1 == 'arrivee' && !empty($city2)) {
            $arrCityName = $em->getRepository('AppBundle:CityName')->findOneBySlug($city2);
            if ($arrCityName) {
                $tripSearch->setArrCity($arrCityName->getCity());
                $h1 = "Covoiturages vers " . $arrCityName->getName();
            }
            else {
                // todo : message flash d'erreur
            }
        }
        else if (!empty($city1) && !empty($city2)) {
            $depCityName = $em->getRepository('AppBundle:CityName')->findOneBySlug($city1);
            if ($depCityName) {
                $tripSearch->setDepCity($depCityName->getCity());
                $h1 = "Covoiturages " . $depCityName->getName() ;
            }
            else {
                // todo : message flash d'erreur
            }
            $arrCityName = $em->getRepository('AppBundle:CityName')->findOneBySlug($city2);
            if ($arrCityName) {
                $tripSearch->setArrCity($arrCityName->getCity());
                $h1 .= " " . $arrCityName->getName();
            }
            else {
                // todo : message flash d'erreur
            }
        }
        else {
            $h1 = "Tous les covoiturages";
        }

        if (!empty($date)) {
            $tripSearch->setDate($date);
        }

        // Current route & parameters, without page query parameter
        $routeName = $request->get('_route');

        $queryString = $request->getQueryString();
        parse_str($queryString,$queryParams);
        if (isset($queryParams['page'])) {
            unset($queryParams['page']);
        }
        $queryParams['city1'] = $city1;
        $queryParams['city2'] = $city2;

        $tripSearchForm =  $this->createForm('app_trip_search', $tripSearch, array(
            'action' => $this->generateUrl('covoiturage_search_rewrite'),
            'method' => 'POST',
        ));

        $em = $this->getDoctrine()->getManager();
        $maxTrips = $this->container->getParameter('max_trips_search_page');
        // Paginated results here !
        // each result has a "trip", and can have a "dep" (delta of departure stop"), and a "arr" (delta...)
        $results = $em->getRepository('AppBundle:Trip')->search($tripSearch, $page, $maxTrips);

        $pagination = array(
            'route' => $routeName,
            'route_params' => $queryParams,
            'word' =>'Trajets',
            'total' => count($results), // total of query results (not only those listed on the page)
            'page' => $page,
            'pages_count' => ceil(count($results) / $maxTrips),
            'per_page' => $maxTrips
        );

        return $this->render('pages/trip/list.html.twig', array(
            'h1' => $h1,
            'form' => $tripSearchForm->createView(),
            'results' => $results,
            'depDate' => $date,
            'pagination' => $pagination,
        ));
    }

    /**
     * Rewrites search url according to search form
     *
     * @Route("/recherche", name="covoiturage_search_rewrite")
     * @Method({"GET", "POST"})
     */
    public function rewriteSearchUrl(Request $request) {
        // Handles form
        $tripSearch = new TripSearch();
        $tripSearchForm =  $this->createForm('app_trip_search', $tripSearch, array());
        $tripSearchForm->handleRequest($request);

        if ($tripSearchForm->isValid()) {
            $tripSearch = $tripSearchForm->getData();

            //== dep and arr cities

            $dep = ($tripSearch->getDepCity()) ? true : false;
            $arr = ($tripSearch->getArrCity()) ? true : false;

            if ($dep && $arr) {
                $route = 'covoiturage_from_to';
                $params = array(
                    'city1' => $tripSearch->getDepCity()->getSlug(),
                    'city2' => $tripSearch->getArrCity()->getSlug()
                );
            }
            else if ($dep) {
                $route = 'covoiturage_from';
                $params = array(
                    'city1' => $tripSearch->getDepCity()->getSlug()
                );
            }
            else if ($arr) {
                $route = 'covoiturage_to';
                $params = array(
                    'city2' => $tripSearch->getArrCity()->getSlug()
                );
            }
            else {
                $route = 'covoiturage_all';
                $params = array();
            }

            //== add date as query parameter
            if ($date = $tripSearch->getDate()) {
                $params['date'] = $tripSearch->getDate()->format('Y-m-d');
            }

            return $this->redirect($this->generateUrl($route, $params));
        }

        return $this->render('pages/trip/list.html.twig', array(
            'h1' => "Recherche de covoiturages",
            'form' => $tripSearchForm->createView(),
            'results' => null,
            'pagination' => array(
                'total' => 0
            ),
        ));

    }


}
