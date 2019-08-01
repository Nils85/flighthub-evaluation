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

if ($flights == false)  // or null
{
	http_response_code(400);
	echo 'Bad Request';
	exit;
}

try
{
	$trip = new TripBuilder\TripBuilder();
	$trip->bookTrip(json_decode($flights, true));
	echo 'OK';
}
catch (Exception $ex)
{
	http_response_code(500);
	echo 'Internal Server Error: ', $ex->getMessage();
}