'use strict';

fillListAirports('a1');

function fillListAirports(id, from)
{
	var select = document.getElementById(id);
	var xhr = new XMLHttpRequest();
	var htm = '';

	if (typeof from === 'undefined')
	{
		htm = '<option value="">Select an airport...</option>';
		xhr.open('GET', '/api/list_airports.php', true);
	}
	else
	{
		xhr.open('GET', '/api/list_airports.php?from=' + from, true);
	}

	xhr.onreadystatechange = function () {
		if (xhr.readyState === 4 && xhr.status === 200)
		{
			var list = JSON.parse(this.responseText);

			for (var item of list)
			{ htm += '<option value="' + item.substr(0,3) + '">' + item + '</option>'; }

			select.innerHTML = htm;
		}
	};

	xhr.send(null);
}