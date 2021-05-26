<?php

/**
 * The deploy:window console command
 *
 * @package  App
 * @category Console
 */

namespace Nails\Deploy\Console\Command;

use Nails\Common\Exception\FactoryException;
use Nails\Components;
use Nails\Console\Command\Base;
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
            ->setDescription('Tests if the site is within a deploy window');
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
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::execute($oInput, $oOutput);

        $this->banner('Deploy Window');

        $aWindows = static::discoverWindows();

        if (empty($aWindows)) {
            $this->oOutput->writeln('Deployment accepted; no deploy windows defined for app');
            return static::EXIT_CODE_SUCCESS;
        }

        $aWindows = static::filterByEnvironment($aWindows, Environment::get());

        if (empty($aWindows)) {
            $this->oOutput->writeln('Deployment accepted; no deploy windows defined for ' . Environment::get());
            return static::EXIT_CODE_SUCCESS;
        }

        /** @var \DateTime $oNow */
        $oNow     = Factory::factory('DateTime');
        $aWindows = static::filterByDate($aWindows, $oNow);

        if (empty($aWindows)) {
            $this->oOutput->writeln('Deployment rejected; outside of deploy window');
            return static::EXIT_CODE_FAILURE;
        }

        $this->oOutput->writeln('Deployment accepted; within deploy window');
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
            ->findClasses()
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
            return $aEnvironments === null || in_array($sEnvironment, $aEnvironments);
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
     */
    public static function filterByDate(array $aWindows, \DateTime $oCompareDate): array
    {
        return array_filter($aWindows, function (Interfaces\Window $oWindow) use ($oCompareDate) {

            $sWindowDay = strtoupper($oWindow->getDay() ?? '') ?: null;
            $bDayIsOk   = $sWindowDay === null || $sWindowDay === strtoupper($oCompareDate->format('l'));
            $bOpenIsOk  = false;
            $bCloseIsOk = false;

            if ($bDayIsOk) {

                $oOpen = clone $oCompareDate;
                $sTime = $oWindow->getOpen() ?? '00:00:00';
                $oOpen->setTime(...array_map('intval', explode(':', $sTime)));

                $bOpenIsOk = $oOpen <= $oCompareDate;

                if ($bOpenIsOk) {

                    $oClose = clone $oCompareDate;
                    $sTime  = $oWindow->getClose() ?? '23:59:59';
                    $oClose->setTime(...array_map('intval', explode(':', $sTime)));

                    $bCloseIsOk = $oClose > $oCompareDate;
                }
            }

            return $bDayIsOk && $bOpenIsOk && $bCloseIsOk;
        });
    }
}
