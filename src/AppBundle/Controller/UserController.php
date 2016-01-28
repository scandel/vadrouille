<?php

namespace AppBundle\Controller;

use AppBundle\Form\User\PhotoType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * Confirm the email of a user
     * @Route("/profile/confirm/{token}", name="user_confirm_email")
     */
    public function confirmEmailAction(Request $request, $token) {

        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEmailConfirmed(new \DateTime());
        $userManager->updateUser($user);

        // Notice
        $this->get('session')->getFlashBag()->add(
            'success',
            'Merci, votre adresse email est maintenant confirmée.'
        );
        $url = $this->generateUrl('fos_user_profile_edit');
        return new RedirectResponse($url);
    }

    /**
     * Send a confirmation mail (to check email) to a user
     * @Route("/profile/email/confirm", name="user_send_confirmation_email")
     */
    public function sendConfirmationMailAction()
    {
        $userManager = $this->get('fos_user.user_manager');

        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
            $userManager->updateUser($user);
        }
        $this->sendWelcomeEmail($user);
        $this->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());

        $response = array("message" => "Mail de confirmation envoyé", "success" => true);
        return new Response(json_encode($response));
    }

    // Send welcome and email confirmation email
    public function sendWelcomeEmail($user)
    {
        $url = $this->get('router')->generate('user_confirm_email',
            array('token' => $user->getConfirmationToken()),
            UrlGeneratorInterface::ABSOLUTE_URL);

        $this->get('app.mail_manager')->sendEmail("email-confirmation",
            array(
                'user' => $user,
                'url' => $url,
            ),
            $user->getEmail());
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


