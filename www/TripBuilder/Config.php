<?php
namespace TripBuilder;

# Web services configuration
class Config {
const

# Database Source Name (PDO DSN)
DB_SOURCE = 'sqlite:../TripBuilder.db',
# Examples :
# MySQL socket --> 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=TripBuilder'
# MySQL TCP/IP --> 'mysql:host=localhost;port=3306;dbname=TripBuilder'
# MS SQL Server -> 'sqlsrv:Server=localhost,1521;Database=TripBuilder'
# PostgreSQL ----> 'pgsql:host=localhost;port=5432;dbname=TripBuilder'
# Oracle DB -----> 'oci:dbname=//localhost:1521/TripBuilder'
# SQLite 3 ------> 'sqlite:C:/Users/Vince/Desktop/TripBuilder.db'

# Connection to database
DB_USERNAME = 'root',
DB_PASSWORD = '';

}