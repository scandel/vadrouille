<?php

namespace AppBundle\Form\Trip;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

/**
 * This class creates a "flow", responsible for splitting TripForm in two parts
 *
 * Class TripFlow
 * @package AppBundle\Form\Trip
 */
class TripFlow extends FormFlow {

    protected $allowDynamicStepNavigation = true;

    protected function loadStepsConfig() {
        return array(
            array(
                'label' => 'Etape 1',
                'form_type' => 'AppBundle\Form\Trip\TripType',
            ),
            array(
                'label' => 'Etape 2',
                'form_type' => 'AppBundle\Form\Trip\TripType',
            ),
                array(
                'label' => 'Confirmation',
            ),
        );
    }
}