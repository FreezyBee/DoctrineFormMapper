CREATE TABLE Address (id INTEGER NOT NULL, street VARCHAR(255) NOT NULL, PRIMARY KEY(id));
CREATE TABLE Article (id INTEGER NOT NULL, flag INTEGER, author_id INTEGER DEFAULT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_CD8737FAF675F31B FOREIGN KEY (author_id) REFERENCES Author (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_CD8737FAF675F31B ON Article (author_id);

CREATE TABLE article_tag (article_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(article_id, tag_id), CONSTRAINT FK_919694F97294869C FOREIGN KEY (article_id) REFERENCES Article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_919694F9BAD26311 FOREIGN KEY (tag_id) REFERENCES Tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE INDEX IDX_919694F97294869C ON article_tag (article_id);
CREATE INDEX IDX_919694F9BAD26311 ON article_tag (tag_id);

CREATE TABLE Car (id INTEGER NOT NULL, license VARCHAR(255) NOT NULL, PRIMARY KEY(id));

CREATE TABLE Author (id INTEGER NOT NULL, address_id INTEGER DEFAULT NULL, car_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, age INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_BA03DDFEF5B7AF75 FOREIGN KEY (address_id) REFERENCES Address (id) NOT DEFERRABLE INITIALLY IMMEDIATE);
CREATE UNIQUE INDEX UNIQ_BA03DDFEF5B7AF75 ON Author (address_id);
CREATE UNIQUE INDEX UNIQ_BA03DDFEF5B7AF76 ON Author (car_id);

CREATE TABLE Tag (id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));

CREATE TABLE UuidCart (id VARCHAR(36) NOT NULL, product_id VARCHAR(36) NOT NULL, PRIMARY KEY(id));
CREATE TABLE UuidProduct (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id));

INSERT INTO Address (id, street) VALUES (1, 'address street1');
INSERT INTO Address (id, street) VALUES (2, 'address street2');
INSERT INTO Address (id, street) VALUES (3, 'address street3');

INSERT INTO Car (id, license) VALUES (10001, 'license1');

INSERT INTO Author (id, address_id, car_id, name, age) VALUES (11, 1, 10001, 'author name1', 666);
INSERT INTO Author (id, address_id, name, age) VALUES (12, 2, 'author name2', 665);
INSERT INTO Author (id, address_id, name, age) VALUES (13, 3, 'author name3', 0);

INSERT INTO Article (id, flag, author_id, title) VALUES (101, 1, 11, 'article title1');
INSERT INTO Article (id, flag, author_id, title) VALUES (102, 2, 12, 'article title2');

INSERT INTO Tag (id, name) VALUES (1001, 'tag name1');
INSERT INTO Tag (id, name) VALUES (1002, 'tag name2');
INSERT INTO Tag (id, name) VALUES (1003, 'tag name3');
INSERT INTO Tag (id, name) VALUES (1004, 'tag name4');

INSERT INTO article_tag (article_id, tag_id) VALUES (101, 1001);
INSERT INTO article_tag (article_id, tag_id) VALUES (101, 1002);

INSERT INTO UuidProduct (id, name) VALUES ('7ec0407c-e7da-48d7-80d6-3b98c4002c21', 'product1');
INSERT INTO UuidProduct (id, name) VALUES ('7ec0407c-e7da-48d7-80d6-3b98c4002c22', 'product2');

INSERT INTO UuidCart (id, product_id) VALUES ('7ec0407c-e7da-48d7-80d6-3b98c4002c00', '7ec0407c-e7da-48d7-80d6-3b98c4002c21');
