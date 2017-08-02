<?php

namespace AppBundle\Command;

use AppBundle\Service\ImapCommunicate;
use AppBundle\Service\ImapCommunicator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppImapCommunicateCommand extends ContainerAwareCommand
{

    const COMM_GET_FOLDERS = 'getFolders';
    const COMM_GET_FOLDER_MAILS_BY_NUMBER = 'getFolderMailsByNumber';
    const COMM_GET_MAIL_BY_NUMBER = 'getMailByNumber';
    const COMM_HELP = 'help';
    const COMM_QUIT = 'quit';

    protected function configure()
    {
        $this
            ->setName('app:imap-communicate')
            ->setDescription('Allows to communicate with mailbox by IMAP protocol');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $username = readline('Username: ');
        $password = readline('Password: ');

        /** @var ImapCommunicator $imapCommunicator */
        $imapCommunicator = $this->getContainer()->get('app.imap_communicator');

        if (!$imapCommunicator->open($username, $password)) {
            $output->writeln($imapCommunicator->getLastErrorMessage());
            return;
        }
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
                case self::COMM_GET_FOLDERS:
                    $previousCommand = self::COMM_GET_FOLDERS;
                    $invitationText = 'Enter command or folder number: ';
                    $folders = $imapCommunicator->getFoldersNames();
                    foreach ($folders as $key => $folder) {
                        $output->writeln($key . " " . $folder['prettyName']);
                    }
                    break;
                case ($previousCommand == self::COMM_GET_FOLDERS && in_array($userInput, array_keys($folders))):
                    $previousCommand = self::COMM_GET_FOLDER_MAILS_BY_NUMBER;
                    $invitationText = 'Enter command or mail number: ';
                    $mails = $imapCommunicator->getFolderMailsList($folders[$userInput]['originalName']);
                    foreach ($mails as $key => $mail) {
                        $output->writeln($key . "\n" . $mail['from'] . "\n\t" . $mail['subject'] . "\n\t" . $mail['date']);
                    }
                    break;
                case (($previousCommand == self::COMM_GET_FOLDER_MAILS_BY_NUMBER || $previousCommand == self::COMM_GET_MAIL_BY_NUMBER) && in_array($userInput, array_keys($mails))):
                    $previousCommand = self::COMM_GET_MAIL_BY_NUMBER;
                    $invitationText = 'Enter command or mail number: ';
                    $mailBody = $imapCommunicator->getMailBody($mails[$userInput]['uid']);
                    $output->writeln($mailBody);
                    break;
                case (mb_strtolower($userInput) == self::COMM_QUIT):
                    $output->writeln('Bye!');
                    return;
                    break;
                case (mb_strtolower($userInput) == self::COMM_HELP):
                    $output->writeln('Available commands: ' . self::COMM_GET_FOLDERS . ', ' . self::COMM_QUIT . ', ' . self::COMM_HELP);
                    break;
                default:
                    $output->writeln("Invalid command. Try to enter 'help'");
                    break;
            }
        } while (true);
    }
}
