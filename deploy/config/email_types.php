<?php

/**
 * This config file defines email types for this module.
 *
 * @package    Nails
 * @subpackage module-deploy
 * @category   Config
 * @author     Nails Dev Team
 */

use Nails\Deploy\Constants;

$config['email_types'] = [
    (object) [
        'slug'            => 'deploy_alert_pre',
        'name'            => 'Deployment Alert: Pre',
        'can_unsubscribe' => false,
        'description'     => 'Sent when the deploy:alert:pre command is executed',
        'template_header' => '',
        'template_body'   => 'deploy/Email/templates/alert/pre',
        'template_footer' => '',
        'default_subject' => 'Deployment Starting',
        'factory'         => Constants::MODULE_SLUG . '::EmailAlertPre',
    ],
    (object) [
        'slug'            => 'deploy_alert_post',
        'name'            => 'Deployment Alert: Post',
        'can_unsubscribe' => false,
        'description'     => 'Sent when the deploy:alert:post command is executed',
        'template_header' => '',
        'template_body'   => 'deploy/Email/templates/alert/post',
        'template_footer' => '',
        'default_subject' => 'Deployment Finished',
        'factory'         => Constants::MODULE_SLUG . '::EmailAlertPost',
    ],
];
