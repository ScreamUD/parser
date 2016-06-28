<?php

namespace ParserBundle\Command;

use Ddeboer\DataImport\Exception\ValidationException;
use Ddeboer\DataImport\Exception\WriterException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ParserCommand
 * @package ParserBundle\Command
 */
class ParserCommand extends ContainerAwareCommand
{
    /** configuration */
    protected function configure()
    {
        $this
            ->setName('parser:command')
            ->setDescription('Importing items to database.')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Type a path to file'
            )->addOption(
                'test',
                null,
                InputOption::VALUE_NONE,
                'Use to run test mode'
            )->addOption(
                'clear-table',
                null,
                InputOption::VALUE_NONE,
                'Use it to clear all the data from your table'
            )->addOption(
                'cost',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                5
            )->addOption(
                'stock',
                null,
                InputOption::VALUE_OPTIONAL,
                '',
                10
            );
    }

    /** Executes configuration and data processing */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $testOption = $input->getOption('test');
        $clearTableOption = $input->getOption('clear-table');
        $dateStart = new \DateTime();

        // start type style
        $beginStyle = new OutputFormatterStyle('black', 'blue');
        $output->getFormatter()->setStyle('begin-output', $beginStyle);

        // success output style
        $successStyle = new OutputFormatterStyle('black', 'green');
        $output->getFormatter()->setStyle('success-output', $successStyle);

        /** @var \ParserBundle\Service\ParserService $parserService */
        $parserService = $this->getContainer()->get('service.parser_service');

        $output->writeln('<begin-output>['. $dateStart->format('Y-m-d h:m:s') .'] parser.BEGIN:</> <options=bold>Start of import data</>');

        if ($testOption) {
            $output->writeln('<begin-output>['. $dateStart->format('Y-m-d h:m:s') .'] parser.TEST:</> <options=bold>You are using test mode</>');
        }

        if($clearTableOption) {
            $parserService->clearItemTable();
            $output->writeln('<begin-output>['. $dateStart->format('Y-m-d h:m:s') .'] parser.NOTICE:</> <options=bold>Clear mode is activated</>');
        }

        $restrictions = array(
            'cost' => $input->getOption('cost'),
            'stock' => $input->getOption('stock'),
        );
        
        $result = $parserService->parse($file, $restrictions, $testOption);

        $errors = $result->getExceptions();
        $dateEnd = $result->getEndTime();
        $parseErrors = $result->getErrors();
        $countErrors = $result->getCountErrors();

        if (!empty($parseErrors)) {
            $output->writeln('<error>[' . $dateStart->format('Y-m-d h:m:s') . '] parser.ERROR:</error> Parse errors - lines ' . implode(', ', $parseErrors));
        }
        foreach ($errors as $error) {
            if ($error instanceof ValidationException) {
                $violations = $error->getViolations();
                $lineNumber = $error->getLineNumber();
                $aErrors = [];

                foreach ($violations as $violation) {
                    $aErrors[] = $violation->getMessage();
                }

                $output->writeln('<error>[' . $dateStart->format('Y-m-d h:m:s') . '] parser.ERROR:</error> ' . implode(', ', $aErrors) . ' - line ' . $lineNumber);
            } else {
                $output->writeln('<error>[' . $dateStart->format('Y-m-d h:m:s') . '] parser.ERROR:</error> ' . $error->getMessage());
            }
        }

        $output->writeln('<info>[' . $dateEnd . '] parser.INFO:</info> Successful items - <fg=green;options=bold>' . $result->getSuccessCount() . '</>, failture items - <fg=red;options=bold>' . $countErrors . '</>');

        if ($result->getSuccessCount() > 0) {
            $output->writeln('<success-output>[' . $dateEnd . '] parser.SUCCESS:</> <options=bold>Congratulations, your data has been imported</>');
        }
    }
}