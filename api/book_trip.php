<?php
/**
 * RESTful web service for a one-way trip or a round-trip.
 * method: POST JSON
 * param flights: { "AC301":"20190731", "AC302":"20190801", ... }
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
	echo $trip->bookTrip(json_decode($flights, true));
}
catch (Exception $ex)
{
	http_response_code(400);
	echo 'Bad Request: ', $ex->getMessage();
}