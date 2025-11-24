<?php

class Announcement {
    private $conn;
    private $title;
    private $date;
    private $description;

    public function __construct($conn, $title = '', $date = '', $description = '', $id = null) {
        $this->conn = $conn;
        $this->title = $title;
        $this->date = $date;
        $this->description = $description;
        $this->id = $id;
    }

    public function getAllAnnouncements() {
        $query = "SELECT * FROM announcements ORDER BY date DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    public function addAnnouncement($title, $date, $description) {
        $stmt = $this->conn->prepare("INSERT INTO announcements (title, date, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $date, $description);
        return $stmt->execute();
    }

    public function deleteAnnouncement($announcement_id) {
        $stmt = $this->conn->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $stmt->bind_param("i", $announcement_id);
        return $stmt->execute();
    }

    public function display() {
        return "<h3>{$this->title}</h3>
                <p class='announcement-date'>Posted: {$this->date}</p>
                <p>{$this->description}</p>";
    }
}
?>
