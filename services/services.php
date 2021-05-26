<?php

use Nails\Deploy\Factory;

return [
    'factories' => [
        'EmailAlertPre'  => function (): Factory\Email\Alert\Pre {
            if (class_exists('\App\Admin\Factory\Email\Alert\Pre')) {
                return new \App\Admin\Factory\Email\Alert\Pre();
            } else {
                return new Factory\Email\Alert\Pre();
            }
        },
        'EmailAlertPost' => function (): Factory\Email\Alert\Post {
            if (class_exists('\App\Admin\Factory\Email\Alert\Post')) {
                return new \App\Admin\Factory\Email\Alert\Post();
            } else {
                return new Factory\Email\Alert\Post();
            }
        },
    ],
];
