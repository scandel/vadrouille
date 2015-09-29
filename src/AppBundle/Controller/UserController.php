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
}


