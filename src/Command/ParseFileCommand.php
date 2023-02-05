<?php

namespace App\Command;

use App\Service\CommissionFeeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\FileParserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'app:calculate-fees',
    description: 'Calculate fees based on CSV data file.'
)]
class ParseFileCommand extends Command
{
    public function __construct(
        private readonly FileParserManager $parser,
        private readonly CommissionFeeManager $feeManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'Filename.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $input->getArgument('filename');

        if (!file_exists($filename)) {
            $output->writeln('Error: selected file cannot be found.');
            return Command::INVALID;
        }

        $fees = $this->feeManager->calculate($this->parser->parse($filename));

        foreach ($fees as $fee) {
            $output->writeln($fee);
        }

        return Command::SUCCESS;
    }
}
