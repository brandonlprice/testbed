<?php
/*****************************************************************************
 * START - Change values
 *****************************************************************************/
// the keys (provided by Pictometry)
$apikey = '772F27871CA9BFBE867F3AF632248C45';
$secretkey ='C427228DA725194D5F497BEC989EFF703C8BC4FCB7245E9CFF5B83A95A4487DA3207C6177B9C73E3E540281D7D4E0E6BDDA162D1C36CBFC68389ED6951166E6FCEE1EBA9F086EF9A64EF92EC88368FC8CB7B443ABEFD045D6B68400D74D8D26F5B590BF7F1A9322B001219B23755B49300AA0E94A852AB93FA01AB5FCB682D0F';

// the IPA Load URL (provided by Pictometry)
$ipaLoadUrl = 'http://pol.pictometry.com/ipa/v1/load.php';

// the IPA Javascript Library URL (provided by Pictometry)
$ipaJsLibUrl = 'http://pol.pictometry.com/ipa/v1/embed/host.php?apikey=772F27871CA9BFBE867F3AF632248C45';

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

$ain = '5342005904';
if(isset($_GET['AIN']))
	$ain = $_GET['AIN'];
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
			border: none; 
			width: 100%; 
			height: 1.75em; 
			text-align: center;
		}
		
		#pictometry {
			margin-left: auto; 
			margin-right: auto; 
			padding: 5px; 
			width: 100%; 
			height: 950px; 
			border-style: hidden;
		}
	</style>    	
</head>
<body>
	<div id="content">
		<!--<a href='#' onclick="gotoEastmanHouse()">PW HQ</a> |-->
		Go to Address: 
		<input type="text" id="addressText" disabled="disabled" 
			onKeyPress="if (event.keyCode == 13) gotoAddress();"></input>
		<button type="button" onclick="gotoAddress();">Go</button>
		<a href='#' onclick="getLocation()">Get Current Location</a> | 
		Set Location: 
		<input type="text" id="locationText" disabled="disabled" 
			onKeyPress="if (event.keyCode == 13) setLocation();" placeholder="Latitude, Longitude"></input>
		<button type="button" onclick="setLocation();">Go</button>
		Search AIN, APN: 
		<input value="<?php echo $ain; ?>" type="text" id="ainorapnvalue" disabled="disabled" 
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

			ipa.activateDualPane();
			
			ipa.setLocation({      
	            y:34.081817,       // Latitude
	            x:-118.150191,      // Longitude
	            zoom:10            // Optional Zoom level
	        });
			
			ipa.setPreferences({
				showMapLabel: false
			});

			if(!!ain) gotoAIN(); // Go to AIN if there is an ain number;

			};
		
		function gotoAIN() {
			var searchValue = document.getElementById("ainorapnvalue").value;
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