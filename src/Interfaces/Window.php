<?php

namespace Nails\Deploy\Interfaces;

/**
 * Interface Window
 *
 * @package Nails\Deploy\Interfaces
 */
interface Window
{
    /**
     * Returns the environments for which this window applies, null means applies to all environments
     *
     * @return string[]|null
     */
    public function getEnvironments(): ?array;

    // --------------------------------------------------------------------------

    /**
     * Returns the day of the week this window applies to, null means applies every day
     *
     * @return string|null
     */
    public function getDay(): ?string;

    // --------------------------------------------------------------------------

    /**
     * Returns the open time of the window, in format HH:MM:SS, null means 00:00:00
     *
     * @return string|null
     */
    public function getOpen(): ?string;

    // --------------------------------------------------------------------------

    /**
     * Returns the close time of the window, in format HH:MM:SS, null means 23:59:59
     *
     * @return string|null
     */
    public function getClose(): ?string;
}
