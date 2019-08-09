<?php
/**
 * RESTful web service to list all airports.
 * param "from": Show only connections from this airport (optional)
 * return: JSON
 * @api
 * @example http://82.251.141.181/api/list_airports.php
 * @example http://82.251.141.181/api/list_airports.php?from=YUL
 */
$from = filter_input(INPUT_GET, 'from');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	$json = json_encode($trip->listAirports($from));

	header('Content-type: application/json');
	echo $json;
}
catch (Exception $ex)
{
	header('Content-Type: text/plain', true, 400);
	echo 'Bad Request: ', $ex->getMessage();
}