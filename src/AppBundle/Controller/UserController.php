<?php

namespace AppBundle\Controller;

use AppBundle\Form\User\PhotoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Entity\User;

class UserController extends Controller
{
    /**
     * Edit the user's photo
     * @Route("/profile/photo", name="user_photo_edit")
     */
    public function editPhotoAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createForm(new PhotoType(), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $user->setUpdatedAt(new \DateTime());
            $userManager->updateUser($user);

            $url = $this->generateUrl('user_photo_edit');
            $response = new RedirectResponse($url);
            return $response;
        }

        return $this->render('pages/user/photo.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Deactivate (unsubscribe) a user
     * @Route("/profile/unsubscribe", name="user_unsubscribe")
     */
    public function unsubscribeAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->createFormBuilder()
            ->add('unsubscribe', 'checkbox', array(
                'label' => "Je souhaite me désinscrire du service ; mon profil, ainsi
                 que toutes mes offres de covoiturage, seront supprimés du site."
            ))
            ->add('message', 'textarea', array(
                'required' => false,
                'label' => 'Pourquoi souhaitez-vous vous désinscrire ? (optionnel,
                 votre réponse nous aide à améliorer le site)'
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // data is an array with "unsubscribe",  and "message" keys
            $data = $form->getData();
            if ($data['unsubscribe']) {
                // Deactivate user
                $manipulator = $this->get('fos_user.util.user_manipulator');
                $manipulator->deactivate($user->getUsername());


                //== Complete Log out
                // (see http://stackoverflow.com/a/28828377/2761700 )
                // Logging user out.
                $this->get('security.token_storage')->setToken(null);

                // Invalidating the session.
                $session = $this->get('request')->getSession();
                $session->invalidate();

                // Redirecting user to login page in the end.
                $response = $this->redirectToRoute('homepage');

                // Clearing the cookies.
                /*$cookieNames = [
                    $this->container->getParameter('session.name'),
                    $this->container->getParameter('session.remember_me.name'),
                ];
                foreach ($cookieNames as $cookieName) {
                    $response->headers->clearCookie($cookieName);
                }*/

                // Notice it
                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Vous avez été désinscrit du site !'
                );
                return $response;
            }
            else {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'Pour vous désinscrire, merci de cocher la case de désinscription'
                );
            }
        }

        return $this->render('pages/user/unsubscribe.html.twig', array(
            'form' => $form->createView()
        ));
    }




}


