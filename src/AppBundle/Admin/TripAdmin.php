<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TripAdmin extends Admin
{

 // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('current')
            ->add('nextDateTime')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            // ->add('Photo', 'string', array('template' => 'AppBundle:Admin:image.html.twig'))
            ->add('current')
            ->add('nextDateTime')
            //->add('phone')
            //->add('enabled')
            //->add('locked', null, array('editable' => true))
            //->add('lastLogin')
        ;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        // get the current User instance
       /* $user = $this->getSubject();

        // define group zoning
        $formMapper
            ->with('General', array('class' => 'col-md-6'))->end()
            ->with('Profile', array('class' => 'col-md-6 clearfix'))->end()
            ->with('Security', array('class' => 'col-md-6'))->end()
            ->with('Photo', array('class' => 'col-md-6'))->end()
        ;

        $now = new \DateTime();

        $formMapper
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
             /*   ->add('firstName')
                ->add('lastName')
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
            ->with('Security')
                ->add('enabled','checkbox',array(
                   //  'disabled' => true,
                ))
                ->add('locked')
                ->add('comment')
                ->add('multipleIds')
            ->end()
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
        ;*/
    }


}