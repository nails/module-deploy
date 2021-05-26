<?php

namespace Nails\Deploy\Interfaces;

/**
 * Interface Alert
 *
 * @package Nails\Deploy\Interfaces
 */
interface Alert
{
    /**
     * Returns the environments for which this alert applies, empty means applies to all environments
     *
     * @return string[]
     */
    public function getEnvironments(): array;

    // --------------------------------------------------------------------------

    /**
     * Returns the emails which should be alerted
     *
     * @return string[]
     */
    public function getEmails(): array;

    // --------------------------------------------------------------------------

    /**
     * Whether the collection of email addresses should be included in the `pre` deployment alert
     *
     * @return bool
     */
    public function isPre(): bool;

    // --------------------------------------------------------------------------

    /**
     * Whether the collection of email addresses should be included in the `post` deployment alert
     *
     * @return bool
     */
    public function isPost(): bool;
}
