<?php

/**
 * The deploy:alert:pre:list console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Alert\ListAlert;

use Nails\Deploy\Console\Command\Alert\ListAlert;

/**
 * Class Pre
 *
 * @package Nails\Deploy\Console\Command\Alert\ListAlert
 */
class Pre extends ListAlert
{
    const COMMAND     = 'deploy:alert:pre:list';
    const TITLE       = 'Deploy Alert: Pre: List';
    const DESCRIPTION = 'Lists the emails which will be alerted that a deployment is starting';

    // --------------------------------------------------------------------------

    public static function filterByChildClass(array $aAlerts): array
    {
        return \Nails\Deploy\Console\Command\Alert\Pre::filterByChildClass($aAlerts);
    }
}
