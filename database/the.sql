SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `customers` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_contact_no` (`contact_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `books` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `customer_id` int(30) NOT NULL,
  `movie_id` int(30) NOT NULL,
  `ts_id` int(30) NOT NULL,
  `qty` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` enum('active','cancelled') NOT NULL DEFAULT 'active',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `movie_id` (`movie_id`),
  KEY `ts_id` (`ts_id`),
  KEY `customer_id` (`customer_id`),
  KEY `idx_booking_date_time` (`date`, `time`),
  CONSTRAINT `check_qty_positive` CHECK (`qty` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `movies` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `cover_img` text NOT NULL,
  `description` text NOT NULL,
  `duration` float NOT NULL,
  `date_showing` date NOT NULL,
  `end_date` date NOT NULL,
  `trailer_yt_link` text NOT NULL,
  `status` enum('showing','ended','coming_soon') NOT NULL DEFAULT 'showing',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `theater` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `theater_settings` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `theater_id` int(30) NOT NULL,
  `seat_group` varchar(250) NOT NULL,
  `seat_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `theater_id` (`theater_id`),
  CONSTRAINT `check_seat_count_positive` CHECK (`seat_count` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `user_type` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `movie_pricing` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `movie_id` int(30) NOT NULL,
  `theater_id` int(30) NOT NULL,
  `seat_group` varchar(250) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `movie_id` (`movie_id`),
  KEY `theater_id` (`theater_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `transaction_log` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `transaction_id` VARCHAR(36) NOT NULL,
  `table_name` VARCHAR(50) NOT NULL,
  `operation` ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
  `record_id` INT NOT NULL,
  `old_data` JSON,
  `new_data` JSON,
  `user_id` INT,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_transaction_id` (`transaction_id`),
  INDEX `idx_table_operation` (`table_name`, `operation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELIMITER $$

CREATE PROCEDURE `sp_create_booking`(
  IN p_customer_id INT,
  IN p_movie_id INT,
  IN p_ts_id INT,
  IN p_qty INT,
  IN p_date DATE,
  IN p_time TIME,
  OUT p_booking_id INT,
  OUT p_error_message VARCHAR(255)
)
BEGIN
  DECLARE v_seats_available INT;
  DECLARE v_transaction_id VARCHAR(36);
  DECLARE v_exit_handler BOOLEAN DEFAULT FALSE;
  
  DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
  BEGIN
    SET v_exit_handler = TRUE;
    GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
    SET p_error_message = CONCAT('Error: ', @errno, ' (', @sqlstate, '): ', @text);
    ROLLBACK;
  END;

  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
  START TRANSACTION;

  SET v_transaction_id = UUID();

  SELECT seat_count INTO v_seats_available
  FROM theater_settings
  WHERE id = p_ts_id
  FOR UPDATE;

  SELECT v_seats_available - COALESCE(SUM(qty), 0) INTO v_seats_available
  FROM books
  WHERE movie_id = p_movie_id
    AND ts_id = p_ts_id
    AND date = p_date
    AND time = p_time
    AND status = 'active'
  FOR UPDATE;

  IF v_seats_available >= p_qty THEN
    INSERT INTO books (customer_id, movie_id, ts_id, qty, date, time, status)
    VALUES (p_customer_id, p_movie_id, p_ts_id, p_qty, p_date, p_time, 'active');
    
    SET p_booking_id = LAST_INSERT_ID();
    
    INSERT INTO transaction_log (transaction_id, table_name, operation, record_id, new_data, user_id)
    VALUES (
      v_transaction_id,
      'books',
      'INSERT',
      p_booking_id,
      JSON_OBJECT(
        'customer_id', p_customer_id,
        'movie_id', p_movie_id,
        'ts_id', p_ts_id,
        'qty', p_qty,
        'date', p_date,
        'time', p_time
      ),
      NULL
    );
    
    IF v_exit_handler THEN
      SET p_booking_id = NULL;
    ELSE
      COMMIT;
      SET p_error_message = NULL;
    END IF;
  ELSE
    SET p_error_message = 'Insufficient seats available';
    SET p_booking_id = NULL;
    ROLLBACK;
  END IF;
END$$

CREATE TRIGGER `trg_prevent_deadlock` BEFORE UPDATE ON `books`
FOR EACH ROW
BEGIN
  DECLARE v_lock_acquired INT DEFAULT 0;
  UPDATE books SET id = id WHERE id = NEW.id;
  SET v_lock_acquired = 1;
  IF v_lock_acquired = 0 THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Failed to acquire row lock';
  END IF;
END$$

CREATE PROCEDURE `sp_cancel_booking`(
  IN p_booking_id INT,
  IN p_user_id INT,
  OUT p_error_message VARCHAR(255)
)
BEGIN
  DECLARE v_transaction_id VARCHAR(36);
  DECLARE v_old_status VARCHAR(10);
  DECLARE v_exit_handler BOOLEAN DEFAULT FALSE;
  
  DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
  BEGIN
    SET v_exit_handler = TRUE;
    GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE, @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
    SET p_error_message = CONCAT('Error: ', @errno, ' (', @sqlstate, '): ', @text);
    ROLLBACK;
  END;

  SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
  START TRANSACTION;

  SET v_transaction_id = UUID();

  SELECT status INTO v_old_status
  FROM books
  WHERE id = p_booking_id
  FOR UPDATE;

  IF v_old_status = 'active' THEN
    UPDATE books
    SET status = 'cancelled'
    WHERE id = p_booking_id;

    INSERT INTO transaction_log (transaction_id, table_name, operation, record_id, old_data, new_data, user_id)
    VALUES (
      v_transaction_id,
      'books',
      'UPDATE',
      p_booking_id,
      JSON_OBJECT('status', v_old_status),
      JSON_OBJECT('status', 'cancelled'),
      p_user_id
    );

    IF v_exit_handler THEN
      SET p_error_message = 'Failed to cancel booking';
    ELSE
      COMMIT;
      SET p_error_message = NULL;
    END IF;
  ELSE
    SET p_error_message = 'Booking is not active';
    ROLLBACK;
  END IF;
END$$

DELIMITER ;

ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`ts_id`) REFERENCES `theater_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `books_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `theater_settings`
  ADD CONSTRAINT `theater_settings_ibfk_1` FOREIGN KEY (`theater_id`) REFERENCES `theater` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `movie_pricing`
  ADD CONSTRAINT `movie_pricing_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `movie_pricing_ibfk_2` FOREIGN KEY (`theater_id`) REFERENCES `theater` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE VIEW `vw_booking_details` AS
SELECT 
  b.id AS booking_id,
  b.date AS booking_date,
  b.time AS booking_time,
  b.qty AS seats_booked,
  b.status AS booking_status,
  c.firstname,
  c.lastname,
  c.contact_no,
  c.email,
  m.title AS movie_title,
  m.duration AS movie_duration,
  t.name AS theater_name,
  ts.seat_group,
  mp.price AS ticket_price,
  (b.qty * mp.price) AS total_amount
FROM books b
JOIN customers c ON b.customer_id = c.id
JOIN movies m ON b.movie_id = m.id
JOIN theater_settings ts ON b.ts_id = ts.id
JOIN theater t ON ts.theater_id = t.id
LEFT JOIN movie_pricing mp ON (m.id = mp.movie_id AND t.id = mp.theater_id AND ts.seat_group = mp.seat_group);
