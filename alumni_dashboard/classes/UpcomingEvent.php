<?php

class UpcomingEvent {
    private $conn;
    private $title;
    private $date;
    private $description;
    private $icon;
    private $location;
    private $organizer;

    public function __construct($conn, $title = '', $date = '', $description = '', $icon = 'fa-calendar-alt', $location = '', $organizer = '', $id = null) {
        $this->conn = $conn;
        $this->title = $title;
        $this->date = $date;
        $this->description = $description;
        $this->icon = $icon;
        $this->location = $location;
        $this->organizer = $organizer;
        $this->id = $id;
    }

    public function getAllEvents() {
        $query = "SELECT * FROM events ORDER BY date ASC";
        $result = $this->conn->query($query);
        return $result;
    }

    public function addEvent($title, $date, $description, $location, $organizer) {
        $stmt = $this->conn->prepare("INSERT INTO events (title, date, description, location, organizer) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $date, $description, $location, $organizer);
        return $stmt->execute();
    }

    public function deleteEvent($event_id) {
        $stmt = $this->conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        return $stmt->execute();
    }

    public function display() {
        return "<div class='event-card'>
                    <i class='fas {$this->icon}'></i>
                    <h3>{$this->title}</h3>
                    <p class='event-date'>Date: {$this->date}</p>
                    <p>Location: {$this->location}</p>
                    <p>Organizer: {$this->organizer}</p>
                    <p>{$this->description}</p>
                </div>";
    }

    public function getIcon() {
        return $this->icon;
    }
}
?>
