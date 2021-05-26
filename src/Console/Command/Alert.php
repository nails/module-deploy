<?php

/**
 * The deploy:alert:* console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command;

use Nails\Common\Exception\FactoryException;
use Nails\Console\Command\Base;
use Nails\Deploy\Constants;
use Nails\Deploy\Interfaces;
use Nails\Deploy\Traits\Console\Command\Utilities;
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
    use Utilities;

    // --------------------------------------------------------------------------

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

        $aAlerts = $this->discoverAlerts();
        /** @var Interfaces\Alert[] $aAlerts */
        $aAlerts = $this->filterByEnvironment($aAlerts, Environment::get());
        $aAlerts = $this->filterByChildClass($aAlerts);

        $aEmails = $this->extractEmails($aAlerts);

        if (empty($aEmails)) {
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

        $oOutput->writeln('');
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
