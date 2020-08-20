<?php
/*****************************************************************************
 * START - Change values
 *****************************************************************************/
// the keys (provided by Pictometry)
$apikey = '2DFAA2D1FA259E4FC437EC3F728F72D4';
$secretkey = '6F29EFF84894D94454BC967100080A5714FAE57BCE3748AC55112CDB113535FA647FD7156D7355CBEB7019D7D59E636900DDA76554404D0F67F3B5510A64A0D20584DE51795EBB59F6E57BA3E2FAE5635BD31F477A6777A41155AA69A8B894C1EB40C5A6B81591230CE3E2D4840A8B44937BACC9EDC7D27CA80EC6FADEF958AA';

// the IPA Load URL (provided by Pictometry)
$ipaLoadUrl = 'http://pol.pictometry.com/ipa/v1/load.php';

// the IPA Javascript Library URL (provided by Pictometry)
$ipaJsLibUrl = 'http://pol.pictometry.com/ipa/v1/embed/host.php?apikey=2DFAA2D1FA259E4FC437EC3F728F72D4';

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
		Search AIN, APN: 
		<input value="<?php echo $ain; ?>" type="text" id="addressText" disabled="disabled" 
			onKeyPress="if (event.keyCode == 13) gotoAddress();"></input>
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
			var searchValue = document.getElementById("addressText").value;
			var dashIncluded = searchValue.includes("-");
			console.log(searchValue);
			
			ipa.getSearchServices(function(searchableLayers) {
         		mySearchLayers = searchableLayers;
				//console.log(mySearchLayers);
				
				for(j=0; j<mySearchLayers.textSearchServices.length; j++){ 
				//console.log(mySearchLayers.textSearchServices);
					if(mySearchLayers.textSearchServices[j].description == "LARIAC - Parcels (streamed)"){ 
						var layerId = mySearchLayers.textSearchServices[j].id; 
						//console.log(layerId);
						if (dashIncluded == true)
						{
							
							for (i=0; i<mySearchLayers.textSearchServices[j].fields.length; i++){
								if (mySearchLayers.textSearchServices[j].fields[i].name == "APN"){ 
									var fieldsKey = mySearchLayers.textSearchServices[j].fields[i].key; 
									//console.log(layerId);
									console.log([fieldsKey]);
								
									var query = {
										searchString: searchValue,
										id: layerId,
										fields: [fieldsKey]
									};
								
									ipa.searchByString(query);
								}
							}
						}
						else 
						{
							
							for (i=0; i<mySearchLayers.textSearchServices[j].fields.length; i++){
								if (mySearchLayers.textSearchServices[j].fields[i].name == "AIN"){ 
									var fieldsKey = mySearchLayers.textSearchServices[j].fields[i].key; 
									//console.log(layerId);
									console.log([fieldsKey]);
								
									var query = {
										searchString: searchValue,
										id: layerId,
										fields: [fieldsKey]
									};
								
									ipa.searchByString(query);
								}							
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