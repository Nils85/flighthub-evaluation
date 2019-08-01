<?php
/**
 * RESTful web service to list all airports.
 * param from: Show only connections from this airport (optional)
 * return: JSON
 * @api
 * @example http://localhost:8000/api/list_airports.php
 * @example http://localhost:8000/api/list_airports.php?from=YUL
 */
$from = filter_input(INPUT_GET, 'from');
header('Content-type: application/json');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	echo json_encode($trip->listAirports($from));
}
catch (Exception $ex)
{
	http_response_code(400);
	echo 'Bad Request: ', $ex->getMessage();
}