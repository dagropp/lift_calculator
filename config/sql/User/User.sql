CREATE DATABASE User
  CHARSET = utf8
  COLLATE utf8_general_ci;

CREATE TABLE User.users
(
  user_id        INT           NOT NULL AUTO_INCREMENT,
  email          VARCHAR(40)   NOT NULL,
  password       VARCHAR(100)  NOT NULL,
  first_name     VARCHAR(40)   NOT NULL,
  last_name      VARCHAR(40)   NOT NULL,
  phone_num      VARCHAR(11)   NOT NULL,
  car_years_id   INT           NOT NULL,
  admin_password VARBINARY(60) NULL     DEFAULT NULL,
  PRIMARY KEY (user_id),
  UNIQUE (email),
  INDEX (first_name),
  INDEX (last_name),
  INDEX (phone_num),
  INDEX (car_years_id)
)
  ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci;

CREATE TABLE User.drives
(
  user_id        INT          NOT NULL,
  drive_id       INT          NOT NULL AUTO_INCREMENT,
  origin         VARCHAR(40)  NOT NULL,
  destination    VARCHAR(40)  NOT NULL,
  distance       FLOAT        NOT NULL,
  duration       VARCHAR(5)   NOT NULL,
  passengers_num INT          NOT NULL,
  equal_division BOOLEAN      NOT NULL DEFAULT FALSE,
  toll_roads     VARCHAR(120) NOT NULL DEFAULT '[]',
  PRIMARY KEY (drive_id),
  INDEX (user_id),
  INDEX (origin),
  INDEX (destination),
  INDEX (duration),
  FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
)
  ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci;
