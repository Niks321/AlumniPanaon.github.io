<?php
require_once 'config/database.php';

$conn->query("CREATE TABLE IF NOT EXISTS org_chart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    display_order INT NOT NULL
) ENGINE=InnoDB");

$conn->query("INSERT INTO org_chart (name, position, display_order) VALUES
    ('Dr. Ambrosio B. Cultura II', 'USTP System President', 1),
    ('Mr. Ruel Baron', 'USTP Alumni Association President', 2),
    ('Dr. Leny Q. AÑasco', 'Campus Director', 3),
    ('John Philip A. Viajedor', 'Alumni Relation Officer', 4)");

echo 'Org chart table created and initial data inserted successfully.';
?>
