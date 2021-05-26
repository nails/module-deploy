<?php

namespace Nails\Deploy\Factory\Email\Alert;

use Nails\Email\Factory\Email;

class Pre extends Email
{
    protected $sType = 'deploy_alert_pre';

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
