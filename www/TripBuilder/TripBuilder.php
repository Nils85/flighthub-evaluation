<?php
namespace TripBuilder;
use DateTime;
use DateInterval;
use Exception;

/**
 * Trip builder.
 * @author Vincent Boursier <vincent.boursier@gmail.com>
 */
class TripBuilder
{
	private $dao;

	public function __construct()
	{
		$this->dao = new DataAccess(
			Config::DB_SOURCE, Config::DB_USERNAME, Config::DB_PASSWORD);
	}

	/**
	 * List available daily flights.
	 * @param string $airport_departure Code of daparture airport
	 * @param string $airport_arrival Code of arrival airport
	 * @return object[]
	 */
	public function listFlights($airport_departure, $airport_arrival)
	{
		if ($airport_departure == '' || $airport_arrival == '')
		{ throw new Exception('all parameters are needed'); }

		return $this->dao->getFlights($airport_departure, $airport_arrival);
	}

	/**
	 * List all airports.
	 * @param string $from Airport
	 * @return string[]
	 */
	public function listAirports($from = '')
	{
		$airports = array();

		if (empty($from))
		{ $rows = $this->dao->getAirports(); }
		else
		{ $rows = $this->dao->getConnections($from); }

		foreach ($rows as $row)
		{ $airports[] = $row[0] . ' - ' . $row[1] . ' (' . $row[2] . ')'; }

		return $airports;
	}

	/**
	 * Book a new trip for a single passenger.
	 * A trip MUST depart after creation time at the earliest or 365 days after creation time at the latest
	 * @param object[] $flights [(Flight ID, Departure date)]
	 * @return string Message
	 * @throws Exception
	 * @todo Be mindful of timezones!
	 */
	public function bookTrip($flights)
	{
		if (!is_array($flights))
		{ throw new Exception('No data received...'); }

		date_default_timezone_set('UTC');
		$creation_time = new DateTime();

		$departure = new DateTime(
			$flights[0]->date . ' ' . $this->dao->getFlight($flights[0]->flight)['DepartureTime']);

		if ($departure <= $creation_time)
		{ return 'Too late! Your first flight is already gone.';}

		if ($creation_time->add(new DateInterval('P365D')) < $departure)
		{ return 'Impossible to book a trip more than 1 year in advance!';}

		$price = 0;
		$creation_time = $creation_time->getTimestamp();

		foreach ($flights as $flight)
		{
			$price += $this->dao->getFlight($flight->flight)['Price'];
			$this->dao->addTrip($creation_time, $flight->flight, $flight->date);
		}

		return 'Ok, your trip number is ' . $creation_time . '. Total price=' . $price;
	}

	public function listTrips($sortColumn ='')
	{
		return $this->dao->getTrips($sortColumn);
	}

	static function phpErrorHandler()
	{
		if (error_get_last() != null)
		{
			header('Content-Type: text/plain', true, 500);
			echo 'Internal Server Error';
		}
	}
}