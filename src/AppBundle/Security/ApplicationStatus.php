<?php
/**
 * Created by PhpStorm.
 * User: Илья
 * Date: 10.12.2017
 * Time: 19:33
 */

namespace AppBundle\Security;


abstract class ApplicationStatus
{
    const SEND_SUCCESSFUL = 0;
    const SEND_FAILED = 1;
    const REPEAT_SEND = 2;
}