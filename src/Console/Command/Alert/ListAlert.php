<?php

/**
 * The deploy:alert:*:list console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Alert;

use Nails\Deploy\Console\Command\Alert;
use Nails\Deploy\Constants;
use Nails\Deploy\Interfaces;
use Nails\Common\Exception\FactoryException;
use Nails\Components;
use Nails\Console\Command\Base;
use Nails\Environment;
use Nails\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListAlert
 *
 * @package Nails\Deploy\Console\Command\Alert
 */
abstract class ListAlert extends Base
{
    const COMMAND     = '';
    const TITLE       = '';
    const DESCRIPTION = '';

    // --------------------------------------------------------------------------

    /**
     * Configure the deploy:alert:* command
     */
    protected function configure(): void
    {
        $this
            ->setName(static::COMMAND)
            ->setDescription(static::DESCRIPTION);
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

        $this->banner(static::TITLE);

        $aAlerts = Alert::discoverAlerts();
        $aAlerts = static::filterByChildClass($aAlerts);
        $aOutput = [];

        foreach (Environment::available() as $sEnvironment) {

            $aFilteredAlerts = Alert::filterByEnvironment($aAlerts, $sEnvironment);
            $aEmails         = Alert::extractEmails($aFilteredAlerts);

            if (empty($aEmails)) {
                continue;
            }

            $aOutput[$sEnvironment] = [];
            foreach ($aEmails as $sEmail) {
                $aOutput[$sEnvironment][] = $sEmail;
            }
        }

        if (empty($aOutput)) {
            $oOutput->writeln('No deployment alerts configured.');
            $oOutput->writeln('');
            return static::EXIT_CODE_SUCCESS;
        }

        foreach ($aOutput as $sEnvironment => $aEnvironmentEmails) {

            $oOutput->writeln('<info>' . $sEnvironment . '</info>');
            $oOutput->writeln('<info>' . str_repeat('-', strlen($sEnvironment)) . '</info>');

            foreach ($aEnvironmentEmails as $sEmail) {
                $oOutput->writeln($sEmail);
            }

            $oOutput->writeln('');
        }

        return static::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    /**
     * Hook for the child class to filter alerts
     *
     * @param Interfaces\Alert[] $aAlerts Available alerts
     *
     * @return Interfaces\Alert[]
     */
    abstract public static function filterByChildClass(array $aAlerts): array;
}
