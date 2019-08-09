<?php
/**
 * RESTful web service to list all trips booked.
 * param "sort": Order by one column (optional)
 * return: JSON
 * @api
 * @example http://82.251.141.181/api/list_trips.php
 * @example http://82.251.141.181/api/list_trips.php?sort=ColName
 */
$sort = filter_input(INPUT_GET, 'sort');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	$json = json_encode($trip->listTrips($sort));

	header('Content-type: application/json');
	echo $json;
}
catch (Exception $ex)
{
	header('Content-Type: text/plain', true, 400);
	echo 'Bad Request: ', $ex->getMessage();
}