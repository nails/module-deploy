<?php

namespace Nails\Deploy\Factory\Email\Alert;

use Nails\Email\Factory\Email;

class Post extends Email
{
    protected $sType = 'deploy_alert_post';

    // --------------------------------------------------------------------------

    /**
     * Returns test data to use when sending test emails
     *
     * @return mixed[]
     */
    public function getTestData(): array
    {
        return [];
    }
}
