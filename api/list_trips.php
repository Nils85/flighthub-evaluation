<?php
/**
 * RESTful web service to list all trips booked.
 * param sort: Order by one column (optional)
 * return: JSON
 * @api
 * @example http://localhost:8000/api/list_trips.php
 * @example http://localhost:8000/api/list_trips.php?sort=ColName
 */
$sort = filter_input(INPUT_GET, 'sort');
header('Content-type: application/json');

spl_autoload_register(function($class_name) {
	require '../' . str_replace('\\', '/', $class_name) . '.php';
});

register_shutdown_function('TripBuilder\\TripBuilder::phpErrorHandler');

try
{
	$trip = new TripBuilder\TripBuilder();
	echo json_encode($trip->listTrips($sort));
}
catch (Exception $ex)
{
	http_response_code(400);
	echo 'Bad Request: ', $ex->getMessage();
}