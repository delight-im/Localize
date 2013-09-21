<?php

class User {

    const TYPE_TRANSLATOR = 1;
    const TYPE_DEVELOPER = 2;

    protected $id;
    protected $type;
    protected $username;
    protected $realName;
    protected $join_date;

    public function __construct($id, $type, $username, $realName, $join_date) {
        $this->id = $id;
        $this->type = $type;
        $this->username = $username;
        $this->realName = $realName;
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

    public function setRealName($realName) {
        return $this->realName = $realName;
    }

    public function isDeveloper() {
        return $this->type == self::TYPE_DEVELOPER;
    }

}