<?php
namespace TripBuilder;
use PDO;

/**
 * Database access.
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
	 * Get all flights corresponding.
	 * @param string $airport_departure
	 * @param string $airport_arrival
	 * @param int $time_departure 7am=700 2:30pm=1430 midnight=0
	 * @return object[] Flights informations
	 */
	public function getDailyFlights($airport_departure, $airport_arrival, $time_departure)
	{
		$sql = 'select * from Flight where DepartureAirport=? and ArrivalAirport=? and DepartureTime >= ?';
		$statement = $this->pdo->prepare($sql);

		if ($statement == false)  // or null
		{
			$this->createTables();
			$statement = $this->pdo->prepare($sql);
		}

		$statement->bindValue(1, $airport_departure, PDO::PARAM_STR);
		$statement->bindValue(2, $airport_arrival, PDO::PARAM_STR);
		$statement->bindValue(3, $time_departure, PDO::PARAM_INT);
		$statement->execute();

		return $statement->fetchAll(PDO::FETCH_OBJ);
	}

	private function createTables()
	{
		$this->pdo->exec('create table Airline ('
			. 'Code char(2) primary key,'
			. 'Name varchar(255) not null)');

		$this->pdo->exec('create table Airport ('
			. 'Code char(3) primary key,'
			. 'CityCode char(3) not null,'
			. 'Name varchar(255) not null,'
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
			. 'DepartureTime smallint not null,'
			. 'ArrivalAirport char(3) not null,'
			. 'ArrivalTime smallint not null,'
			. 'Price varchar(255) not null)');

		$this->pdo->exec('create table Trip ('
			. 'Passenger varchar(255) not null,'
			. 'FlightID varchar not null,'
			. 'Date int not null)');

		// Sample data

		$this->pdo->exec("insert into Airline values ('AC', 'Air Canada')");

		$this->pdo->exec("insert into Airport values ('YUL', 'YMQ', 'Pierre Elliott Trudeau International',"
			. "'Montreal', 'CA', 'QC', '45.457714', '-73.749908', 'America/Montreal')");

		$this->pdo->exec("insert into Airport values ('YVR', 'YVR', 'Vancouver International',"
			. "'Vancouver', 'CA', 'BC', '49.194698', '-123.179192', 'America/Vancouver')");

		$this->pdo->exec("insert into Flight values ('AC301', 'AC', 301, 'YUL', 735, 'YVR', 1005, '273.23')");
		$this->pdo->exec("insert into Flight values ('AC302', 'AC', 302, 'YVR', 1130, 'YUL', 1911, '220.63')");
	}
}