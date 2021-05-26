<?php

/**
 * The deploy:window:list console command
 *
 * @package  Nails\Deploy\Console\Command
 * @category Console
 */

namespace Nails\Deploy\Console\Command\Window;

use Nails\Common\Helper\Strings;
use Nails\Console\Command\Base;
use Nails\Deploy\Console\Command\Window;
use Nails\Deploy\Exception\WindowException\InvalidTimeException;
use Nails\Environment;
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
     * @throws InvalidTimeException
     */
    protected function execute(InputInterface $oInput, OutputInterface $oOutput)
    {
        parent::execute($oInput, $oOutput);

        $this->banner('Deploy Window: List');

        $aWindows = Window::discoverWindows();
        if (empty($aWindows)) {
            $oOutput->writeln('No deployment windows configured.');
            $oOutput->writeln('');
            return static::EXIT_CODE_SUCCESS;
        }

        foreach (Environment::available() as $sEnvironment) {

            $aFilteredWindows = Window::filterByEnvironment($aWindows, $sEnvironment);

            if (empty($aFilteredWindows)) {
                continue;
            }

            $oOutput->writeln('<info>' . $sEnvironment . '</info>');
            $oOutput->writeln('<info>' . str_repeat('-', strlen($sEnvironment)) . '</info>');
            foreach ($aFilteredWindows as $oWindow) {

                $aDays = $oWindow->getDays();

                $sOpen = $oWindow->getOpen() ?? '00:00:00';
                Window::validateTime($sOpen);

                $sClose = $oWindow->getClose() ?? '23:59:59';
                Window::validateTime($sClose);

                $this->keyValueList([
                    'Class'  => get_class($oWindow),
                    'Window' => sprintf(
                        '%s between %s and %s',
                        !empty($aDays)
                            ? Strings::replaceLastOccurrence(', ', ' and ', implode(', ', $aDays))
                            : 'Every day',
                        $sOpen,
                        $sClose
                    ),
                ], false);
            }
        }

        return static::EXIT_CODE_SUCCESS;
    }
}
