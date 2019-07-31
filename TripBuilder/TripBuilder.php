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
	 * List all available flights.
	 * @param string $airport_departure Code of daparture airport
	 * @param string $airport_arrival Code of arrival airport
	 * @param string $time_departure 7am="0700" 2:30pm="1430" midnight="0000"
	 * @return string JSON
	 */
	public function listFlights($airport_departure, $airport_arrival, $time_departure)
	{
		return json_encode($this->dao->getDailyFlights(
			$airport_departure, $airport_arrival, (int)ltrim($time_departure, '0')));
	}

	//TODO: public function findRoundTrip() {}
}