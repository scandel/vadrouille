<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('pages/homepage.html.twig', array(
            'name' => 'Séverine',
        ));
    }

    /**
     * @Route("/test/{count}", requirements={ "count": "\d+" }, defaults={"count" = 1}, name="test")
     */
    public function testAction($count)
    {
        $numbers = array();
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = rand(0, 100);
        }
        $numbersList = implode(', ', $numbers);


        return new Response(
            '<html><body>Lucky numbers: '.$numbersList.'</body></html>'
        );
    }



}
