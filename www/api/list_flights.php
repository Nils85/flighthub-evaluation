<?php
/**
 * RESTful web service to list flights available.
 * param "from": Code departure airport
 * param "to": Code arrival airport
 * return: JSON
 * @api
 * @example http://82.251.141.181/api/list_flights.php?from=YUL&to=YVR
 */
$from = filter_input(INPUT_GET, 'from');
$to = filter_input(INPUT_GET, 'to');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	$json = json_encode($trip->listFlights($from, $to));

	header('Content-type: application/json');
	echo $json;
}
catch (Exception $ex)
{
	header('Content-Type: text/plain', true, 400);
	echo 'Bad Request: ', $ex->getMessage();
}