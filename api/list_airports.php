<?php
/**
 * RESTful web service to list all airports.
 * return: JSON
 * @api
 */
header('Content-type: application/json');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

try
{
	$trip = new TripBuilder\TripBuilder();
	echo json_encode($trip->listAirports());
}
catch (Exception $ex)
{
	http_response_code(500);
	echo 'Internal Server Error: ', $ex->getMessage();
}