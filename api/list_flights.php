<?php
/**
 * RESTful web service to list flights available.
 * param from: Code departure airport
 * param to: Code arrival airport
 * return: JSON
 * @api
 * @example http://localhost:8000/api/list_flights.php?from=YUL&to=YVR
 */
$from = filter_input(INPUT_GET, 'from');
$to = filter_input(INPUT_GET, 'to');
header('Content-type: application/json');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	echo json_encode($trip->listFlights($from, $to));
}
catch (Exception $ex)
{
	http_response_code(400);
	echo 'Bad Request: ', $ex->getMessage();
}