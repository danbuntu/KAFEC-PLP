<?php
class block_nwkc_geolocation extends block_base {

	function init() {
		$this->title   = 'Moodle Check-in';
		$this->version = 2011032215;
	}
	
	function instance_allow_config() {
	  return true;
	}
	
	function has_config() {
  	  return true;
	}
  
	function get_content() {
		
        global $CFG;
		
		$this->content = new stdClass;
			
		$this->content->text.= '
			
		<script type="text/javascript">
		
			updatepagepath = \''.$CFG->wwwroot.'\' + "/blocks/nwkc_geolocation/updatelocation.php";	
			userip = \''.$_SERVER['REMOTE_ADDR'].'\';
			var examplepos =  { "coords" : { "latitude" : 0.0, "longitude" : 0.0} } ;
						
			$(function() {
			
				//$("#gpsText").html("Loaded");
				
				if (navigator.geolocation && navigator.userAgent.indexOf("Firefox")==-1 && navigator.userAgent.indexOf("MSIE")==-1 ) {
					getLocation();
				} else if (userip == "10.32.0.150") {
					showGPS(examplepos);
			    } else {
					$("#gpsText").html("No GPS Functionality.");
				}
			
			});
			
			function getLocation() {
				navigator.geolocation.getCurrentPosition(showGPS, gpsError);
			}
			
			function gpsError(error) {
				alert("GPS Error: "+error.code+", "+error.message);
			}
			
			function updateLocation(lat, lon, location) {
				$.get(updatepagepath+"?lat="+lat+"&lon="+lon+"&location="+location, function(data) {
																								  alert(\'Location Saved.\');
																								});
			}
			
			function showGPS(position) {
				
				lat = position.coords.latitude;
				lon = position.coords.longitude;			
				
				tmptxt = "<p>";

				';
				
						
				$this->content->text.= 'tmptxt+= "<a href=\"#\" onclick=\"updateLocation("+lat+","+lon+",0)\">Hidden</a><br />";'."\n";
				
				for ($i=0; $i<6; $i++) {
					$tmpstr = 'block_nwkc_geolocation_loc'.$i;
					if ($CFG->$tmpstr) {
						$this->content->text.= 'tmptxt+= "<a href=\"#\" onclick=\"updateLocation("+lat+","+lon+",'.$i.')\">'.$CFG->$tmpstr.'</a><br />";'."\n";
					}
				}
				
				
				$this->content->text.= 'tmptxt+= "</p>";';


		$condindex = 0;
		$tempoutput = '';

		for ($i=0; $i<3; $i++) {
			
			$tmpstr = 'block_nwkc_geolocation_site'.$i;
			
			if ($CFG->$tmpstr) {	
			
				$tmparr = explode(",", $CFG->$tmpstr);
				if (count($tmparr)==4) {
					
					$tmpstr = 'block_nwkc_geolocation_site'.$i.'_desc';
					
					if ($condindex==0) $tempoutput.= "\n\n";
					if ($condindex>0) $tempoutput.= ' else ';
					
					$tempoutput.= 'if ((lat >= '.$tmparr[0].' && lat <= '.$tmparr[2].') && (lon >= '.$tmparr[1].' && lon <= '.$tmparr[3].')) {
							$("#gpsText").html("<p>You are at the '.$CFG->$tmpstr.', please select a location below:</p>");
							$("#gpsText").append(tmptxt);							
						}';
					
					$condindex++;
					
				} // if (count($tmparr)==4)
			} // if ($CFG->$tmpstr)
			
			if ($i==2 && $condindex) {
				
				$tempoutput.= ' else {
						$("#gpsText").html("<p>You are <strong>not</strong> on campus!</p>");
					}	
								
				';
				
			} // if ($i==2 && $condindex)
			
		} // for ($i=0;$i<3;$i++)
		
		
		$this->content->text.= $tempoutput;
				

				

				
				
			$this->content->text.= '
				
				$("#gpsText").append("<p>Latitude: "+lat+"<br>Longitude: "+lon+"</p>");
				$("#gpsText").append("<p><a href=\"#\" onclick=\"getLocation()\">Refresh</a></p>");

			}
			
			</script>
			
			<div align="center">
				<div id="gpsText">Waiting for location...</div>
			</div>
		';
		
		return $this->content;
		
	} // function get_content()
  
} // class block_nwkc_geolocation extends block_base
?>