<?php

/**
 * The deploy:alert:pre console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command;

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
 * Class Alert
 *
 * @package Nails\Deploy\Console\Command
 */
abstract class Alert extends Base
{
    const COMMAND     = '';
    const TITLE       = '';
    const DESCRIPTION = '';
    const EMAIL       = '';

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

        $aAlerts = static::discoverAlerts();
        $aAlerts = static::filterByEnvironment($aAlerts, Environment::get());
        $aAlerts = $this->filterByChildClass($aAlerts);

        $aEmails = [];
        foreach ($aAlerts as $oAlert) {
            foreach ($oAlert->getEmails() as $sEmail) {
                $aEmails[] = $sEmail;
            }
        }

        $aEmails = array_unique($aEmails);
        $aEmails = array_filter($aEmails);

        if (empty($aAlerts)) {
            $oOutput->writeln('No alerts to be sent');
            $oOutput->writeln('');
            return static::EXIT_CODE_SUCCESS;
        }

        /** @var \Nails\Email\Factory\Email $oEmail */
        $oEmail = Factory::factory(static::EMAIL, Constants::MODULE_SLUG);

        foreach ($aEmails as $sEmail) {
            $oOutput->write(sprintf(
                'Alerting %s... ',
                $sEmail
            ));

            try {

                $oEmail
                    ->to($sEmail)
                    ->send();

                $oOutput->writeln('sent');

            } catch (\Exception $e) {
                $oOutput->writeln(sprintf(
                    '<error>failed: %s</error>',
                    $e->getMessage()
                ));

            } catch (\Error $e) {
                $oOutput->writeln(sprintf(
                    '<error>failed: %s</error>',
                    $e->getMessage()
                ));
            }
        }

        return static::EXIT_CODE_SUCCESS;
    }

    // --------------------------------------------------------------------------

    /**
     * Discover Deployment Alerts
     *
     * @return Interfaces\Alert[]
     */
    public static function discoverAlerts(): array
    {
        $oCollection = Components::getApp()
            ->findClasses('Deploy\\Alert')
            ->whichImplement(Interfaces\Alert::class)
            ->whichCanBeInstantiated();

        $aOut = [];
        foreach ($oCollection as $sClass) {
            $aOut[] = new $sClass();
        }

        return $aOut;
    }

    // --------------------------------------------------------------------------

    /**
     * Filters alerts which do not apply to the given environment
     *
     * @param Interfaces\Alert[] $aAlerts      Available alerts
     * @param string             $sEnvironment The Environment to check
     *
     * @return Interfaces\Alert[]
     */
    public static function filterByEnvironment(array $aAlerts, string $sEnvironment): array
    {
        return array_filter($aAlerts, function (Interfaces\Alert $oAlert) use ($sEnvironment) {
            $aEnvironments = $oAlert->getEnvironments();
            return empty($aEnvironments) || in_array($sEnvironment, $aEnvironments);
        });
    }

    // --------------------------------------------------------------------------

    /**
     * @param Interfaces\Alert[] $aAlerts Available alerts
     *
     * @return Interfaces\Alert[]
     */
    abstract protected function filterByChildClass(array $aAlerts): array;
}
