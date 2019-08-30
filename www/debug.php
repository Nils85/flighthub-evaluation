<!DOCTYPE html>
<html>
<head>
	<title>Trip Builder</title>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
	<p class="z">
		<b>Trip Builder</b>
		<a href="index.html">Book a new trip</a>
		<a href="trips.html">Trip listing</a>
		<a href="debug.php?XDEBUG_SESSION_START=netbeans-xdebug" style="text-decoration:underline"><i>Debug API</i></a>
	</p>
	<p>
		<b>api/list_airports.php</b><br/>
		<a href="api/list_airports.php">List all airports</a><br/>
	</p>		
	<form action="api/list_airports.php" method="get">
		<b>api/list_airports.php?from=...</b><br/>
		List connections between airports<br/>
		<label for="txtbox4">From:</label><input type="text" id="txtbox4" name="from" value="YUL"/>
		<input type="submit"/>
	</form>
	<form action="api/list_flights.php" method="get">
		<b>api/list_flights.php?from=...&to=...</b><br/>
		List available flights<br/>
		<label for="txtbox1">From:</label><input type="text" id="txtbox1" name="from" value="YUL"/>
		<label for="txtbox2">To:</label><input type="text" id="txtbox2" name="to" value="YVR"/>
		<br/><input type="submit"/>
	</form>
	<form action="api/book_trip.php" method="post" enctype="multipart/form-data">
		<b>api/book_trip.php</b><br/>
		<label for="txtbox0">Book a trip (POST JSON)</label><br/>
		<textarea id="txtbox0" name="flights">[{"flight":"AC301","date":"2019-09-29"},{"flight":"AC302","date":"2019-09-30"}]</textarea>
		<br/><input type="submit"/>
	</form>
	<p>
		<b>api/list_trips.php</b><br/>
		<a href="api/list_trips.php">List all trips</a><br/>
	</p>
</body>
</html>