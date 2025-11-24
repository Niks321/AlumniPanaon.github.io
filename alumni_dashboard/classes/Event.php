<?php
abstract class Event { // Abstraction
    protected $title; // Encapsulation
    protected $date;
    protected $description;

    public function __construct($title, $date, $description) {
        $this->title = $title;
        $this->date = $date;
        $this->description = $description;
    }

    abstract public function display(); // Abstract method for polymorphism

    public function getTitle() {
        return $this->title;
    }

    public function getDate() {
        return $this->date;
    }

    public function getDescription() {
        return $this->description;
    }
}
?>
