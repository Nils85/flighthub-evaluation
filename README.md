# Trip Builder

PHP Coding Assignment (see "Trip Builder.pdf").  
Web Services to ​build and navigate trips​ for a single passenger using criteria such as
departure locations, departure dates and arrival locations...

You can try this project locally on your computer or install it on a usual web server.

## Installation on your computer

- Download PHP 7 for your OS
- Install PHP and enable the PDO driver for SQLite in "php.ini"
- [Download the master branch of this project](https://bitbucket.org/Nils85/trip-builder/downloads/?tab=branches)
- Open a terminal (or command prompt) and type :  
`cd ~/trip-builder/www` to move in the directory of the project  
`php -S localhost:8000` to run the PHP built-in web server
- Open your browser and go to http://localhost:8000

## Installation on a web server

- Set up a server with Apache or Nginx and PHP version 5.4 minimum
- Install a database system like MySQL, Postgres, MS SQL Server, Oracle...
- Enable the PDO driver for your database
- Create manually the database that will be used by the project (without tables)
- `git clone https://bitbucket.org/Nils85/trip-builder.git`
- Modify the file "TripBuilder/Config.php" with your database parameters
- Deploy the folder "trip-builder/www" to your web server directory (htdocs)
- Open a browser and go to the web directory of your web server

## Web services documentation

RESTful web services using JSON.

### Web API to list airports

```
http://.../api/list_airports.php
http://.../api/list_airports.php?from=YUL
```

Method: GET  
Param "from": Show only connections from this airport (optional)  
Return: JSON

```
["YUL - Pierre Elliott Trudeau International (Montreal)",
"YVR - Vancouver International (Vancouver)"...]
```

### Web API to list available flights

```
http://.../api/list_flights.php?from=CDG&to=YYZ
```

Method: GET  
Param "from": Code departure airport  
Param "to": Code arrival airport  
Return: JSON

```
[{
"ID": "AC881",
"Name": "Air Canada",
"DepartureTime": "11:30",
"ArrivalTime": "13:40",
"Price": "252.84"
},{
"ID": "AF356",
"Name": "Air France",
"DepartureTime": "14:25",
"ArrivalTime": "16:50",
"Price": "445.78"
}...]
```

### Web API to book a trip

```
http://.../api/book_trip.php

[{"flight": "AC301", "date": "2019-12-31"},
{"flight": "AC302", "date": "2019-12-31"}...]  
```

Method: POST  
Param "flights": JSON  
Return: Text (confirmation message)

```
Ok, your trip number is 1598739541. Total price=493.86
```

### Web API to list trips booked

```
http://.../api/list_trips.php
http://.../api/list_trips.php?sort=price
```

Method: GET  
Param "sort": Order by one column (optional)  
Return: JSON

```
[{
"CreationTime": 1596843287,
"FlightID": "AC881",
"Airline": "Air Canada",
"DateDeparture": "2019-12-31",
"DepartureTime": "11:30",
"DepartureAirport": "CDG",
"DepartureAirportName": "Paris-Charles de Gaulle",
"DepartureCity": "Paris",
"ArrivalTime": "13:40",
"ArrivalAirport": "YYZ",
"ArrivalAirportName": "Pearson International",
"ArrivalCity": "Toronto",
"Price": "252.84"
}...]
```