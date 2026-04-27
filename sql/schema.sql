-- Forever Together — MySQL schema
CREATE DATABASE IF NOT EXISTS forever_together CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forever_together;

DROP TABLE IF EXISTS memory_selections;
DROP TABLE IF EXISTS photos;
DROP TABLE IF EXISTS guests;
DROP TABLE IF EXISTS weddings;
DROP TABLE IF EXISTS couples;

CREATE TABLE couples (
  email        VARCHAR(190) PRIMARY KEY,
  password     VARCHAR(255) NOT NULL,
  name1        VARCHAR(100) NOT NULL,
  name2        VARCHAR(100) NOT NULL,
  wedding_id   CHAR(8)      NOT NULL UNIQUE,
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE weddings (
  id            CHAR(8) PRIMARY KEY,
  couple_email  VARCHAR(190) NOT NULL,
  name1         VARCHAR(100) NOT NULL,
  name2         VARCHAR(100) NOT NULL,
  wedding_date  DATE         NOT NULL,
  venue         VARCHAR(255),
  theme         VARCHAR(40)  NOT NULL DEFAULT 'dark_romantic',
  card_style    VARCHAR(40)  NOT NULL DEFAULT 'dark',
  created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_weddings_couple FOREIGN KEY (couple_email)
    REFERENCES couples(email) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE guests (
  username        VARCHAR(40) PRIMARY KEY,
  password_hash   VARCHAR(255) NOT NULL,
  password_plain  VARCHAR(40)  NOT NULL,
  name            VARCHAR(100) NOT NULL,
  email           VARCHAR(190),
  wedding_id      CHAR(8)      NOT NULL,
  created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_guests_wedding (wedding_id),
  CONSTRAINT fk_guests_wedding FOREIGN KEY (wedding_id)
    REFERENCES weddings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE photos (
  id              CHAR(12) PRIMARY KEY,
  wedding_id      CHAR(8)      NOT NULL,
  guest_username  VARCHAR(40)  NOT NULL,
  file_path       VARCHAR(255) NOT NULL,
  message         VARCHAR(300),
  uploaded_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_photos_wedding (wedding_id),
  INDEX idx_photos_guest (guest_username),
  CONSTRAINT fk_photos_wedding FOREIGN KEY (wedding_id)
    REFERENCES weddings(id) ON DELETE CASCADE,
  CONSTRAINT fk_photos_guest FOREIGN KEY (guest_username)
    REFERENCES guests(username) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE memory_selections (
  wedding_id  CHAR(8)  NOT NULL,
  photo_id    CHAR(12) NOT NULL,
  position    INT      NOT NULL,
  PRIMARY KEY (wedding_id, photo_id),
  CONSTRAINT fk_ms_wedding FOREIGN KEY (wedding_id)
    REFERENCES weddings(id) ON DELETE CASCADE,
  CONSTRAINT fk_ms_photo FOREIGN KEY (photo_id)
    REFERENCES photos(id) ON DELETE CASCADE
) ENGINE=InnoDB;
