<?php
namespace TripBuilder;
use PDO;
use DateTime;
use Exception;

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

		$sql = 'select * from Flight where DepartureAirport=? and ArrivalAirport=? and DepartureTime >= ?';
		$this->preparedStatement = $this->pdo->prepare($sql);

		if ($this->preparedStatement == false)  // or null
		{
			$this->createTables();
			$this->insertData();
			$this->preparedStatement = $this->pdo->prepare($sql);
		}
	}

	/**
	 * Get flights corresponding with criteria.
	 * @param string $airport_departure
	 * @param string $airport_arrival
	 * @param int $time_departure 7am=700 2:30pm=1430 midnight=0
	 * @return object[] Flights informations
	 */
	public function getFlights($airport_departure, $airport_arrival, $time_departure)
	{
		$this->preparedStatement->bindValue(1, $airport_departure, PDO::PARAM_STR);
		$this->preparedStatement->bindValue(2, $airport_arrival, PDO::PARAM_STR);
		$this->preparedStatement->bindValue(3, $time_departure, PDO::PARAM_INT);
		$this->preparedStatement->execute();

		return $this->preparedStatement->fetchAll(PDO::FETCH_OBJ);
	}

	public function getFlight($id)
	{
		static $prepared_statement = false;

		if (!$prepared_statement)
		{ $prepared_statement = $this->pdo->prepare('select * from Flight where ID=?'); }

		$prepared_statement->bindValue(1, $id, PDO::PARAM_STR);
		$prepared_statement->execute();

		$row = $prepared_statement->fetch(PDO::FETCH_ASSOC);
		$prepared_statement->closeCursor();
		return $row;
	}

	/**
	 * Get all airports in the database.
	 * @return string[] "Code - Airport name (City)"
	 */
	public function getAirports()
	{
		$airports = array();
		$statement = $this->pdo->query('select Code,Name,City from Airport');

		foreach ($statement as $row)
		{ $airports[] = $row['Code'] . ' - ' . $row['Name'] . ' (' . $row['City'] . ')'; }

		return $airports;
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
		$prepared_statement->bindValue(3, $date_departure, PDO::PARAM_INT);
		$prepared_statement->execute();
	}

	static function intToDateTime($date, $time)
	{
		if (strlen($date) != 8)
		{ throw new Exception("Invalid DateTime: $date $time"); }

		$time = str_pad($time, 4, '0', STR_PAD_LEFT);

		return new DateTime(
			substr($date,0,4) . '-' . substr($date,4,2) . '-' . substr($date,-2)
			. ' ' . substr($time,0,2) . ':' . substr($time,-2) . ':00');
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
			. 'DepartureTime smallint not null,'
			. 'ArrivalAirport char(3) not null,'
			. 'ArrivalTime smallint not null,'
			. 'Price varchar(255) not null)');

		$this->pdo->exec('create table Trip ('
			. 'CreationTime int not null,'
			. 'FlightID varchar not null,'
			. 'DateDeparture int not null)');
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

		$this->pdo->exec("insert into Airport values ('ORY','PAR','Paris-Orly',"
			. "'Paris','FR','FR','48.72333','2.37944','Europe/Paris')");

		$this->pdo->exec("insert into Flight values ('AC301','AC',301,'YUL',735,'YVR',1005,'273.23')");
		$this->pdo->exec("insert into Flight values ('AC302','AC',302,'YVR',1130,'YUL',1911,'220.63')");
		$this->pdo->exec("insert into Flight values ('AF351','AF',351,'YYZ',1845,'CDG',815,'446.78')");
		$this->pdo->exec("insert into Flight values ('AF356','AF',356,'CDG',1425,'YYZ',1650,'446.78')");
		$this->pdo->exec("insert into Flight values ('AC881','AC',881,'CDG',1130,'YYZ',1340,'252.84')");
		$this->pdo->exec("insert into Flight values ('AC7968','AC',7968,'YTZ',1515,'YUL',1625,'152.80')");
		$this->pdo->exec("insert into Flight values ('AC870','AC',870,'YUL',2100,'CDG',845,'252.80')");
		$this->pdo->exec("insert into Flight values ('PD946','PD',946,'BOS',1730,'YTZ',1929,'314.02')");
		$this->pdo->exec("insert into Flight values ('PD947','PD',947,'YTZ',1630,'BOS',1805,'314.02')");
	}
}