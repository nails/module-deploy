<?php

/**
 * The deploy:alert:post console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Alert;

use Nails\Deploy\Interfaces;
use Nails\Deploy\Console\Command\Alert;

/**
 * Class Post
 *
 * @package Nails\Deploy\Console\Command\Alert
 */
class Post extends Alert
{
    const COMMAND     = 'deploy:alert:post';
    const TITLE       = 'Deploy Alert: Post';
    const DESCRIPTION = 'Sends an alert to configured users that a deployment has finished';

    // --------------------------------------------------------------------------

    public static function filterByChildClass(array $aAlerts): array
    {
        return array_filter($aAlerts, function (Interfaces\Alert $oAlert) {
            return $oAlert->isPost();
        });
    }
}
