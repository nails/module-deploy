<?php

use Nails\Deploy\Factory;

return [
    'factories' => [
        'EmailAlertPre'  => function (): Factory\Email\Alert\Pre {
            if (class_exists('\App\Deploy\Factory\Email\Alert\Pre')) {
                return new \App\Deploy\Factory\Email\Alert\Pre();
            } else {
                return new Factory\Email\Alert\Pre();
            }
        },
        'EmailAlertPost' => function (): Factory\Email\Alert\Post {
            if (class_exists('\App\Deploy\Factory\Email\Alert\Post')) {
                return new \App\Deploy\Factory\Email\Alert\Post();
            } else {
                return new Factory\Email\Alert\Post();
            }
        },
    ],
];
