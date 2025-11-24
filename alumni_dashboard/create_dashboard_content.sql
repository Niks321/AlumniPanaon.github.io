USE alumni_system;

-- Create dashboard_content table
CREATE TABLE IF NOT EXISTS dashboard_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_title VARCHAR(255) NOT NULL DEFAULT '',
    hero_subtitle VARCHAR(255) NOT NULL DEFAULT '',
    stat1_number VARCHAR(50) NOT NULL DEFAULT '',
    stat1_label VARCHAR(100) NOT NULL DEFAULT '',
    stat2_number VARCHAR(50) NOT NULL DEFAULT '',
    stat2_label VARCHAR(100) NOT NULL DEFAULT '',
    stat3_number VARCHAR(50) NOT NULL DEFAULT '',
    stat3_label VARCHAR(100) NOT NULL DEFAULT '',
    stat4_number VARCHAR(50) NOT NULL DEFAULT '',
    stat4_label VARCHAR(100) NOT NULL DEFAULT '',
    hub_title VARCHAR(255) NOT NULL DEFAULT '',
    hub_text TEXT NOT NULL DEFAULT ''
) ENGINE=InnoDB;

-- Insert default row with id=1
INSERT INTO dashboard_content (id, hero_title, hero_subtitle, stat1_number, stat1_label, stat2_number, stat2_label, stat3_number, stat3_label, stat4_number, stat4_label, hub_title, hub_text)
VALUES (1, 'Welcome to AlumniPanaon Hub', 'Connect, Network, and Grow with Your Alumni Community', '1000', 'Alumni Members', '50', 'Events Hosted', '200', 'Success Stories', '10', 'Years of Excellence', 'Alumni Hub', 'This is the central hub for alumni activities, networking, and updates.')
ON DUPLICATE KEY UPDATE id=id;
