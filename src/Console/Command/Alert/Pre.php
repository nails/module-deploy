<?php

/**
 * The deploy:alert:pre console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Alert;

use Nails\Deploy\Interfaces;
use Nails\Deploy\Console\Command\Alert;

/**
 * Class Pre
 *
 * @package Nails\Deploy\Console\Command\Alert
 */
class Pre extends Alert
{
    const COMMAND     = 'deploy:alert:pre';
    const TITLE       = 'Deploy Alert: Pre';
    const DESCRIPTION = 'Sends an alert to configured users that a deployment is starting';

    // --------------------------------------------------------------------------

    protected function filterByChildClass(array $aAlerts): array
    {
        return array_filter($aAlerts, function (Interfaces\Alert $oAlert) {
            return $oAlert->isPre();
        });
    }
}
