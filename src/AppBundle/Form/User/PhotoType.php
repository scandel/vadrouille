<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PhotoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $builder->getForm()->getData();

        $builder->add('photo','comur_image', array(
            'uploadConfig' => array(
                'uploadUrl' => $user->getUploadRootDir(),
                'webDir' => $user->getUploadDir(),
                'saveOriginal' => 'originalPhoto',
                'showLibrary' => false, // don't show images already uploaded
            ),
            'cropConfig' => array(
                'minWidth' => 150,
                'minHeight' => 150,
                'aspectRatio' => true,
                'forceResize' => false,
                'thumbs' => array(
                    array(
                        'maxWidth' => 600,
                        'maxHeight' => 600,
                    ),
                    array(
                        'maxWidth' => 300,
                        'maxHeight' => 300,
                        'useAsFieldImage' => true  //optional
                    ),
                    array(
                        'maxWidth' => 100,
                        'maxHeight' => 100,
                    ),
                )
            )
        ));

    }

    public function getName()
    {
        return 'app_user_photo';
    }
}