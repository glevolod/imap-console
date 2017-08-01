<?php

namespace AppBundle\Service;


class ImapCommunicator
{
    const MAIL_BOX_PATH = '{imap.gmail.com:993/imap/ssl}';
    const NUM_MESSAGES = 10;
    private $imap;

    private $username;
    private $password;

    private $error;


    public function open($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        try {
            $this->imap = imap_open(self::MAIL_BOX_PATH, $username, $password);
        } catch (\Throwable $exception) {
            $this->error = $exception;
            return false;
        }
        return true;
    }

    public function getFoldersNames()
    {
        $folders = imap_list($this->imap, self::MAIL_BOX_PATH, "*");
        $folderNumber = 1;

        foreach ($folders as $folder) {
            $folderOriginalName = str_replace(self::MAIL_BOX_PATH, "", $folder);
            $folderPrettyName = str_replace(self::MAIL_BOX_PATH, "", mb_convert_encoding($folder, "UTF-8", "UTF7-IMAP"));
            $foldersArr[$folderNumber] = ['originalName' => $folderOriginalName, 'prettyName' => $folderPrettyName];
            $folderNumber++;
        }
        return $foldersArr;
    }

    public function getFolderMailsList($folderName = null)
    {
        $imap = imap_open(self::MAIL_BOX_PATH . $folderName, $this->username, $this->password);
        $numMessages = imap_num_msg($imap);
        $num = 1;
        $messageDetails = [];
        for ($i = $numMessages; $i > ($numMessages - self::NUM_MESSAGES) && $i != 0; $i--) {
            $header = imap_headerinfo($imap, $i, 50, 50);
            $messageDetails[$num++] = [
                'from' => imap_utf8($header->fromaddress),
                'subject' => imap_utf8($header->subject),
                'date' => imap_utf8($header->date),
                'uid' => imap_uid($imap, $i),
            ];
        }
        return $messageDetails;
    }

    public function getMailBody($uid)
    {
        $body = imap_fetchbody($this->imap, imap_msgno($this->imap, $uid), '1');
        if (empty($body)) {
            $body = imap_fetchbody($this->imap, imap_msgno($this->imap, $uid), '2');
        }
        return imap_qprint($body);
    }

    public function getLastErrorMessage()
    {
        if ($this->error instanceof \Throwable) {
            return $this->error->getMessage();
        }
    }
}