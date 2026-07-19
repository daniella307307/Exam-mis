-- ============================================================================
-- Lab Resources migration
--   1. facilitator_equipment_stock  — per-facilitator (independent) inventory
--   2. software_resources           — download/links catalogue for the lab
--   3. Seed software_resources
--   4. Add CodeRobo to `simulators`
--   5. Seed example kits (LEGP) into `laboratory_equipments`
-- Idempotent: safe to run more than once.
-- ============================================================================

-- 1. Per-facilitator equipment store ----------------------------------------
CREATE TABLE IF NOT EXISTS `facilitator_equipment_stock` (
    `stock_id`      INT(11)      NOT NULL AUTO_INCREMENT,
    `owner_user_id` INT(11)      NOT NULL,               -- the facilitator (users.user_id)
    `school_id`     INT(11)      DEFAULT NULL,
    `equipment_id`  INT(11)      DEFAULT NULL,            -- laboratory_equipments.equipments_id (NULL = custom)
    `item_name`     VARCHAR(255) NOT NULL,
    `category_id`   INT(11)      DEFAULT NULL,            -- Equipment_categories.category_id
    `model_no`      VARCHAR(100) DEFAULT NULL,
    `quantity`      INT(11)      NOT NULL DEFAULT 0,
    `stock_status`  VARCHAR(20)  NOT NULL DEFAULT 'in_stock', -- in_stock | low_stock | out_of_stock
    `location`      VARCHAR(150) DEFAULT NULL,
    `notes`         TEXT         DEFAULT NULL,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`stock_id`),
    KEY `idx_owner` (`owner_user_id`),
    KEY `idx_school` (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Software / tools resources catalogue ------------------------------------
CREATE TABLE IF NOT EXISTS `software_resources` (
    `sw_id`          INT(11)      NOT NULL AUTO_INCREMENT,
    `sw_name`        VARCHAR(150) NOT NULL,
    `sw_description` TEXT         DEFAULT NULL,
    `sw_category`    VARCHAR(100) DEFAULT NULL,
    `sw_download_url` TEXT        DEFAULT NULL,
    `sw_platform`    VARCHAR(120) DEFAULT NULL,          -- e.g. "Windows, macOS, Linux" or "Web-based"
    `sw_type`        VARCHAR(20)  NOT NULL DEFAULT 'download', -- download | web
    `sw_icon`        VARCHAR(50)  DEFAULT 'fa-download',
    `sw_status`      VARCHAR(20)  NOT NULL DEFAULT 'Active',
    PRIMARY KEY (`sw_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Seed software_resources (only if table is empty) ------------------------
INSERT INTO `software_resources`
    (sw_name, sw_description, sw_category, sw_download_url, sw_platform, sw_type, sw_icon)
SELECT * FROM (
    SELECT 'Arduino IDE' AS a, 'Write, compile and upload code to Arduino Uno and other boards. The core tool for every microcontroller lesson.' AS b, 'IDEs & Code Editors' AS c, 'https://www.arduino.cc/en/software' AS d, 'Windows, macOS, Linux' AS e, 'download' AS f, 'fa-microchip' AS g
    UNION ALL SELECT 'Visual Studio Code', 'Free, lightweight code editor for C/C++, Python, web and PlatformIO. Great general-purpose editor for older students.', 'IDEs & Code Editors', 'https://code.visualstudio.com/download', 'Windows, macOS, Linux', 'download', 'fa-code'
    UNION ALL SELECT 'Thonny (Python IDE)', 'Beginner-friendly Python IDE — perfect for MicroPython on micro:bit, ESP32 and Raspberry Pi Pico.', 'IDEs & Code Editors', 'https://thonny.org/', 'Windows, macOS, Linux', 'download', 'fa-terminal'
    UNION ALL SELECT 'Python', 'The Python programming language runtime — required by Thonny and many robotics tools.', 'IDEs & Code Editors', 'https://www.python.org/downloads/', 'Windows, macOS, Linux', 'download', 'fa-python'
    UNION ALL SELECT 'CodeRobo', 'Browser-based coding & robotics platform for kids (ages 6-14) — drag-and-drop Blockly plus real Python with virtual robots. No install needed.', 'Robotics & Coding for Kids', 'https://www.coderobo.ai/', 'Web-based', 'web', 'fa-robot'
    UNION ALL SELECT 'Scratch Desktop', 'Offline version of Scratch for block-based coding when the lab has no internet.', 'Robotics & Coding for Kids', 'https://scratch.mit.edu/download', 'Windows, macOS', 'download', 'fa-puzzle-piece'
    UNION ALL SELECT 'mBlock', 'Block + Python coding for robots and Arduino, based on Scratch. Good bridge from blocks to text code.', 'Robotics & Coding for Kids', 'https://www.mblock.cc/en/download/', 'Windows, macOS, Linux, Web', 'download', 'fa-cubes'
    UNION ALL SELECT 'Fritzing', 'Design breadboard circuits, schematics and PCBs. Handy for documenting wiring in projects.', 'Electronics & Circuit Design', 'https://fritzing.org/download/', 'Windows, macOS, Linux', 'download', 'fa-bolt'
    UNION ALL SELECT 'KiCad', 'Full open-source suite for schematic capture and PCB design for advanced electronics work.', 'Electronics & Circuit Design', 'https://www.kicad.org/download/', 'Windows, macOS, Linux', 'download', 'fa-microchip'
    UNION ALL SELECT 'Tinkercad', 'Free online 3D design, electronics and Codeblocks — runs in the browser, nothing to install.', 'Design & 3D / CAD', 'https://www.tinkercad.com/', 'Web-based', 'web', 'fa-cube'
    UNION ALL SELECT 'Autodesk Fusion (for Education)', '3D CAD/CAM for designing robot parts and mechanical assemblies. Free for students and educators.', 'Design & 3D / CAD', 'https://www.autodesk.com/products/fusion-360/education', 'Windows, macOS', 'download', 'fa-drafting-compass'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `software_resources` LIMIT 1);

-- 4. Add CodeRobo to simulators (online simulators / learning resources) -----
INSERT INTO `simulators` (sim_name, sim_description, sim_link, sim_status)
SELECT 'CodeRobo — Coding & Robotics for Kids', 'ROBOTICS SIMULATION', 'https://www.coderobo.ai/', 'Active'
WHERE NOT EXISTS (
    SELECT 1 FROM `simulators` WHERE sim_link = 'https://www.coderobo.ai/'
);

-- 5. Seed example kit(s) into the shared equipment catalogue -----------------
INSERT INTO `laboratory_equipments`
    (equipments_name, equipments_ModelNo, equipments_description, equipments_category, equipments_subcategory, equipments_picture, equipments_datasheet, equipments_status)
SELECT 'LEGP Robotics Kit', 'LEGP', 'Modular robotics building kit with motors, sensors and controller — used for hands-on robot building lessons.', 3, 0, 'YYY', '', 'Active'
WHERE NOT EXISTS (
    SELECT 1 FROM `laboratory_equipments` WHERE equipments_name = 'LEGP Robotics Kit'
);
