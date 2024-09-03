<?php

namespace App\Command;

use App\Service\CommissionCalculator\CommissionCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:calculate-commission',
    description: 'Calculates commissions for performed transactions.',
    aliases: ['app:calculate-commission'],
    hidden: false
)]
class CalculateCommissionCommand extends Command
{
    private string $projectDir;
    private string $fileName;
    private CommissionCalculator $commissionCalculator;

    public function __construct(KernelInterface $kernel, CommissionCalculator $commissionCalculator)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir() . '/public/';
        $this->commissionCalculator = $commissionCalculator;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Calculate commissions for performed transactions based on txt input file')
            ->setHelp('This command allows you to calculate commissions for already made transactions using Commission Calculator Service')
            ->addArgument('filename', InputArgument::REQUIRED, 'The name of the file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fileName = $input->getArgument('filename');
        $transactionFileContent = file_get_contents($this->projectDir . $this->fileName);
        $output = $this->commissionCalculator->calculate($transactionFileContent);

        foreach ($output as $outputLine) {
            echo $outputLine;
            print "\n";
        };

        return Command::SUCCESS;
    }
}