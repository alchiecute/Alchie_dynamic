-- Secure Dynamic Personal Portfolio - database schema + seed data
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS settings (
    `key` VARCHAR(60) PRIMARY KEY,
    `value` TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS goals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS skills (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    percent TINYINT UNSIGNED NOT NULL DEFAULT 50,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    category ENUM('web','design','animation') NOT NULL DEFAULT 'web',
    description TEXT NOT NULL,
    details TEXT NULL,
    tech VARCHAR(200) NOT NULL DEFAULT '',
    image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS timeline (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(40) NOT NULL,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    image VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    ip VARCHAR(45) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (ip, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    username VARCHAR(60) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (ip, attempted_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_hash CHAR(64) NOT NULL,
    page VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_at),
    INDEX (page)
) ENGINE=InnoDB;

-- ---------- Seed data (from your original static site) ----------
INSERT INTO settings (`key`, `value`) VALUES
('logo_text', 'AC'),
('full_name', 'Alchie Chavez'),
('first_name', 'Alchie'),
('last_name', 'Chavez'),
('typing_words', 'IT Student,Web Developer,Creative Designer,Technology Learner'),
('intro', 'I am an Information Technology student passionate about web development, design, animation, and technology. I enjoy creating modern and user-friendly digital experiences.'),
('profile_image', 'assets/images/profile1.png'),
('about_text', 'I am an Information Technology student interested in web development, graphic design, animation, and modern technology.\n\nI enjoy learning through hands-on projects and transforming ideas into creative digital outputs.\n\nMy goal is to become a skilled IT professional who can develop useful, responsive, and user-friendly systems.'),
('education', 'Information Technology'),
('focus', 'Web Development & Design'),
('goal', 'Become an IT Professional'),
('interests', 'Coding, Design, Animation, Technology'),
('email', 'alchiechavz.16@gmail.com'),
('github_url', 'https://github.com/'),
('linkedin_url', 'https://www.linkedin.com/'),
('phone', '09750453688'),
('available_for', 'Academic collaborations, creative projects, web development practice, and learning opportunities.');

INSERT INTO goals (title, description, sort_order) VALUES
('Learn', 'Continue improving my programming, design, and technical skills.', 1),
('Create', 'Build useful and creative websites and applications.', 2),
('Grow', 'Develop my communication, teamwork, and professional skills.', 3);

INSERT INTO skills (name, percent, sort_order) VALUES
('HTML5', 90, 1), ('CSS3', 85, 2), ('JavaScript', 70, 3), ('Responsive Design', 85, 4),
('UI/UX Design', 78, 5), ('Adobe Animate', 82, 6), ('Graphic Design', 80, 7),
('Git & GitHub', 75, 8), ('Problem Solving', 85, 9), ('Communication', 88, 10);

INSERT INTO projects (title, category, description, details, tech, image, sort_order) VALUES
('Baroque Perfume Advertisement', 'animation',
 'An animated perfume advertisement created using Adobe Animate. The project focuses on elegant product presentation and promotional messaging.',
 'A Baroque-inspired animated advertisement created for an academic animation project.',
 'Adobe Animate · Animation · Graphic Design', 'assets/images/project1.png', 1),
('Personal Portfolio Website', 'web',
 'A responsive portfolio website built with HTML, CSS, JavaScript, PHP and MySQL.',
 'A secure, dynamic portfolio featuring an admin dashboard, dark mode, project filtering, modal windows, and a protected contact form.',
 'HTML · CSS · JavaScript · PHP · MySQL', 'assets/images/project2.png', 2),
('Student Information System', 'web',
 'A concept system for organizing and displaying student information.',
 'A student-focused website concept designed to organize and present information through a simple interface.',
 'HTML · CSS · JavaScript', 'assets/images/project3.png', 3),
('Digital Poster Collection', 'design',
 'A collection of digital poster concepts exploring typography, layout, and visual communication.',
 'A collection of poster designs created to practice visual hierarchy, typography, composition, and communication.',
 'Graphic Design · Typography · Layout', 'assets/images/project4.png', 4);

INSERT INTO timeline (label, title, description, sort_order) VALUES
('Education', 'Information Technology Student', '3rd-Year BSIT Student currently developing knowledge in programming, web development, databases, networking, multimedia, and information technology.', 1),
('Academic Project', 'Baroque Perfume Advertisement', 'Created an animated advertisement using Adobe Animate, including product presentation, motion design, promotional content, and visual storytelling.', 2),
('Training', 'Web Development Practice', 'Practicing HTML, CSS, JavaScript, PHP, MySQL, responsive design, Git, and GitHub through hands-on projects.', 3),
('Activities', 'Creative & Technology Activities', 'Participating in academic activities involving design, animation, presentations, teamwork, and technology.', 4);

INSERT INTO certificates (title, image) VALUES
('Cybersecurity - ADBI Institute (25 September 2026)', 'assets/images/certificate.png');

-- NOTE: no admin account is created here. Open /admin/setup.php once to create yours.
