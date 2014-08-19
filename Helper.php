<?php
/**
 * @author      Serge Postrash aka SDKiller <jexy.ru@gmail.com>
 * @copyright   Copyright (c) 2014 Serge Postrash aka SDKiller
 * @license     BSD-3-Clause
 */

namespace zyx\unbloat;

use Composer\Script\CommandEvent;


class UnbloatHelper
{
    /**
     * Deletes directories listed in ... TODO
     * @param CommandEvent $event
     */
    public static function postCmd($event)
    {
        echo 'Testing post-create-project-cmd and post-update-cmd';
    }

}