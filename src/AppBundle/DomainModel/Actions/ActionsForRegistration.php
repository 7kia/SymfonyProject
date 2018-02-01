<?php

namespace AppBundle\DomainModel\Actions;

use AppBundle\DomainModel\Rules\RulesForRegistration;
use AppBundle\DomainModel\Strategies\StrategiesForRegistration;

class ActionsForRegistration
{
    /** @var  RulesForRegistration */
    private $rulesForRegistration;
    /** @var  StrategiesForRegistration */
    private $strategiesForRegistration;

    /**
     * ActionsForRegistration constructor.
     * @param $doctrine
     */
    public function __construct($doctrine)
    {
        $this->rulesForRegistration = new RulesForRegistration($doctrine);
        $this->strategiesForRegistration = new StrategiesForRegistration($doctrine);
    }

    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function registerUser($user, $passwordEncoder)
    {
        if ($this->rulesForRegistration->canRegisterUser($user, $passwordEncoder)) {
            $this->strategiesForRegistration->registerUser($user, $passwordEncoder);
        }
    }
}