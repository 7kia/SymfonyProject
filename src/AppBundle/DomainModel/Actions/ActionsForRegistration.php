<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForRegistration;
use AppBundle\DomainModel\Strategies\StrategiesForRegistration;

class ActionsForRegistration
{
    private $rulesForRegistration;
    private $strategiesForRegistration;

    public function __construct($doctrine)
    {
        $this->rulesForRegistration = new RulesForRegistration($doctrine);
        $this->strategiesForRegistration = new StrategiesForRegistration($doctrine);
    }

    public function registerUser($user, $passwordEncoder)
    {
        if ($this->rulesForRegistration->canRegisterUser($user, $passwordEncoder)) {
            $this->strategiesForRegistration->registerUser($user, $passwordEncoder);
        }
    }
}