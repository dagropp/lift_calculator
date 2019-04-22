CREATE DATABASE Car
  CHARSET = utf8
  COLLATE utf8_general_ci;

CREATE TABLE Car.company
(
  company_id   INT         NOT NULL AUTO_INCREMENT,
  company_name VARCHAR(40) NOT NULL,
  PRIMARY KEY (company_id),
  INDEX (company_name)
)
  ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci;

CREATE TABLE Car.model
(
  company_id INT         NOT NULL,
  model_id   INT         NOT NULL AUTO_INCREMENT,
  model_name VARCHAR(40) NOT NULL,
  gas_type   VARCHAR(40) NOT NULL,
  PRIMARY KEY (model_id),
  INDEX (company_id),
  INDEX (model_name),
  INDEX (gas_type),
  FOREIGN KEY (company_id) REFERENCES Car.company (company_id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
)
  ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci;

CREATE TABLE Car.year_range
(
  model_id     INT         NOT NULL,
  years_id     INT         NOT NULL AUTO_INCREMENT,
  years        VARCHAR(40) NOT NULL,
  km_per_liter FLOAT       NOT NULL,
  PRIMARY KEY (years_id),
  INDEX (model_id),
  INDEX (years),
  INDEX (km_per_liter),
  FOREIGN KEY (model_id) REFERENCES Car.model (model_id)
    ON DELETE CASCADE
    ON UPDATE RESTRICT
)
  ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci;

CREATE TABLE Car.prices
(
  service       VARCHAR(40) NOT NULL,
  price         FLOAT       NOT NULL,
  date_modified DATE        NOT NULL,
  UNIQUE (service),
  INDEX (price),
  INDEX (date_modified)
)
  ENGINE = InnoDB
  CHARSET = utf8
  COLLATE utf8_general_ci;

