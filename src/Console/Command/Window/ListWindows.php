<?php

/**
 * The deploy:window:list console command
 *
 * @package  App
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Window;

use Nails\Console\Command\Base;
use Nails\Deploy\Console\Command\Window;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListWindows
 *
 * @package Nails\Deploy\Console\Command\Window
 */
class ListWindows extends Base
{
    /**
     * Configure the deploy:window:list command
     */
    protected function configure(): void
    {
        $this
            ->setName('deploy:window:list')
            ->setDescription('Lists configured deployment windows');
    }

    // --------------------------------------------------------------------------

    /**
     * Execute the command
     *
     * @param InputInterface  $oInput  The Input Interface provided by Symfony
     * @param OutputInterface $oOutput The Output Interface provided by Symfony
     *
     * @return int
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::execute($oInput, $oOutput);

        $this->banner('Deploy Window: List');

        foreach (Window::discoverWindows() as $oWindow) {

            $aEnvironments = $oWindow->getEnvironments();

            $this->keyValueList([
                'Class'       => get_class($oWindow),
                'Environment' => $aEnvironments
                    ? implode(', ', $aEnvironments)
                    : 'All',
                'Window'      => sprintf(
                    '%s at %s, closing %s',
                    $oWindow->getDay() ?? 'Every day',
                    $oWindow->getOpen() ?? '00:00:00',
                    $oWindow->getClose() ?? '23:59:59'
                ),
            ]);
        }

        return static::EXIT_CODE_SUCCESS;
    }
}
