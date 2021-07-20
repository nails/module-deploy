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
     * Returns the environments for which this window applies, empty means applies to all environments
     *
     * @return string[]
     */
    public function getEnvironments(): array;

    // --------------------------------------------------------------------------

    /**
     * Returns the day of the week this window applies to, empty means applies every day
     *
     * @return string[]
     */
    public function getDays(): array;

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

    // --------------------------------------------------------------------------

    /**
     * Returns the timezone to use when checking the window
     *
     * @return \DateTimeZone
     */
    public function getTimezone(): \DateTimeZone;
}
