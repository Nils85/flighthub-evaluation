<?php
/**
 * RESTful web service for a one-way trip or a round-trip.
 * method: POST JSON
 * param flights: [{"Date":"...",...}{...}]
 * return: Text (confirmation message)
 * @api
 * @todo A trip MUST depart after creation time at the earliest or 365 days after creation time at the latest
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
	$trip->bookTrips(json_decode($flights));
	echo 'OK';
}
catch (Exception $ex)
{
	http_response_code(500);
	echo 'Internal Server Error: ', $ex->getMessage();
}