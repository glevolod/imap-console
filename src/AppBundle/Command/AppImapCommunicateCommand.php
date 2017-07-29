<?php

namespace AppBundle\Command;

use AppBundle\Service\ImapCommunicate;
use AppBundle\Service\ImapCommunicator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppImapCommunicateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:imap-communicate')
            ->setDescription('Allows to communicate with mailbox by IMAP protocol');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

//        $username = readline('Username: ');
//        $password = readline('Password: ');

        $username = 'g86vladimir@gmail.com';
        $password = 'V_G862356q';


        /** @var ImapCommunicator $imapCommunicator */
        $imapCommunicator = $this->getContainer()->get('app.imap_communicator');

        $imapCommunicator->open($username, $password);

        $folders = $imapCommunicator->getFoldersNames();

        foreach ($folders as $key => $name) {
            $output->writeln($key . " " . $name);
        }


        $mails = $imapCommunicator->getFolderMailsList();

        foreach ($mails as $key => $mail) {
            $output->writeln($key . "\t" . $mail['from'] . "\t" . $mail['subject'] . "\t" . $mail['date']);
        }

        $mailBody = $imapCommunicator->getMailBody($mail['uid']);
        $output->writeln($mailBody);

        $output->writeln('Finished.');
    }

}
