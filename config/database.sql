    -- Drop existing tables if they exist (in reverse order of dependencies)
    DROP TABLE IF EXISTS event_registrations;
    DROP TABLE IF EXISTS donations;
    DROP TABLE IF EXISTS events;
    DROP TABLE IF EXISTS users;

    -- Create users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        address TEXT,
        bio TEXT,
        role ENUM('admin', 'volunteer') NOT NULL DEFAULT 'volunteer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Create events table
    CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        date_time DATETIME NOT NULL,
        location VARCHAR(255) NOT NULL,
        max_volunteers INT NOT NULL,
        status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open',
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Create donations table
    CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        status ENUM('Pending', 'Completed', 'Failed') NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Create event_registrations table
    CREATE TABLE IF NOT EXISTS event_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        status ENUM('Registered', 'Attended', 'Cancelled') NOT NULL DEFAULT 'Registered',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE KEY unique_registration (user_id, event_id)
    );

    -- Insert default admin user
    INSERT INTO users (username, password, full_name, email, role) 
    VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin@volunteerhub.com', 'admin');

    -- Create indexes for better performance
    CREATE INDEX idx_events_date ON events(date_time);
    CREATE INDEX idx_donations_user ON donations(user_id);
    CREATE INDEX idx_event_registrations_user ON event_registrations(user_id);
    CREATE INDEX idx_event_registrations_event ON event_registrations(event_id); 