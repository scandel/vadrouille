<?php

namespace AppBundle\Form\Car;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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

            $form->add('model', EntityType::class, array(
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

        $builder->add('type', ChoiceType::class, array(
            'label' => 'Type de voiture :',
            'choices' => array(
                'Petite voiture' => 'small',
                'Moyenne voiture' =>'medium',
                'Routière' => 'road',
                'Espace/4x4' => 'espace',
                'Minibus' => 'minibus',
                ),
            'choices_as_values' => true,
            'expanded' => false,
            'multiple' => false,
        ));
        $builder->add('color', ChoiceType::class, array(
            'label' => 'Couleur :',
            'choices' => array(
                'noire'        => 'BLACK' ,
                'argent'       => 'SILVER',
                'grise'        => 'GREY'  ,
                'blanche'      => 'WHITE' ,
                'bleue'        => 'BLUE'  ,
                'rouge'        => 'RED'   ,
                'verte'        => 'GREEN' ,
                'jaune/dorée'  => 'YELLOW',
                'marron/beige' => 'BROWN' ,
                'rose'         => 'PINK'  ,
                'orange'       => 'ORANGE',
                'autre'        => 'OTHER' ,
                ),
            'choices_as_values' => true,
            'expanded' => false,
            'multiple' => false,
        ));
        $builder->add('number_plate', TextType::class, array(
            'label' => 'Numéro d\'immatriculation :'
        ));
        $builder->add('places', TextType::class, array(
            'label' => 'Nombre de places total :'
        ));
        $builder->add('doors', TextType::class, array(
            'label' => 'Nombre de portes :'
        ));
        $builder->add('airCond', ChoiceType::class, array(
            'label' => 'Climatisation :',
            'choices' => array(
                'oui' => 1       ,
                'non' => 0
            ),
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false,
        ));
        $builder->add('music', ChoiceType::class, array(
            'label' => 'Autoradio :',
            'choices' => array(
                'oui' => 1       ,
                'non' => 0
            ),
            'choices_as_values' => true,
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

    public function getBlockPrefix()
    {
        return 'app_car_edit';
    }
}
