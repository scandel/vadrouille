<?php

namespace AppBundle\Form\Trip;

use AppBundle\Form\Stop\StopType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Craue\FormFlowBundle\Event\FormFlowEvent;
use Craue\FormFlowBundle\Form\FormFlowEvents;
use AppBundle\Form\Person\PersonType;
use AppBundle\Form\Person\PersonGuestType;
use AppBundle\Form\Stop\StopHiddenType;

class TripType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Underlying $trip object...
        $trip = $builder->getData();

        // This form is splitted in two steps, so we use the 'fow_step' option
        // from form flow to cnstruct the fields of the form
        if (isset($options['flow_step'])) {
            switch ($options['flow_step']) {
                case 1:

                    //== Departure, arrival, and intermediate stops
                    // (Cities and addresses)
                    $builder->add('stops', 'collection', array(
                        'type' => 'app_stop_edit',
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false
                    ));

                    //== Date, time, regularity...
                    // One-shot or regular
                    $builder->add('regular', 'choice', array(
                        'choices' => array(
                            0 => 'Une seule fois',
                            1 => 'Régulier',
                        ),
                        'expanded' => true,
                        'multiple' => false,
                        'label' => 'Fréquence : ',
                    ));
                    $builder->addEventListener(
                        FormEvents::PRE_SET_DATA,
                        function (FormEvent $event) {
                            $trip = $event->getData();
                            $trip->setRegular($trip->getRegular() == false ? 0 : 1);
                            $event->setData($trip);
                        }
                    );

                    // If one-shot, departure date
                    $builder->add('depDate', 'datePicker', array(
                        'label' => 'Départ le : ',
                    ));

                    // If regular, departure days of the week
                    $builder->add('days', 'daysOfWeek', array(
                        'label' => 'Jours du trajet : ',
                    ));

                    // If regular, begin and end  date
                    $builder->add('beginDate', 'datePicker', array(
                        'label' => 'Début : ',
                    ));
                    $builder->add('endDate', 'datePicker', array(
                        'label' => 'Fin : ',
                    ));


                    // Departure time
                    $hours = range(0, 23);
                    $minutes = range(0, 55, 5);

                    $builder->add('depTime', 'time', array(
                        'label' => 'à : ',
                        'placeholder' => '---',
                        'input' => 'datetime',
                        'widget' => 'choice',
                        'hours' => $hours,
                        'minutes' => $minutes,
                    ));

                    // Mappy Roadbook
                    $builder->add('mappyRoadbook', HiddenType::class);

                    break;
                case 2:
                    // 2nd part of the form
                    // Comment
                    $builder->add('comment', null, array('required' => false));

                    // Places
                    $builder->add('places', IntegerType::class, array(
                        'label' => 'Places proposées :',
                        'attr' => array(
                            'min' => 1,
                            'max' => 8,
                        )
                    ));

                    // Bags
                    $builder->add('bags',  ChoiceType::class, array(
                        'label' => 'Taille des bagages :',
                        'choices' => array(
                            'Petits' => 'small',
                            'Moyens' =>'medium',
                            'Grands' => 'big',
                        ),
                        'choices_as_values' => true,
                        'expanded' => false,
                        'multiple' => false,
                    ));

                    // Contact
                    $builder->add('contact',  ChoiceType::class, array(
                        'label' => 'Modes de contact :',
                        'choices' => array(
                            'Téléphone et email' => 'both',
                            'Téléphone seulement' =>'phone',
                            'Email seulement' => 'email',
                        ),
                        'choices_as_values' => true,
                        'expanded' => false,
                        'multiple' => false,
                    ));

                    // Person
                    if (!$trip->getPerson() || $trip->getPerson()->isGuest()) {
                        // The Guest Person Form
                        $builder->add("person", PersonGuestType::class, array(
                            'label' => false
                        ));
                    }

                    // Non mapped fields for price manipulation

                    $diffPrices = array();
                    $prices = array();
                    foreach ($trip->getStops() as $stop) {
                        $prices[$stop->getDelta()] = $stop->getPrice();
                    }
                    ksort($prices);
                    $prices = array_values($prices);
                    for ($i = 0; $i < count($prices)-1; $i++) {
                        $diffPrices[] = max(0, (int) $prices[$i+1] - (int) $prices[$i]);
                    }

                    $builder->add('pricediff', CollectionType::class, array(
                        'mapped' => false,
                        'entry_type' => MoneyType::class,
                        'entry_options' => array(
                            'currency' => 'EUR',
                            'scale' => 0,
                        ),
                        'data' => $diffPrices,
                    ));

                    // Put again stops to modify stop times and prices
                    $builder->add('stops', 'collection', array(
                        'type' => StopHiddenType::class ,
                        'by_reference' => false
                    ));

                    // Mappy Roadbook
                    $builder->add('mappyRoadbook', HiddenType::class);

                    break;
            }
        }
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Trip'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_trip_edit';
    }
}
