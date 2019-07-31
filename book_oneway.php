<?php
/**
 * RESTful web service for a one-way trip.
 * @api
 * @example http://.../book_oneway.php?from=YUL&to=YVR&time=0700
 * @param string $from Code departure airport
 * @param string $to Code arrival airport
 * @param string $time Departure time in 24 hours format
 * @return string JSON
 * @todo A trip MUST depart after creation time at the earliest or 365 days after creation time at the latest
 */
$from = filter_input(INPUT_GET, 'from');
$to = filter_input(INPUT_GET, 'to');
$time = filter_input(INPUT_GET, 'time');
header('Content-Type: text/plain');

function __autoload($class_name)
{ require str_replace('\\', '/', $class_name) . '.php'; }

if ($from == '' || $to == '' || $time == '')
{
	http_response_code(400);
	echo 'Bad Request! Expected URL -> http://',
		filter_input(INPUT_SERVER, 'HTTP_HOST'), filter_input(INPUT_SERVER, 'SCRIPT_NAME'),
		'?from=YUL&to=YVR&time=0700';

	exit;
}

try
{
	$trip = new TripBuilder();
	$trip->findOneWay($from, $to, $time);
}
catch (Exception $ex)
{
	http_response_code(500);
	echo 'Internal Server Error: ', $ex->getMessage();
}

echo 'XML or JSON for ', $from, ' ', $to, ' ', $time;