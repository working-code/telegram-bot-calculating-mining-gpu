<?php
declare(strict_types=1);

namespace App\Command;

use App\Parser\HashRateParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'parser:hashRate:main',
    description: 'get and update main data',
)]
class MainHashRateParserCommand extends Command
{
    public function __construct(
        private readonly HashRateParser $hashRateParser,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->hashRateParser->getMainData();

        return static::SUCCESS;
    }
}
