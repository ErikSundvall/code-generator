<?php

namespace Console\Command;

use OpenEHR\Tools\CodeGen\Reader\BmmReader;
use OpenEHR\Tools\CodeGen\CodeGenerator;
use OpenEHR\Tools\CodeGen\Writer\BmmDenoWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BmmDenoCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('bmm:deno');
        $this->setDescription('Generate Deno library based on indicated BMM schema(s).');
        $this->addArgument(
            'read',
            InputArgument::IS_ARRAY,
            'BMM schema(s) to read; multiple schemas are supported when given as multiple arguments. '
            . 'Dependencies should be read first (i.e. first BASE then RM). '
            . 'Example: <info>generate bmm:deno openehr_base_1.2.0 openehr_rm_1.1.0</info>.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $toRead = $input->getArgument('read');
        if (empty($toRead)) {
            $output->writeln('<error>Please specify which BMM schema should be read. See usage with --help.</error>');
            return Command::INVALID;
        }
        try {
            $reader = new BmmReader();
            foreach ($toRead as $schema) {
                $reader->read($schema);
            }
            $writer = new CodeGenerator($reader);
            $writer->addWriter(new BmmDenoWriter());
            $writer->generate();
        } catch (\UnhandledMatchError $e) {
            $output->writeln((string)$e);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
