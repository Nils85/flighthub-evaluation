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
	private $preparedStatement;

	public function __construct($dsn, $username, $password)
	{
		$this->pdo = new PDO($dsn, $username, $password, [
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_EMULATE_PREPARES => false,
			PDO::ATTR_STRINGIFY_FETCHES => false]);

		$sql = 'select * from Flight where ID=?';
		$this->preparedStatement = $this->pdo->prepare($sql);

		if ($this->preparedStatement == false)  // or null
		{
			$this->createTables();
			$this->insertData();
			$this->preparedStatement = $this->pdo->prepare($sql);
		}
	}

	public function getFlight($id)
	{
		$this->preparedStatement->bindValue(1, $id, PDO::PARAM_STR);
		$this->preparedStatement->execute();

		$row = $this->preparedStatement->fetch(PDO::FETCH_ASSOC);
		$this->preparedStatement->closeCursor();
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
		$statement = $this->pdo->prepare(
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
		$statement = $this->pdo->query('select Airport.Code,Airport.Name,Airport.City from Airport');
		return $statement->fetchAll(PDO::FETCH_NUM);
	}

	public function getConnections($from_airport)
	{
		$statement = $this->pdo->prepare('select distinct Airport.Code,Airport.Name,Airport.City from Flight'
			. ' inner join Airport on Flight.ArrivalAirport=Airport.Code where Flight.DepartureAirport=?');

		$statement->bindValue(1, $from_airport, PDO::PARAM_STR);
		$statement->execute();

		return $statement->fetchAll(PDO::FETCH_NUM);
	}

	public function addTrip($creation_time, $flight_id, $date_departure)
	{
		static $prepared_statement = false;

		if (!$prepared_statement)
		{
			$prepared_statement = $this->pdo->prepare(
				'insert into Trip (CreationTime,FlightID,DateDeparture) values (?,?,?)');
		}

		$prepared_statement->bindValue(1, $creation_time, PDO::PARAM_INT);
		$prepared_statement->bindValue(2, $flight_id, PDO::PARAM_STR);
		$prepared_statement->bindValue(3, $date_departure, PDO::PARAM_STR);
		$prepared_statement->execute();
	}

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

		$statement = $this->pdo->query("select Trip.CreationTime, Trip.FlightID,"
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
			. 'Latitude varchar(255) not null,'
			. 'Longitude varchar(255) not null,'
			. 'Timezone varchar(255) not null)');

		$this->pdo->exec('create table Flight ('
			. 'ID varchar primary key,'
			. 'Airline char(2) not null,'
			. 'Number int not null,'
			. 'DepartureAirport char(3) not null,'
			. 'DepartureTime char(5) not null,'
			. 'ArrivalAirport char(3) not null,'
			. 'ArrivalTime char(5) not null,'
			. 'Price varchar(255) not null)');

		$this->pdo->exec('create table Trip ('
			. 'CreationTime int not null,'
			. 'FlightID varchar not null,'
			. 'DateDeparture char(10) not null)');
	}

	/**
	 * Fill tables with sample data.
	 */
	private function insertData()
	{
		$this->pdo->exec("insert into Airline values ('AC','Air Canada')");
		$this->pdo->exec("insert into Airline values ('AF','Air France')");
		$this->pdo->exec("insert into Airline values ('PD','Porter Airlines')");

		$this->pdo->exec("insert into Airport values ('YUL','YMQ','Pierre Elliott Trudeau International',"
			. "'Montreal','CA','QC','45.457714','-73.749908','America/Montreal')");

		$this->pdo->exec("insert into Airport values ('YVR','YVR','Vancouver International',"
			. "'Vancouver','CA','BC','49.194698','-123.179192','America/Vancouver')");

		$this->pdo->exec("insert into Airport values ('YYZ','YTO','Pearson International',"
			. "'Toronto','CA','ON','43.67720','-79.63060','America/Toronto')");

		$this->pdo->exec("insert into Airport values ('YTZ','YTO','Billy Bishop Toronto City Centre',"
			. "'Toronto','CA','ON','43.62750','-79.39620','America/Toronto')");

		$this->pdo->exec("insert into Airport values ('BOS','BOS','Logan International',"
			. "'Boston','US','MA','42.36430','-71.00520','America/New_York')");

		$this->pdo->exec("insert into Airport values ('CDG','PAR','Paris-Charles de Gaulle',"
			. "'Paris','FR','FR','49.01280','2.55000','Europe/Paris')");

		$this->pdo->exec("insert into Flight values ('AC301','AC',301,'YUL','07:35','YVR','10:05','273.23')");
		$this->pdo->exec("insert into Flight values ('AC302','AC',302,'YVR','11:30','YUL','19:11','220.63')");
		$this->pdo->exec("insert into Flight values ('AF351','AF',351,'YYZ','18:45','CDG','08:15','447.78')");
		$this->pdo->exec("insert into Flight values ('AF356','AF',356,'CDG','14:25','YYZ','16:50','445.78')");
		$this->pdo->exec("insert into Flight values ('AC881','AC',881,'CDG','11:30','YYZ','13:40','252.84')");
		$this->pdo->exec("insert into Flight values ('AC7968','AC',7968,'YTZ','15:15','YUL','16:25','152.80')");
		$this->pdo->exec("insert into Flight values ('AC870','AC',870,'YUL','21:00','CDG','08:45','252.80')");
		$this->pdo->exec("insert into Flight values ('PD946','PD',946,'BOS','17:30','YTZ','19:29','314.04')");
		$this->pdo->exec("insert into Flight values ('PD947','PD',947,'YTZ','16:30','BOS','18:05','314.00')");
	}
}