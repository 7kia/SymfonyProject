<?php

namespace AppBundle\Security;

abstract class ApplicationStatus
{
    const SEND_SUCCESSFUL = 0;
    const SEND_FAILED = 1;
    const REPEAT_SEND = 2;
}