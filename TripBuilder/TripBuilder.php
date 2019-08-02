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
	 * @param array $flights [Flight ID => Departure date]
	 * @return string Message
	 * @throws Exception
	 * @todo Be mindful of timezones!
	 */
	public function bookTrip($flights)
	{
		if (!is_array($flights) || empty($flights))
		{ throw new Exception('No data received...'); }

		date_default_timezone_set('UTC');
		$creation_time = new DateTime();

		//$first_flight = array_key_first($flights);
		foreach ($flights as $key => $value)
		{
			$first_flight = $key;
			break;
		}

		$departure = new DateTime(
			$flights[$first_flight] . ' '. $this->dao->getFlight($first_flight)['DepartureTime']);

		if ($departure <= $creation_time)
		{ return 'Too late! Your first flight is already gone.';}

		if ($creation_time->add(new DateInterval('P365D')) < $departure)
		{ return 'Impossible to book a trip more than 1 year in advance!';}

		$price = 0;
		$creation_time = $creation_time->getTimestamp();

		foreach ($flights as $flight_id => $departure_date)
		{
			$price += $this->dao->getFlight($first_flight)['Price'];
			$this->dao->addTrip($creation_time, $flight_id, $departure_date);
		}

		return 'Ok, total price = ' . $price;
	}

	static function phpErrorHandler()
	{
		if (error_get_last() != null)
		{
			http_response_code(500);
			echo 'Internal Server Error';
		}
	}
}