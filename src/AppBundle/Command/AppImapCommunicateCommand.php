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
        $password = 'V_G862356qs';

        /** @var ImapCommunicator $imapCommunicator */
        $imapCommunicator = $this->getContainer()->get('app.imap_communicator');

        if (!$imapCommunicator->open($username, $password)) {
            $output->writeln($imapCommunicator->getLastErrorMessage());
            return;
        }
return;

        $previousCommand = 'start';

        $folders = [];
        $mails = [];
        $invitationText = 'Enter command: ';
        do {
            $userInput = readline($invitationText);
            if (empty($userInput)) {
                continue;
            }
            switch ($userInput) {
                case 'get_folders':
                    $previousCommand = 'get_folders';
                    $invitationText = 'Enter command or folder number: ';
                    $folders = $imapCommunicator->getFoldersNames();
                    foreach ($folders as $key => $folder) {
                        $output->writeln($key . " " . $folder['prettyName']);
                    }
                    break;
                case ($previousCommand == 'get_folders' && in_array($userInput, array_keys($folders))):
                    $previousCommand = 'folder_by_number';
                    $invitationText = 'Enter command or mail number: ';
                    $mails = $imapCommunicator->getFolderMailsList($folders[$userInput]['originalName']);
                    foreach ($mails as $key => $mail) {
                        $output->writeln($key . "\n" . $mail['from'] . "\n\t" . $mail['subject'] . "\n\t" . $mail['date']);
                    }
                    break;
                case (($previousCommand == 'folder_by_number' || $previousCommand == 'mail_by_number') && in_array($userInput, array_keys($mails))):
                    $previousCommand = 'mail_by_number';
                    $invitationText = 'Enter command or mail number: ';
                    $mailBody = $imapCommunicator->getMailBody($mails[$userInput]['uid']);
                    $output->writeln($mailBody);
                    break;
                case (mb_strtolower($userInput) == 'exit' || mb_strtolower($userInput) == 'quit'):
                    $output->writeln('Bye!');
                    return;
                    break;
                case (mb_strtolower($userInput) == 'help'):
                    $output->writeln('Available commands: get_folders, quit, exit, help');
                    break;
                default:
                    $output->writeln("Invalid command. Try to enter 'help'");
                    break;
            }
        } while (true);
    }
}
