<!DOCTYPE html>
<html>
<head>
	<title>Debugging Frontend</title>
	<meta charset="UTF-8">
</head>
<body>
	<b>Web API tests :</b>
	<p>
		<a href="api/list_airports.php">List all airports</a><br/>
	</p>
	<p><form action="api/list_flights.php" method="get">
		List available flights<br/>
		<label for="txtbox1">From:</label><input type="text" id="txtbox1" name="from" value="YUL"/>
		<label for="txtbox2">To:</label><input type="text" id="txtbox2" name="to" value="YVR"/>
		<label for="txtbox3">Time:</label><input type="text" id="txtbox3" name="time" value="0700"/>
		<br/><input type="submit"/>
	</form></p>
	<p><form action="api/book_trip.php" method="post" enctype="multipart/form-data">
		<label for="txtbox0">Book a trip (POST JSON)</label><br/>
		<textarea id="txtbox0" name="flights">{"AC301":"20190831","AC302":"20190901"}</textarea>
		<br/><input type="submit"/>
	</form></p>
</body>
</html>