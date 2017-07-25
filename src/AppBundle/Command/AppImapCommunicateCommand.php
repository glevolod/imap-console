<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AppImapCommunicateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:imap-communicate')
            ->setDescription('Allows to communicate with mailbox by IMAP protocol')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $username = readline('Username: ');
        $password = readline('Password: ');
        $output->writeln('Finished.');
    }

}
