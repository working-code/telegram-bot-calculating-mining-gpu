<?php
declare(strict_types=1);

namespace App\Command;

use App\Parser\CurrencyParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'parser:currency:usd',
    description: 'get and update usd'
)]
class CurrencyUsdParserCommand extends Command
{
    public function __construct(
        private readonly CurrencyParser $currencyParser,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->currencyParser->updateCurrency();

        return Command::SUCCESS;
    }
}
