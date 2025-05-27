<?php

class PostException extends Exception {
}

class Post implements \JsonSerializable {   
    private $Id;
    private $Author;
    private $Message;
    private $TimeStamp;

    function __construct($id, $author, $message) {
        $this->setId($id);
        $this->setAuthor($author);
        $this->setMessage($message);
        $this->setTimeStamp();
    }

    function setId($id) {
         $this->Id = $id;
    }
    function getId() {
        return $this->Id;
    }
    function setAuthor($author) {
        if (empty($author)) {
            throw new PostException("Author cannot be empty");
        } else if (strlen($author) > 50) {
            throw new PostException("Author name is too long, maximum 50 characters allowed");
        }
         $this->Author = $author;
    }

    function getAuthor() {
        return $this->Author;
    }
    function setMessage($message) {
         $this->Message = $message;
    }
    function getMessage() {
        return $this->Message;
    }
    function setTimeStamp() {
         $this->TimeStamp = date('Y-m-d H:i:s');
    }

    function getTimeStamp() {
        return $this->TimeStamp;
    }

    public function jsonSerialize() :mixed {
        return [
            'id' => $this->getId(),
            'author' => $this->getAuthor(),
            'message' => $this->getMessage(),
            'timestamp' => $this->getTimeStamp()
        ];
    }
}
