<?php

namespace Tests\ParserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ParserBundle\Command\ParserCommand;

class ParserCommandTest extends KernelTestCase
{
    public $path;
    public $filename;
    private $application;

    /**
     * Test of existing file and correct
     */
    public function testExecute()
    {
        $command = $this->application->find('parser:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => $this->path . $this->filename,
            '--test' => 1,
            '--clear-table' => 0
        ]);

        $this->assertFileExists($this->path . $this->filename);
        $this->assertRegExp('/BEGIN/', $commandTester->getDisplay());
        $this->assertRegExp('/SUCCESS/', $commandTester->getDisplay());
    }


    /**
     * Test with mode clear-table
     */
    public function testClearTable()
    {
        $command = $this->application->find('parser:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => $this->path . $this->filename,
            '--test' => 1,
            '--clear-table' => 1
        ]);

        $this->assertFileExists($this->path . $this->filename);
        $this->assertRegExp('/BEGIN/', $commandTester->getDisplay());
        $this->assertRegExp('/NOTICE/', $commandTester->getDisplay());
    }

    /**
     * throw exception of not existing file
     *
     * @expectedException \Exception
     */
    public function testExistenceFile()
    {
        $command = $this->application->find('parser:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => $this->path . 'not-existence-file.csv',
            '--test' => 1,
            '--clear-table' => 0
        ]);
    }

    /**
     * throw exception of invalid file format extension
     *
     * @expectedException \Exception
     */
    public function testInvalidFormatExtension()
    {
        $command = $this->application->find('parser:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => $this->path . 'invalid-format.yml',
            '--test' => 1,
            '--clear-table' => 0
        ]);
    }

    /**
     * test file with parse errors
     */
    public function testWithParseErrors()
    {
        $command = $this->application->find('parser:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => $this->path . 'stock-with-parse-errors.csv',
            '--test' => 1,
            '--clear-table' => 0
        ]);
    }

    /**
     * throw exception of incorrect format data from file
     *
     * @expectedException \Exception
     */
    public function testInvalidFormatData()
    {
        $command = $this->application->find('parser:command');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'file' => $this->path . 'invalid-format-data.csv',
            '--test' => 1,
            '--clear-table' => 0
        ]);
    }

    /**
     * application settings
     */
    public function setUp()
    {
        $kernel = $this->createKernel();
        $kernel->boot();
        $this->application = new Application($kernel);
        $this->application->add(new ParserCommand());

        $this->path = $kernel->getRootDir() . '/TestFiles/';
        $this->filename = 'stock.csv';
    }
}