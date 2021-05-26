<?php

/**
 * The deploy:window console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command;

use Nails\Common\Exception\FactoryException;
use Nails\Components;
use Nails\Console\Command\Base;
use Nails\Deploy\Exception\WindowException;
use Nails\Deploy\Exception\WindowException\InvalidTimeException;
use Nails\Deploy\Interfaces;
use Nails\Environment;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Window
 *
 * @package App\Console\Command\Deploy
 */
class Window extends Base
{
    /**
     * Configure the deploy:window command
     */
    protected function configure(): void
    {
        $this
            ->setName('deploy:window')
            ->setDescription('Tests if the site is within a deployment window');
    }

    // --------------------------------------------------------------------------

    /**
     * Execute the command
     *
     * @param InputInterface  $oInput  The Input Interface provided by Symfony
     * @param OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @return int
     * @throws FactoryException
     * @throws WindowException
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::execute($oInput, $oOutput);

        $this->banner('Deploy Window');

        $aWindows = static::discoverWindows();

        /**
         * If there are NO windows for the app then that means there are no windows configured at
         * all, so rather than block all deployments, let all deployments through as non-configuration
         * shouldn't block
         */
        if (empty($aWindows)) {
            $oOutput->writeln('Deployment accepted; no deployment windows defined for app');
            $oOutput->writeln('');
            return static::EXIT_CODE_SUCCESS;
        }

        $aWindows = static::filterByEnvironment($aWindows, Environment::get());

        /**
         * If there are no windows defined for the environment, then assume all deployments are acceptable
         */
        if (empty($aWindows)) {
            $oOutput->writeln('Deployment accepted; no deployment windows defined for ' . Environment::get());
            $oOutput->writeln('');
            return static::EXIT_CODE_SUCCESS;
        }

        /** @var \DateTime $oNow */
        $oNow     = Factory::factory('DateTime');
        $aWindows = static::filterByDate($aWindows, $oNow);

        /**
         * In this case, as there ARE windows defined for the environment then a lack of windows means that
         * the current time is outside any window, in this case we SHOULD block the deployment
         */
        if (empty($aWindows)) {
            throw new WindowException('Deployment rejected; outside of deployment window');
        }

        $oOutput->writeln('Deployment accepted; within deployment window');
        $oOutput->writeln('');
        return static::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    /**
     * Discover Deployment Windows
     *
     * @return Interfaces\Window[]
     */
    public static function discoverWindows(): array
    {
        $oCollection = Components::getApp()
            ->findClasses('Deploy\\Window')
            ->whichImplement(Interfaces\Window::class)
            ->whichCanBeInstantiated();

        $aOut = [];
        foreach ($oCollection as $sClass) {
            $aOut[] = new $sClass();
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Filters windows which do not apply to the given environment
     *
     * @param Interfaces\Window[] $aWindows     Available windows
     * @param string              $sEnvironment The Environment to check
     *
     * @return Interfaces\Window[]
     */
    public static function filterByEnvironment(array $aWindows, string $sEnvironment): array
    {
        return array_filter($aWindows, function (Interfaces\Window $oWindow) use ($sEnvironment) {
            $aEnvironments = $oWindow->getEnvironments();
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
    public static function filterByDate(array $aWindows, \DateTime $oCompareDate): array
    {
        return array_filter($aWindows, function (Interfaces\Window $oWindow) use ($oCompareDate) {

            $aDays      = array_map('strtoupper', $oWindow->getDays());
            $bDayIsOk   = empty($aDays) || in_array(strtoupper($oCompareDate->format('l')), $aDays);
            $bOpenIsOk  = false;
            $bCloseIsOk = false;

            if ($bDayIsOk) {

                $oOpen = clone $oCompareDate;
                $sTime = $oWindow->getOpen() ?? '00:00:00';
                static::validateTime($sTime);
                $oOpen->setTime(...array_map('intval', explode(':', $sTime)));

                $bOpenIsOk = $oOpen <= $oCompareDate;

                if ($bOpenIsOk) {

                    $oClose = clone $oCompareDate;
                    $sTime  = $oWindow->getClose() ?? '23:59:59';
                    static::validateTime($sTime);
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
    public static function validateTime(string $sTime): void
    {
        if (!preg_match('/[0-2]\d:[0-5]\d:[0-5]\d/', $sTime)) {
            throw new InvalidTimeException(sprintf(
                '"%s" must be in the format [0-2]\d:[0-5]\d:[0-5]\d',
                $sTime
            ));
        }
    }
}
