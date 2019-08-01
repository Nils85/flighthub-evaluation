<?php
namespace TripBuilder;

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
	 * @param string $time_departure 7am="0700" 2:30pm="1430" midnight="0000"
	 * @return object[]
	 */
	public function listFlights($airport_departure, $airport_arrival, $time_departure)
	{
		return $this->dao->getFlights(
			$airport_departure, $airport_arrival, (int)ltrim($time_departure, '0'));
	}

	/**
	 * List all airports.
	 * @return string[]
	 */
	public function listAirports()
	{
		return $this->dao->getAirports();
	}

	/**
	 * Book a new trip for a single passenger.
	 * @param array $flights [Flight ID => Departure date]
	 * @return string Message
	 * @todo A trip MUST depart after creation time at the earliest or 365 days after creation time at the latest
	 * @todo Be mindful of timezones!
	 */
	public function bookTrip($flights)
	{
		date_default_timezone_set('UTC');
		$last_arrival = $time();  // Creation time

		foreach ($flights as $flight_id => $departure_date)
		{
			$flight_infos = $this->dao->getFlight($flight_id);

			//TODO...
			$last_arrival = mktime(
				substr($flight_infos['ArrivalTime'],0),
				substr($flight_infos['ArrivalTime'],0),
				0,
				substr($departure_date,4,2),
				substr($departure_date,-2),
				substr($departure_date,0,4));
		}



		print_r($flights);
	}
}