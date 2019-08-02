<?php
/**
 * RESTful web service for a one-way trip or a round-trip.
 * method: POST JSON
 * param flights: [{"flight":"AC301","date":"2019-12-31"},{"flight":"AC302","date":"2019-12-31"}...]
 * return: Text (confirmation message)
 * @api
 */
$flights = filter_input(INPUT_POST, 'flights');
header('Content-Type: text/plain');

function __autoload($class_name)
{ require '../' . str_replace('\\', '/', $class_name) . '.php'; }

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	echo $trip->bookTrip(json_decode($flights));
}
catch (Exception $ex)
{
	http_response_code(400);
	echo 'Bad Request: ', $ex->getMessage();
}