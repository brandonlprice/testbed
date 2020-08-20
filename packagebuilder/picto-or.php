<?php
/*****************************************************************************
 * START - Change values
 *****************************************************************************/
// the keys (provided by Pictometry)
$apikey = '0BEF41C7583B50E7B529DB86914506FC';
$secretkey = 'F3FB7B9167B4614F6D75D525D49CCBBF9F72904964B5041092F4A98E3905AE0EA8E85776C53018E5DC91CF70AB1990C886E0FE48735D9EC39863FDE0A0CAEA3A32CA0B4D736584D6D4325733CB89179AE7C1098E0782A9BDDFFA223652C5E96C2FA0B384AB7F12A5ED6A968BA3F07F378473994FED3C43AFA826C8763CA3F136';

// the IPA Load URL (provided by Pictometry)
$ipaLoadUrl = 'http://pol.pictometry.com/ipa/v1/load.php';

// the IPA Javascript Library URL (provided by Pictometry)
$ipaJsLibUrl = 'http://pol.pictometry.com/ipa/v1/embed/host.php?apikey=0BEF41C7583B50E7B529DB86914506FC';

// iframe id (needed to ensure communication with correct iframe)
// must be a unique element id in your application
$iframeId = 'pictometry_ipa';

/*****************************************************************************
 * END - Change values
 *****************************************************************************/
 
// create the URL to be signed
$unsignedUrl = $ipaLoadUrl."?apikey=$apikey&ts=".time();

// create the digital signature using the unsigned Load URL and the secret key
$digitalSignature = hash_hmac('md5', $unsignedUrl, $secretkey);

// create the signed Load URL using the generated digital signature
$signedUrl = $unsignedUrl."&ds=".$digitalSignature."&app_id=".$iframeId;

$ain = $_GET['AIN']; // getting the Ain from the request url
?>

<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>IPA Example - PHP - Advanced</title>

	<!-- include the IPA JS Library from pictometry -->
	<script type="text/javascript" src="<?php echo $ipaJsLibUrl; ?>"></script>

	<style>
		#content {
			margin-left: auto; 
			margin-right: auto; 
			border-style: solid; 
			width: 95%; 
			height: 1.5em; 
			text-align: center;
		}
		
		#pictometry {
			margin-left: auto; 
			margin-right: auto; 
			padding: 5px; 
			width: 95%; 
			height: 950px; 
			border-style: hidden;
		}
	</style>    	
</head>
<body>
	<div id="content">
		<a href='#' onclick="gotoEastmanHouse()">PW HQ</a> |
		<a href='#' onclick="getLocation()">Get Current Location</a> | 
		Set Location: 
		<input type="text" id="locationText" disabled="disabled" 
			onKeyPress="if (event.keyCode == 13) setLocation();" placeholder="Latitude, Longitude"></input>
		<button type="button" onclick="setLocation();">Go</button>
		Goto Address: 
		<input value="<?php echo $ain; ?>" type="text" id="addressText" disabled="disabled" 
			onKeyPress="if (event.keyCode == 13) gotoAddress();"></input>
		<button type="button" onclick="gotoAddress();">Go</button>
		<button type="button" onclick="gotoAIN();">Search</button>
	</div>
	<div id="pictometry">
		<iframe id="<?php echo $iframeId; ?>" width="100%" height="100%" src="#"></iframe>
	</div>
	
	<script type="text/javascript">
		var ipa = new PictometryHost('<?php echo $iframeId; ?>','<?php echo $ipaLoadUrl; ?>');
		const ain = "<?php echo $ain; ?>"; // capturing the AIN value

		ipa.ready = function() {
			document.getElementById('locationText').disabled = false;
			document.getElementById('addressText').disabled = false;
			ipa.addListener('location', function( location ) {
				alert( location.y + " , " + location.x );
			} );
			ipa.getLayers( function(result) {

         // Do something with the list of layers
         // We can save them to reference later when we need to show or hide them.
			myLayers = result.layers;

			});
			ipa.getSearchServices( function(searchableLayers) {

			// Do something with the list of searches
			// We can them save to reference later when we need to search.
			mySearchLayers = searchableLayers;

			});

			if(!!ain) gotoAIN(); // Go to AIN if there is an ain number;

		};
		
		function gotoAIN() {
			ipa.getSearchServices(function(searchableLayers) {
         		mySearchLayers = searchableLayers;
				console.log(mySearchLayers);
				
				for(j=0; j<mySearchLayers.textSearchServices.length; j++){ 
				console.log(mySearchLayers.textSearchServices);
					if(mySearchLayers.textSearchServices[j].description == "LARIAC - Parcels (streamed)"){ 
						var layerId = mySearchLayers.textSearchServices[j].id; 
						console.log(layerId);
						for (i=0; i<mySearchLayers.textSearchServices[j].fields.length; i++){
							if (mySearchLayers.textSearchServices[j].fields[i].name == "AIN"){ 
								var fieldsKey = mySearchLayers.textSearchServices[j].fields[i].key; 
								console.log(layerId);
								console.log([fieldsKey]);
								
								var query = {
									searchString: "8401006017",
									id: layerId,
									fields: [fieldsKey]
								};
								
								ipa.searchByString(query);
							}
						}
					}
				} 
		 	});
			return false;
		}
		//function searchByString() {
			//var query = {
				//searchString: "8401006017",    // A street in the street layer.
				//id: 57106,              // The street layer id.  A list of ids can be retrieved using getSearchServices.
				//fields: ["AIN"]     // Specifies the field name to search.
			//}
			//ipa.searchByString(query);
		//}

		function gotoEastmanHouse() {

			// Set the view location
	    	ipa.setLocation({      
	            y:34.085267,       // Latitude
	            x:-118.150130,      // Longitude
	            zoom:20            // Optional Zoom level


	        });
		
			return false;
		}
		
		function setLocation() {
			var location = document.getElementById('locationText');
			var loc = location.value.split(',');
			var lat = loc[0].replace(/^\s+|\s+$/g, "");
			var lon = loc[1].replace(/^\s+|\s+$/g, "");
			
			// Alternate syntax to pass parameters			
			ipa.setLocation(lat, lon, 17);
			
			return false;
		}
		
		function gotoAddress() {
			var address = document.getElementById('addressText');
			ipa.gotoAddress(address.value);
			return false;
		}

		function getLocation() {
			ipa.getLocation();
		}
		
		// set the iframe src to load the IPA
		var iframe = document.getElementById('<?php echo $iframeId; ?>');
		iframe.setAttribute('src', '<?php echo $signedUrl; ?>');
		
		
	</script>	
</body>
</html>