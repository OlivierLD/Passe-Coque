
For local install, see https://dev.mysql.com/doc/mysql-shell/8.0/en/mysql-shell-install-macos-quick.html
Also see: https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql

Also see the top of create.passe-coque.schema.sql

From CLI:
$ mysql -u root -p [!DonPedro123]

mysql> CREATE DATABASE pcDB;  -- No Quotes !
mysql> CREATE USER 'pc'@'localhost' IDENTIFIED BY 'pc';
-- default database ?
mysql> GRANT ALL PRIVILEGES ON *.* TO 'pc'@'localhost' WITH GRANT OPTION;
mysql> exit


$ mysql -u pc -p
mysql> show databases;
mysql> use pcDB;
mysql> show tables;

mysql> source sample.sql
mysql> show tables;
+----------------+
| Tables_in_pcdb |
+----------------+
| contacts       |
+----------------+
1 row in set (0.00 sec)

mysql> desc contacts;
+-------+--------------+------+-----+---------+----------------+
| Field | Type         | Null | Key | Default | Extra          |
+-------+--------------+------+-----+---------+----------------+
| id    | int          | NO   | PRI | NULL    | auto_increment |
| name  | varchar(255) | NO   |     | NULL    |                |
| email | varchar(320) | NO   |     | NULL    |                |
+-------+--------------+------+-----+---------+----------------+
3 rows in set (0.00 sec)

mysql>

# TODO: import / export

