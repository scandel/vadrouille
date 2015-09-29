<?php

namespace AppBundle\Form\Car;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use  AppBundle\Entity\CarBrand;

class CarType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $car = $builder->getForm()->getData();

        $builder->add('brand', 'entity', array(
            'class' => 'AppBundle:CarBrand',
            'property' => 'name',
            'placeholder' => 'Choisissez - - -',
            'label' => 'Marque :'
        )) ;

        $formModifier = function (FormInterface $form, CarBrand $brand = null) {
            $models = null === $brand ? array() : $brand->getModels();

            $form->add('model', 'entity', array(
                'class'       => 'AppBundle:CarModel',
                'property' => 'name',
                'placeholder' => 'Choisissez - - -',
                'label' => 'Modèle :',
                'choices'     => $models,
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {

                // this gets the  entity, i.e. Car
                $car = $event->getData();

                $formModifier($event->getForm(), $car->getBrand());
            }
        );

        $builder->get('brand')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $brand = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $brand);
            }
        );

        $builder->add('type', 'choice', array(
            'label' => 'Type de voiture :',
            'choices' => array(
                'small'     => 'Petite voiture',
                'medium'    => 'Moyenne voiture',
                'road'      => 'Routière',
                'espace'    => 'Espace/4x4',
                'minibus'    => 'Minibus',
                ),
            'expanded' => false,
            'multiple' => false,
        ));
        $builder->add('color', 'choice', array(
            'label' => 'Couleur :',
            'choices' => array(
                'BLACK' => 'noire'       ,
                'SILVER'=> 'argent'      ,
                'GREY'  => 'grise'       ,
                'WHITE' => 'blanche'     ,
                'BLUE'  => 'bleue'       ,
                'RED'   => 'rouge'       ,
                'GREEN' => 'verte'       ,
                'YELLOW'=> 'jaune/dorée' ,
                'BROWN' => 'marron/beige',
                'PINK'  => 'rose'        ,
                'ORANGE'=> 'orange'      ,
                'OTHER' => 'autre'       ,
                ),
            'expanded' => false,
            'multiple' => false,
        ));
        $builder->add('number_plate', null, array(
            'label' => 'Numéro d\'immatriculation :'
        ));
        $builder->add('places', null, array(
            'label' => 'Nombre de places total :'
        ));
        $builder->add('doors', null, array(
            'label' => 'Nombre de portes :'
        ));
        $builder->add('airCond', 'choice', array(
            'label' => 'Climatisation :',
            'choices' => array(
                1 => 'oui'       ,
                0 => 'non'
            ),
            'expanded' => true,
            'multiple' => false,
        ));
        $builder->add('music', 'choice', array(
            'label' => 'Autoradio :',
            'choices' => array(
                1 => 'oui'       ,
                0 => 'non'
            ),
            'expanded' => true,
            'multiple' => false,
        ));
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $car = $event->getData();
                $car->setAirCond( $car->getAirCond() === false ? 0 : 1 );
                $car->setMusic( $car->getMusic() === false ? 0 : 1 );
                $event->setData($car);
            }
        );
        $builder->add('photo','comur_image', array(
            'uploadConfig' => array(
                'uploadUrl' => $car->getUploadRootDir(),
                'webDir' => $car->getUploadDir(),
                'saveOriginal' => 'originalPhoto',
                'showLibrary' => false, // don't show images already uploaded
            ),
            'cropConfig' => array(
                'minWidth' => 200,
                'minHeight' => 150,
                'aspectRatio' => true,
                'forceResize' => false,
                'thumbs' => array(
                    array(
                        'maxWidth' => 600,
                        'maxHeight' => 450,
                    ),
                    array(
                        'maxWidth' => 300,
                        'maxHeight' => 225,
                        'useAsFieldImage' => true  //optional
                    ),
                    array(
                        'maxWidth' => 100,
                        'maxHeight' => 75,
                    ),
                )
            )
        ));

    }

    public function getName()
    {
        return 'app_car_edit';
    }
}
