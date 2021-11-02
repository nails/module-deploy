<?php

namespace Nails\Deploy\Traits\Console\Command;

use Nails\Components;
use Nails\Deploy\Exception\WindowException\InvalidTimeException;
use Nails\Deploy\Interfaces;

/**
 * Trait Utilities
 *
 * @package Nails\Deploy\Traits\Console\Command
 */
trait Utilities
{
    /**
     * Discover Deployment Windows
     *
     * @return Interfaces\Window[]
     */
    public function discoverWindows(): array
    {
        $oCollection = Components::getApp()
            ->findClasses('Deploy\\Window')
            ->whichImplement(Interfaces\Window::class)
            ->whichCanBeInstantiated();

        $aOut = [];
        foreach ($oCollection as $sClass) {
            /** @var Interfaces\Window $oClass */
            $oClass = new $sClass();
            $aOut[] = $oClass;
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Discover Deployment Alerts
     *
     * @return Interfaces\Alert[]
     */
    public function discoverAlerts(): array
    {
        $oCollection = Components::getApp()
            ->findClasses('Deploy\\Alert')
            ->whichImplement(Interfaces\Alert::class)
            ->whichCanBeInstantiated();

        $aOut = [];
        foreach ($oCollection as $sClass) {
            /** @var Interfaces\Alert $oClass */
            $oClass = new $sClass();
            $aOut[] = $oClass;
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Filters items which do not apply to the given environment
     *
     * @param Interfaces\Window[]|Interfaces\Alert[] $aItems       Available items
     * @param string                                 $sEnvironment The Environment to check
     *
     * @return Interfaces\Window[]|Interfaces\Alert[]
     */
    public function filterByEnvironment(array $aItems, string $sEnvironment): array
    {
        return array_filter($aItems, function ($oItem) use ($sEnvironment) {
            $aEnvironments = $oItem->getEnvironments();
            return empty($aEnvironments) || in_array($sEnvironment, $aEnvironments);
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Filters windows which are outside of a given date constraint
     *
     * @param Interfaces\Window[] $aWindows     Available windows
     * @param \DateTime           $oCompareDate The date to compare
     *
     * @return Interfaces\Window[]
     * @throws InvalidTimeException
     */
    public function filterByDate(array $aWindows, \DateTime $oCompareDate): array
    {
        return array_filter($aWindows, function (Interfaces\Window $oWindow) use ($oCompareDate) {

            $aDays      = array_map('strtoupper', $oWindow->getDays());
            $bDayIsOk   = empty($aDays) || in_array(strtoupper($oCompareDate->format('l')), $aDays);
            $bOpenIsOk  = false;
            $bCloseIsOk = false;

            if ($bDayIsOk) {

                $oOpen = clone $oCompareDate;
                $sTime = $oWindow->getOpen() ?? '00:00:00';
                $this->validateTime($sTime);
                $oOpen->setTimezone($oWindow->getTimeZone());
                $oOpen->setTime(...array_map('intval', explode(':', $sTime)));

                $bOpenIsOk = $oOpen <= $oCompareDate;

                if ($bOpenIsOk) {

                    $oClose = clone $oCompareDate;
                    $sTime  = $oWindow->getClose() ?? '23:59:59';
                    $this->validateTime($sTime);
                    $oClose->setTimezone($oWindow->getTimeZone());
                    $oClose->setTime(...array_map('intval', explode(':', $sTime)));

                    $bCloseIsOk = $oClose > $oCompareDate;
                }
            }

            return $bDayIsOk && $bOpenIsOk && $bCloseIsOk;
        });
    }

    // --------------------------------------------------------------------------

    /**
     * Validates that a time is a valid timestamp
     *
     * @param string $sTime The time to validate
     *
     * @throws InvalidTimeException
     */
    public function validateTime(string $sTime): void
    {
        if (!preg_match('/[0-2]\d:[0-5]\d:[0-5]\d/', $sTime)) {
            throw new InvalidTimeException(sprintf(
                '"%s" must be in the format [0-2]\d:[0-5]\d:[0-5]\d',
                $sTime
            ));
        }
    }

    // --------------------------------------------------------------------------

    /**
     * @param Interfaces\Alert[] $aAlerts
     *
     * @return string[]
     */
    public function extractEmails(array $aAlerts): array
    {
        $aEmails = [];
        foreach ($aAlerts as $oAlert) {
            foreach ($oAlert->getEmails() as $sEmail) {
                $aEmails[] = $sEmail;
            }
        }

        return array_filter(array_unique($aEmails));
    }
}
