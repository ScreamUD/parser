<?php

namespace ParserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

/**
 * Class ParserCommand
 * @package ParserBundle\Command
 */
class ParserCommand extends ContainerAwareCommand
{
    /** config parser command */
    protected function configure()
    {
        $this
            ->setName('parser:command')
            ->setDescription('Importing product to database. Choose your file.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Type a path to file'
            )
            ->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Use to run test mode'
            )
            ->addOption(
                'clear-table',
                null,
                InputOption::VALUE_NONE,
                'Use to clear all data from your table'
            );
    }

    /** Execute configure and data processing */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $testOption = $input->getOption('test');
        $clearTableOption = $input->getOption('clear-table');
        $dateStart = new \DateTime();
        $beginStyle = new OutputFormatterStyle('black', 'blue');
        $output->getFormatter()->setStyle('begin-output', $beginStyle);

        /** @var \ParserBundle\Service\ParserService $parserService */
        $parserService = $this->getContainer()->get('service.parser_service');

        $output->writeln('<begin-output>['. $dateStart->format('Y-m-d h:m:s') .'] parser.BEGIN:</> <options=bold>Start importing data to database</>');

        if ($testOption) {
            /** @var EntityManager $em */
            $em = $this->getContainer()->get('doctrine.orm.entity_manager');
            $evm = $em->getEventManager();
            $evm->dispatchEvent('testEvent');

            $output->writeln('<begin-output>['. $dateStart->format('Y-m-d h:m:s') .'] parser.TEST:</> <options=bold>You use the test mode</>');
        }

        if($clearTableOption) {
            $parserService->clearItemTable();

            $output->writeln('<begin-output>['. $dateStart->format('Y-m-d h:m:s') .'] parser.NOTICE:</> <options=bold>Clear mode of database was activated and has success.</>');
        }

        $result = $parserService->parse($file);

        if($result->getSuccessCount() > 0) {
            $dateEnd = $result->getEndTime()->format('Y-m-d h:m:s');

            $successStyle = new OutputFormatterStyle('black', 'green');
            $output->getFormatter()->setStyle('success-output', $successStyle);

            $output->writeln('<success-output>['. $dateEnd .'] parser.SUCCESS:</> <options=bold>Congratulation, you have successful imported data</>');
        }
    }
}