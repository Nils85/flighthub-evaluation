# Trip Builder

PHP Coding Assignment

## Easy to follow instructions

- Download PHP for your OS (version 5.6 minimum)
- Install PHP and enable the PDO driver for SQLite
- Open a terminal (or command prompt) and type :  
`cd ~/trip-builder/www` move in the directory of the project  
`php -S localhost:8000`
- Go to http://localhost:8000 with your browser.
- You have nothing else to do if you use SQLite!

Of course you can also try this project on a usual web server like Apache, Nginx...

## Use another data storage

This project use SQLite by default to simplify installation. But it works on other database system like :

- MySQL
- PostgreSQL
- Microsoft SQL Server
- Oracle DB
- etc...

For that, the corresponding PDO driver is needed and you have to modify the file in "TripBuilder/Config.php" and create manually the database on your data storage.

## Web services documentation

RESTful web services using JSON.

### Web API to list airports

http://82.251.141.181/api/list_airports.php  
http://82.251.141.181/api/list_airports.php?from=YUL

Param "from": Show only connections from this airport (optional)  
Return: JSON

### Web API to list available flights

http://82.251.141.181/api/list_flights.php?from=CDG&to=YYZ

Param "from": Code departure airport  
Param "to": Code arrival airport  
Return: JSON

### Web API to book a trip

Method: POST JSON  
Param "flights": [{"flight":"AC301","date":"2019-12-31"},{"flight":"AC302","date":"2019-12-31"}...]  
Return: Text (confirmation message)

### Web API to list trips booked

http://82.251.141.181/api/list_trips.php  
http://82.251.141.181/api/list_trips.php?sort=price

Param "sort": Order by one column (optional)  
Return: JSON