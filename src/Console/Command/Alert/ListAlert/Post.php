<?php

/**
 * The deploy:alert:post:list console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Alert\ListAlert;

use Nails\Deploy\Console\Command\Alert\ListAlert;

/**
 * Class Post
 *
 * @package Nails\Deploy\Console\Command\Alert\ListAlert
 */
class Post extends ListAlert
{
    const COMMAND     = 'deploy:alert:post:list';
    const TITLE       = 'Deploy Alert: Post: List';
    const DESCRIPTION = 'Lists the emails which will be alerted that a deployment has finished';

    // --------------------------------------------------------------------------

    public static function filterByChildClass(array $aAlerts): array
    {
        return \Nails\Deploy\Console\Command\Alert\Post::filterByChildClass($aAlerts);
    }
}
