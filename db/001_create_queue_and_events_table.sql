CREATE TABLE @queueTable (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(128),
    data BLOB,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    executed TINYINT(1) DEFAULT 0,
    executedAt TIMESTAMP,
    exitCode INT
);

CREATE TABLE @eventTable (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(128),
    cmd VARCHAR(255),
    dataAsCliParams TINYINT(1) DEFAULT 1
);

INSERT INTO @eventTable SET event = 'HELLO', cmd='/bin/echo';
INSERT INTO @queueTable SET event = 'HELLO', data='Hello queue!';
