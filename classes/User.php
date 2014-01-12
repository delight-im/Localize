<?php

class User {

    const TYPE_TRANSLATOR = 1;
    const TYPE_DEVELOPER = 2;

    protected $id;
    protected $type;
    protected $username;
    protected $realName;
    protected $country;
    protected $timezone;
    protected $email;
    protected $email_lastVerificationAttempt;
    protected $join_date;

    public function __construct($id, $type, $username, $realName, $country, $timezone, $email, $email_lastVerificationAttempt, $join_date) {
        $this->id = $id;
        $this->type = $type;
        $this->username = $username;
        $this->realName = $realName;
        $this->country = $country;
        $this->timezone = $timezone;
        $this->email = $email;
        $this->email_lastVerificationAttempt = $email_lastVerificationAttempt;
        $this->join_date = $join_date;
    }

    /**
     * @return int the user's ID
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @return string the user's name
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return string the user's name
     */
    public function getRealName() {
        return $this->realName;
    }

    /**
     * @return string the user's country code
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * @return string the user's timezone name
     */
    public function getTimezone() {
        return $this->timezone;
    }

    /**
     * @return string the user's email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return int the UNIX timestamp of the user's last verification attempt for their email address
     */
    public function getEmail_lastVerificationAttempt() {
        return $this->email_lastVerificationAttempt;
    }

    public function setRealName($realName) {
        return $this->realName = $realName;
    }

    public function setCountry($country) {
        return $this->country = $country;
    }

    public function setTimezone($timezone) {
        return $this->timezone = $timezone;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setEmail_lastVerificationAttempt($email_lastVerificationAttempt) {
        $this->email_lastVerificationAttempt = $email_lastVerificationAttempt;
    }

    public function isDeveloper() {
        return $this->type == self::TYPE_DEVELOPER;
    }

    public static function isEmailValid($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

}