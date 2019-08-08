<?php
namespace TripBuilder;
use PDO;

/**
 * Database access (compatible with SQLite, MySQL, PostgreSQL, MS SQL Server, Oracle...)
 * @author Vincent Boursier <vincent.boursier@gmail.com>
 */
class DataAccess
{
	private $pdo;

	public function __construct($dsn, $username, $password)
	{
		$this->pdo = new PDO($dsn, $username, $password, [
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_STRINGIFY_FETCHES => false]);
	}

	/**
	 * Find a specific flight by its ID.
	 * @param string $id
	 * @return array
	 */
	public function getFlight($id)
	{
		static $prepared_statement = false;

		if (!$prepared_statement)
		{ $prepared_statement = $this->prepare('select * from Flight where ID=?'); }

		$prepared_statement->bindValue(1, $id, PDO::PARAM_STR);
		$prepared_statement->execute();

		$row = $prepared_statement->fetch(PDO::FETCH_ASSOC);
		$prepared_statement->closeCursor();
		return $row;
	}

	/**
	 * Get flights corresponding with criteria.
	 * @param string $airport_departure
	 * @param string $airport_arrival
	 * @return object[] Flights informations
	 */
	public function getFlights($airport_departure, $airport_arrival)
	{
		$statement = $this->prepare(
			'select Flight.ID, Airline.Name, Flight.DepartureTime, Flight.ArrivalTime, Flight.Price'
			. ' from Flight inner join Airline on Flight.Airline=Airline.Code'
			. ' where DepartureAirport=? and ArrivalAirport=? order by DepartureTime');

		$statement->bindValue(1, $airport_departure, PDO::PARAM_STR);
		$statement->bindValue(2, $airport_arrival, PDO::PARAM_STR);
		$statement->execute();

		return $statement->fetchAll(PDO::FETCH_OBJ);
	}

	/**
	 * Get all airports in the database.
	 * @return array Rows with columns 0=>Code 1=>Name 2=>City
	 */
	public function getAirports()
	{
		$statement = $this->query('select Airport.Code,Airport.Name,Airport.City from Airport');
		return $statement->fetchAll(PDO::FETCH_NUM);
	}

	/**
	 * Find all destinations from an airport.
	 * @param string $from_airport
	 * @return array
	 */
	public function getConnections($from_airport)
	{
		$statement = $this->prepare('select distinct Airport.Code,Airport.Name,Airport.City from Flight'
			. ' inner join Airport on Flight.ArrivalAirport=Airport.Code where Flight.DepartureAirport=?');

		$statement->bindValue(1, $from_airport, PDO::PARAM_STR);
		$statement->execute();

		return $statement->fetchAll(PDO::FETCH_NUM);
	}

	/**
	 * Save a new trip in the database.
	 * @param int $creation_time Unix timestamp
	 * @param string $flight_id
	 * @param string $date_departure Date format: "2019-21-31"
	 */
	public function addTrip($creation_time, $flight_id, $date_departure)
	{
		static $prepared_statement = false;

		if (!$prepared_statement)
		{
			$prepared_statement = $this->prepare(
				'insert into Trip (CreationTime,FlightID,DateDeparture) values (?,?,?)');
		}

		$prepared_statement->bindValue(1, $creation_time, PDO::PARAM_INT);
		$prepared_statement->bindValue(2, $flight_id, PDO::PARAM_STR);
		$prepared_statement->bindValue(3, $date_departure, PDO::PARAM_STR);
		$prepared_statement->execute();
	}

	/**
	 * List all trip saved.
	 * @param string $order Sort by a column
	 * @return object[]
	 */
	public function getTrips($order)
	{
		$order_by = 'Trip.CreationTime, Trip.DateDeparture, Flight.DepartureTime';  // Default

		switch ($order)
		{
			case 'flight': $order_by = 'Trip.FlightID'; break;
			case 'airline': $order_by = 'Airline.Name, Trip.FlightID'; break;
			case 'departure_date': $order_by = 'Trip.DateDeparture, Flight.DepartureTime'; break;
			case 'departure_time': $order_by = 'Flight.DepartureTime'; break;
			case 'departure_airport': $order_by = 'Flight.DepartureAirport'; break;
			case 'departure_city': $order_by = 'Airport1.City'; break;
			case 'arrival_time': $order_by = 'Flight.ArrivalTime'; break;
			case 'arrival_airport': $order_by = 'Flight.ArrivalAirport'; break;
			case 'arrival_city': $order_by = 'Airport2.City'; break;
			case 'price': $order_by = 'Flight.Price'; break;
		}

		$statement = $this->query("select Trip.CreationTime, Trip.FlightID,"
			. " Airline.Name as 'Airline', Trip.DateDeparture, Flight.DepartureTime, Flight.DepartureAirport,"
			. " Airport1.Name as 'DepartureAirportName', Airport1.City as 'DepartureCity', Flight.ArrivalTime,"
			. " Flight.ArrivalAirport, Airport2.Name as 'ArrivalAirportName', Airport2.City as 'ArrivalCity',"
			. " Flight.Price from Trip"
			. " inner join Flight on Trip.FlightID = Flight.ID"
			. " inner join Airline on Flight.Airline = Airline.Code"
			. " inner join Airport as Airport1 on Flight.DepartureAirport = Airport1.Code"
			. " inner join Airport as Airport2 on Flight.ArrivalAirport = Airport2.Code"
			. " order by $order_by");

		return $statement->fetchAll(PDO::FETCH_OBJ);
	}

	/**
	 * Prepares a statement and check if the database exists.
	 * @param string $sql SQL query
	 * @return PDOStatement
	 */
	private function prepare($sql)
	{
		$prepared_statement = $this->pdo->prepare($sql);

		if ($prepared_statement == false)  // or null
		{
			$this->createTables();
			$this->insertData();
			$prepared_statement = $this->pdo->prepare($sql);
		}

		return $prepared_statement;
	}

	/**
	 * Executes an SQL statement and check if the database exists.
	 * @param string $sql SQL query
	 * @return PDOStatement
	 */
	private function query($sql)
	{
		$statement = $this->pdo->query($sql);

		if ($statement == false)  // or null
		{
			$this->createTables();
			$this->insertData();
			$statement = $this->pdo->query($sql);
		}

		return $statement;
	}

	/**
	 * Initialize tables for the first time.
	 */
	private function createTables()
	{
		$this->pdo->exec('create table Airline ('
			. 'Code char(2) primary key,'
			. 'Name varchar(255) not null)');

		$this->pdo->exec('create table Airport ('
			. 'Code char(3) primary key,'
			. 'CityCode char(3) not null,'
			. 'Name varchar(255) not null,'
			. 'City varchar(255) not null,'
			. 'CountryCode char(2) not null,'
			. 'RegionCode char(2) not null,'
			. 'Latitude decimal(9,6) not null,'
			. 'Longitude decimal(9,6) not null,'
			. 'Timezone varchar(255) not null)');

		$this->pdo->exec('create table Flight ('
			. 'ID varchar(7) primary key,'
			. 'Airline char(2) not null,'
			. 'Number smallint not null,'
			. 'DepartureAirport char(3) not null,'
			. 'DepartureTime char(5) not null,'
			. 'ArrivalAirport char(3) not null,'
			. 'ArrivalTime char(5) not null,'
			. 'Price decimal(6,2) not null)');

		$this->pdo->exec('create table Trip ('
			. 'CreationTime int not null,'
			. 'FlightID varchar(255) not null,'
			. 'DateDeparture char(10) not null)');
	}

	/**
	 * Fill tables with sample data.
	 */
	private function insertData()
	{
		$this->pdo->beginTransaction();

		$this->pdo->exec("insert into Airline values ('AC','Air Canada')");
		$this->pdo->exec("insert into Airline values ('AF','Air France')");
		$this->pdo->exec("insert into Airline values ('PD','Porter Airlines')");

		$this->pdo->exec("insert into Airport values ('YUL','YMQ','Pierre Elliott Trudeau International',"
			. "'Montreal','CA','QC',45.457714,-73.749908,'America/Montreal')");

		$this->pdo->exec("insert into Airport values ('YVR','YVR','Vancouver International',"
			. "'Vancouver','CA','BC',49.194698,-123.179192,'America/Vancouver')");

		$this->pdo->exec("insert into Airport values ('YYZ','YTO','Pearson International',"
			. "'Toronto','CA','ON',43.67720,-79.63060,'America/Toronto')");

		$this->pdo->exec("insert into Airport values ('YTZ','YTO','Billy Bishop Toronto City Centre',"
			. "'Toronto','CA','ON',43.62750,-79.39620,'America/Toronto')");

		$this->pdo->exec("insert into Airport values ('BOS','BOS','Logan International',"
			. "'Boston','US','MA',42.36430,-71.00520,'America/New_York')");

		$this->pdo->exec("insert into Airport values ('CDG','PAR','Paris-Charles de Gaulle',"
			. "'Paris','FR','FR',49.01280,2.55000,'Europe/Paris')");

		$this->pdo->exec("insert into Flight values ('AC301','AC',301,'YUL','07:35','YVR','10:05',273.23)");
		$this->pdo->exec("insert into Flight values ('AC302','AC',302,'YVR','11:30','YUL','19:11',220.63)");
		$this->pdo->exec("insert into Flight values ('AF351','AF',351,'YYZ','18:45','CDG','08:15',447.78)");
		$this->pdo->exec("insert into Flight values ('AF356','AF',356,'CDG','14:25','YYZ','16:50',445.78)");
		$this->pdo->exec("insert into Flight values ('AC881','AC',881,'CDG','11:30','YYZ','13:40',252.84)");
		$this->pdo->exec("insert into Flight values ('AC7968','AC',7968,'YTZ','15:15','YUL','16:25',152.80)");
		$this->pdo->exec("insert into Flight values ('AC870','AC',870,'YUL','21:00','CDG','08:45',252.80)");
		$this->pdo->exec("insert into Flight values ('PD946','PD',946,'BOS','17:30','YTZ','19:29',314.04)");
		$this->pdo->exec("insert into Flight values ('PD947','PD',947,'YTZ','16:30','BOS','18:05',314.00)");

		$this->pdo->commit();
	}
}