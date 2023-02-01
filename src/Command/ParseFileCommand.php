<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\FileParserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(
    name: 'app:parse-file',
    description: 'Parse a file.'
)]
class ParseFileCommand extends Command
{

    public function __construct(private readonly FileParserManager $parser)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filename', InputArgument::REQUIRED, 'Filename.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // @todo add validation

        $fees = $this->parser->parse($input->getArgument('filename'));

        foreach ($fees as $fee) {
            $output->writeln($fee);
        }

        return Command::SUCCESS;
    }
}
