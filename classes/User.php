<?php

class User {

    const TYPE_TRANSLATOR = 1;
    const TYPE_DEVELOPER = 2;

    protected $id;
    protected $type;
    protected $username;
    protected $join_date;

    public function __construct($id, $type, $username, $join_date) {
        $this->id = $id;
        $this->type = $type;
        $this->username = $username;
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

    public function isDeveloper() {
        return $this->type == self::TYPE_DEVELOPER;
    }

    public function isTranslator() {
        return $this->type == self::TYPE_TRANSLATOR;
    }

}