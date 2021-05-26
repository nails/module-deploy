<?php

/**
 * The deploy:window console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command;

use Nails\Common\Exception\FactoryException;
use Nails\Console\Command\Base;
use Nails\Deploy\Exception\WindowException;
use Nails\Deploy\Interfaces;
use Nails\Deploy\Traits\Console\Command\Utilities;
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
    use Utilities;

    // --------------------------------------------------------------------------

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

        $aWindows = $this->discoverWindows();

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

        /** @var Interfaces\Window[] $aWindows */
        $aWindows = $this->filterByEnvironment($aWindows, Environment::get());

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
        $aWindows = $this->filterByDate($aWindows, $oNow);

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
}
