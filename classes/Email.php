<?php

/**
 * Sends emails (plain text or HTML) simply and conveniently
 */
class Email {

    protected $lines = array();
    protected $recipientsTo = array(); // list of visible recipients (To)
    protected $recipientsBCC = array(); // list of invisible recipients (BCC)
    protected $fromMail = '';
    protected $fromName = '';
    protected $subject = '';
    protected $is_html = false;

    /**
     * Creates a new email that may be sent later
     */
    public function __construct($fromMail, $fromName, $subject, $is_html = false) {
        $this->fromMail = trim($fromMail);
        $this->fromName = trim($fromName);
        $this->subject = trim($subject);
        $this->is_html = $is_html;
    }

    /**
     * Adds a new recipient for this message (may be an invisible BCC, optionally)
     */
    public function addRecipient($mail, $isBCC = false) {
        if ($isBCC) {
            $this->recipientsBCC[] = $mail;
        }
        else {
            $this->recipientsTo[] = $mail;
        }
    }

    /**
     * Adds a new line with arbitrary content to the message body
     */
    public function addLine($text) {
        $this->lines[] = $text;
    }

    /**
     * Sends the email that has already been prepared (recipient, sender and subject must be set)
     */
    public function send() {
        if (count($this->recipientsTo) <= 0 && count($this->recipientsBCC) <= 0) {
            throw new Exception('There must be at least one recipient (addRecipient)');
        }
        elseif (!isset($this->fromMail) || $this->fromMail == '') {
            throw new Exception('The sender\'s mail (fromMail) may not be empty');
        }
        elseif (!isset($this->subject) || $this->subject == '') {
            throw new Exception('The subject may not be empty');
        }
        else {
            $from_string = (isset($this->fromName) && $this->fromName != '') ? $this->fromName.' <'.$this->fromMail.'>' : $this->fromMail;
            $headers = array();
            $headers[] = 'From: '.$from_string;
            if (count($this->recipientsBCC) > 0) {
                $headers[] = 'Bcc: '.implode(', ', $this->recipientsBCC);
            }
            $headers[] = 'MIME-Version: 1.0';
            if ($this->is_html) {
                $headers[] = 'Content-type: text/html; charset=utf-8';
            }
            else {
                $headers[] = 'Content-type: text/plain; charset=utf-8';
            }
            $headers[] = 'Content-Transfer-Encoding: 8bit';
            mail(implode(', ', $this->recipientsTo), $this->subject, self::getBody(), implode("\r\n", $headers));
        }
    }

    protected function getBody() {
        return implode("\n", $this->lines);
    }

    /**
     * For debugging only: Returns the message (body) of the email to send
     */
    public function getText() {
        return self::getBody();
    }

}

?>