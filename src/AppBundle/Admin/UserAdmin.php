<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use libphonenumber\PhoneNumberFormat;

class UserAdmin extends Admin
{

 // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('email')
            ->add('first_name')
            ->add('last_name')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('Photo', 'string', array('template' => 'AppBundle:Admin:image.html.twig'))
            ->add('email')
            ->add('first_name', null, array('editable' => true))
            ->add('last_name', null, array('editable' => true))
            //->add('phone')
            ->add('locked', null, array('editable' => true))
            ->add('lastLogin')
        ;

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
                ->add('Switch', 'string', array('template' => 'AppBundle:Admin:impersonating.html.twig'))
            ;
        }

    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        // get the current User instance
        $user = $this->getSubject();

        // define group zoning
        $formMapper
            ->tab('User')
                ->with('General', array('class' => 'col-md-6'))->end()
                ->with('Profile', array('class' => 'col-md-6'))->end()
            ->end()
           ->tab('Photo')
                ->with('Photo', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('Security')
                ->with('Status', array('class' => 'col-md-6'))->end()
                ->with('Roles', array('class' => 'col-md-6'))->end()

            ->end()
        ;

        $now = new \DateTime();

        $formMapper
            ->tab('User')
                ->with('General')
                    ->add('id','text',array(
                        'disabled' => true,
                    ))
                    ->add('email')
                    ->add('plainPassword', 'text', array(
                        'required' => (!$this->getSubject() || is_null($this->getSubject()->getId())),
                    ))
                ->end()
                ->with('Profile')
                    /*->add('dateOfBirth', 'sonata_type_date_picker', array(
                        'years'       => range(1900, $now->format('Y')),
                        'dp_min_date' => '1-1-1900',
                        'dp_max_date' => $now->format('c'),
                        'required'    => false,
                    ))*/
                    ->add('first_name')
                    ->add('last_name')
                    // ->add('biography', 'text', array('required' => false))
                    ->add('gender', 'choice', array(
                        'label' => 'user.gender.label',
                        'choices' => array('m' => 'user.gender.male', 'w' => 'user.gender.female'),
                    ))
                    ->add('phone', 'tel', array(
                        'label' => 'user.phone.label',
                        'required' => false,
                        'default_region' => 'FR',
                        'format' => PhoneNumberFormat::NATIONAL,
                    ))
                ->end()
            ->end()
            ->tab('Photo')
                ->with('Photo')
                    ->add('photo','comur_image', array(
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
                        ))
                ->end()
            ->end()
        ;

    }


}