# aixbus
aixbus is a set of php scripts that perform requests to the ASEAG realtime bus service and return comprehensive JSON.

Simply put:

- it takes simple input parameters
- performs the requests to the `ivu.aseag.de` API
- parses the response
- returns a pretty JSON :)

**NOTE**: The `ivu.aseag.de` is undocumented and may be not intended for public use. This code was put together for educational purposes. The rights to use the API should be requested to ASEAG directly.

## Requirements
- aixbus was tested on a server running php `7.0` 
- although  **untested**, it may work with earlier php versions.
- the server needs to be able to perform HTTP requests for `file_get_contents` to work properly.

## Usage

#### closest_stop.php

Returns a list of the closest stops for given coordinates with nested live departure information for each stop.

###### Parameters

- `lat`
- `lon`
- (Optional) `returnCount`: Limits the number of closest stops to the given location
- (Optional) `nestedDeparturesCount`: Limits the number of nested stops to the given location

Tip: `lat` and `lon` use `.` as decimal separator.

###### Return

	[
		{
	        "id": 218500,
	        "name": "Imgenbroich Bushof",
	        "geoLocation": {
	            "lat": 50.579083,
	            "lon": 6.2605
	        },
	        "distance": 67028.993657647,
	        "departures": [
	            {
	                "eta": 1487932320,
	                "local_time": "11:32:00",
	                "line": "82",
	                "destination": "Simmerath",
	                "live_info": false,
	                "tripId": 24000027042001,
	                "indicator": "H3"
	            },
	            ...
	        ]
	    },
	    ...
	]

#### trip_stops.php

Returns all the **remaining stops** for a single given `tripId`. 

This is

- live information of a **single physical vehicle**
- **not** all the stops that a line service covers

###### Parameters

- `tripId`

###### Return

	[
	    {
	        "eta": 1487936520000,
	        "stop": {
	            "id": 218500,
	            "name": "Imgenbroich Bushof",
	            "indicator": "H.4"
	        }
	    },
	    {
	        "eta": 1487936580000,
	        "stop": {
	            "id": 100999,
	            "name": "ASA-Haltestelle \/ FGI-Haltestelle",
	            "indicator": "H.4"
	        }
	    }
	]

## License
aixbus is available under the MIT license. See the LICENSE file for more info.