<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Brings print functionality to your blog
Version: 1.0.0
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


$singleColumnTemplate = 107;
$doubleColumnTemplate = 108;
$defaultDivId         = 2434;
$cleanPrintCcgUrl     = 'http://cache-01.cleanprint.net/cp/ccg';
$defaultLogoUrl       = 'http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg';
$cleanPrintUrl        = 'http://cleanprint.net/cp/t';

$pluginName           = 'cleanprint-lt';
$pluginFile           = $pluginName . '/cleanprint.php';
$pluginAttr           = 'plugin';
$printAttr            = 'print';
$defaultPrintBtnImg   = $pluginName . '/BlogPrintButton.png';
$defaultLocalBtnUrl   = plugins_url($defaultPrintBtnImg);
$defaultVipBtnUrl     = get_bloginfo('template_directory') . '/plugins/' . $defaultPrintBtnImg;
$defaultButtonUrl     = function_exists(wpcom_is_vip) ? $defaultVipBtnUrl : $defaultLocalBtnUrl;
$cpProxyUrl           = 'index.php?' . $pluginAttr . '=' . $pluginName . '&' . $printAttr . '=1';
$optionsName          = 'CleanPrintAdminOptions';


// Display the options page
function pluginOptionsPage() {
   global $optionsName;
   global $pluginName;
?>
    <script language="javascript">
       // Visually and functionally enables/disables the printSpec controls
       function enableIt(enabled) {
          var div   = document.getElementById("printSpecControls");
          var radio = document.getElementById("plugin_printSpecId");

          if (enabled) {
             div.style.color     = "";
             div.style.fontStyle = "";
             radio.disabled      = false;
          } else {
             div.style.color     = "gray";
             div.style.fontStyle = "italic";
             radio.disabled      = true;
          }
       }

       // Examines the activation key text field and enables/disables printSpec if set
       function checkIt(node) {
          enableIt(node.value.length==0);
       }
    </script>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>CleanPrint Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields     ($optionsName); ?>
			<?php do_settings_sections($pluginName); ?>

			<input name="Submit" type="submit" value="Save Changes" />
		</form>
	</div>
<?php
}


// This function takes an activation key and returns an array of divisionID and printSpecID (or null)
function parseActivationKey($theKey){
	$rv = explode("-", $theKey);
	if(count($rv) != 2){
		return null;
	}
	if(!is_numeric($rv[0]) || !is_numeric($rv[1])){
		return null;
	}
	return $rv;
}



// Outputs a section heading but we do not use it
function echoSectionText() {
?>
	The CleanPrint plugin can be configured to print in either a single or double column format
	using a custom header and print button images. 	If an activation key is used, the key
	itself defines the column format thus disabling the choice.

	<p>If a header or print button image URL is provided it should be fully qualified.  Header images
	should be 660x40 otherwise they will be altered to fit the page.  Print buttons should be small.
<?php
}


// WP callback for handling the printSpec (single/double column) option
function echoPrintSpecSetting() {
    global $optionsName;
    global $singleColumnTemplate;
    global $doubleColumnTemplate;
    global $defaultDivId;
    
    $options              = get_option($optionsName);

    $printSpecId          = $options['printSpecId'];
    $activationKey        = $options['activationKey'];

    $doubleChecked        = $printSpecId==$doubleColumnTemplate;
    $singleChecked        = !$doubleChecked;
    $hasActivation        = !empty($activationKey);
    $keys                 = parseActivationKey($activationKey);
    $divId                = empty($keys) ? $defaultDivId                                                  : $keys[0];
    $printSpecId          = empty($keys) ? $singleChecked ? $singleColumnTemplate : $doubleColumnTemplate : $keys[1];

    $disabledAttr         = $hasActivation ? "disabled='disabled'" : "";
    $singleCheckedAttr    = $singleChecked ? "checked='checked'"   : "";
    $doubleCheckedAttr    = $doubleChecked ? "checked='checked'"   : "";

    printf( "<div id='printSpecControls'>\n");
    printf( "<input type='radio' id='plugin_printSpecId' name='%s[printSpecId]' value='%s' %s %s />", $optionsName, $singleColumnTemplate, $disabledAttr, $singleCheckedAttr);
    printf( "Single Column<br />\n");

    printf( "<input type='radio' id='plugin_printSpecId' name='%s[printSpecId]' value='%s' %s %s />", $optionsName, $doubleColumnTemplate, $disabledAttr, $doubleCheckedAttr);
    printf( "Double Column<br />\n");

    printf( "</div>\n");
    printf( "<script>enableIt(%s)</script>\n", $hasActivation ? "false" : "true");
}


// WP callback for handling the Logo URL (default/custom) option
function echoLogoUrlSetting() {
    global $optionsName;
    global $defaultLogoUrl;
    
	$options        = get_option($optionsName);
	$logoUrl        = $options['logoUrl'];
    $customChecked  = isset($logoUrl) && $logoUrl!=$defaultLogoUrl;
    $defaultChecked = !$customChecked;
    $defaultGravity = "center";

    printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='%s' %s />", $optionsName, $defaultLogoUrl, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='custom' %s />", $optionsName, $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='text'  id='plugin_logoUrl' name='%s[customLogo]' value='%s' /><br>\n", $optionsName, $customChecked ? $logoUrl : "");
	printf( "<img width='100%%' src='%s'>", $customChecked ? $logoUrl : $defaultLogoUrl);
}


// WP callback for handling the Print Button URL (default/custom) option
function echoButtonUrlSetting() {
    global $optionsName;
    global $defaultButtonUrl;
    
	$options        = get_option($optionsName);
	$buttonUrl      = $options['buttonUrl'];
    $customChecked  = isset($buttonUrl) && $buttonUrl!=$defaultButtonUrl;
    $defaultChecked = !$customChecked;

    printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='%s' %s />", $optionsName, $defaultButtonUrl, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='custom'  %s />", $optionsName, $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='' name='%s[customButton]' value='%s' /><br>\n", $optionsName, $customChecked ? $buttonUrl : "");
	printf( "<img src='%s'>", $customChecked ? $buttonUrl : $defaultButtonUrl);
}


// WP callback for handling the activation key option, setting an key disables the printSpec controls
function echoActivationKeySetting() {
    global $optionsName;
    
	$options       = get_option($optionsName);
	$activationKey = $options['activationKey'];

	printf( "<input type='text' id='plugin_activationKey' name='%s[activationKey]' value='%s' onKeyUp='checkIt(this)' />", $optionsName, $activationKey);
}


function proxyCleanPrint() {
	global $cleanPrintUrl;

	// Remove PHP's double quoted strings
	$body = array();
	foreach ($_POST as $key=>$value) {
		$body[$key] = stripslashes($value);
	}

	$http   = new WP_Http();		
	$result = $http->request( $cleanPrintUrl, array('method'=>'POST', 'body'=>$body) );
		
	// Check for anything catastrophic.			
	if (isset($result->errors)) {
		header("HTTP/1.0 404 Script Error");
		error_log("proxyCleanUrl: ".$result->errors);
		exit;
	}

	$response = $result['response'];
	$code     = $response['code'];
	$message  = $response['message'];	
		
	// Look for error responses
	if ($code != 200) {	 	
		header("HTTP/1.0 ". $code ." Script Error");
		error_log( sprintf("proxyCleanUrl: %d,%s", $code, $message) );
		exit;
   	}
		
	echo $result['body'];
}


function pluginParseRequest($wp) {
	global $pluginName;
	global $pluginAttr;
	global $printAttr;
	
	$params = $wp->query_vars;
    if (array_key_exists($pluginAttr,$params) && $params[$pluginAttr] == $pluginName) {
        // The only param we support is print
        if (array_key_exists($printAttr,$params)) {
        	proxyCleanPrint();
        }
    }
}


function pluginQueryVars($vars) {
	global $pluginAttr;
	global $printAttr;
		
	array_push($vars, $printAttr,$pluginAttr);
    return $vars;
}


// Clean up the DB properties
function sanitizeSettings($options) {
   global $defaultLogoUrl;
   global $defaultButtonUrl;
   
   $logoUrl      = $options['logoUrl'];
   $customLogo   = $options['customLogo'];
   $buttonUrl    = $options['buttonUrl'];
   $customButton = $options['customButton'];


   if (isset($logoUrl) && $logoUrl!=$defaultLogoUrl) {
      $options['logoUrl'] = $customLogo;
   }

   if (isset($buttonUrl) && $buttonUrl!=$defaultButtonUrl) {
      $options['buttonUrl'] = $customButton;
   }

   unset($options['customLogo']);
   unset($options['customButton']);

   return $options;
}


// WP callback for launching the options menu
function addCleanPrintAdminMenu() {
   global $pluginName;
   add_options_page('CleanPrint Settings', 'CleanPrint', 'manage_options', $pluginName, 'pluginOptionsPage');
}


// WP callback for initializing the options menu
function initCleanPrintAdmin() {
	global $pluginName;
	global $pluginFile;
	global $optionsName;
    
	register_setting       ($optionsName, $optionsName, 'sanitizeSettings');
	register_uninstall_hook($pluginFile, 'addCleanPrintUninstallHook');

	add_settings_section   ('plugin_main', '',      'echoSectionText',  $pluginName);
	add_settings_field     ('plugin_printSpecId',   'Column format',    'echoPrintSpecSetting',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_logoUrl',       'Header image URL', 'echoLogoUrlSetting',       $pluginName, 'plugin_main');
	add_settings_field     ('plugin_buttonUrl',     'Print button URL', 'echoButtonUrlSetting',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_activationKey', 'Activation key',   'echoActivationKeySetting', $pluginName, 'plugin_main');
}


// Adds the CleanPrint button to the page
function addCleanPrintButton() {
	global $optionsName;
	global $defaultButtonUrl;
	 	    
	$options       = get_option($optionsName);
	$buttonUrl     = $options['buttonUrl'];
	$customChecked = isset($buttonUrl) && $buttonUrl!="default";

	return sprintf("<br /><a href='#' onclick='FDCPUrl();return false;'><img src='%s'></a>", $customChecked ? $buttonUrl : $defaultButtonUrl);
}


// Add the hooks for print functionality
function addCleanPrintContentTags($content = '') {
	if (is_single()) {
		//single post content selection tags
		//select title and other elements preceding post body
		$content .= '<span class="fdPrintIncludeParentsPreviousSiblings"></span>';

		//if title and other elements aren't sized correctly in printout, comment out previous line and un-comment out next line
		//$content .= '<span class="fdPrintIncludeParentsPreviousSiblingssChildren"></span>';

		//grab all the nodes of the post body
		$content .= '<span class="fdPrintIncludeParentsChildren"></span>';

		//uncomment out next line if to include node immediately following the post node. Uncomment out line after that for all nodes following post node. Only uncomment out one at a time.
		//$content .= '<span class="fdPrintIncludeParentsNextSibling"></span>';
		//$content .= '<span class="fdPrintIncludeParentsNextSiblings"></span>';

		//if item exist after the post body that belong in the printout, comment out the next line
		$content .= '<span class="fdPrintExcludeNextSiblings"></span>';
		$content .= addCleanPrintButton();

	} else {
		//Multiple blog posts on the page
		//select title and other elements preceding post body
		$content .= '<span class="fdPrintIncludeParentsPreviousSiblings"></span>';

		//if title and other elements aren't sized correctly in printout, comment out previous line and un-comment out next line
		//$content .= '<span class="fdPrintIncludeParentsPreviousSiblingssChildren"></span>';

		//grab all the nodes of the post body
		$content .= '<span class="fdPrintIncludeParentsChildren"></span>';
		//uncomment out next line if to include node immediately following the post node. Uncomment out line after that for all nodes following post node. Only uncomment out one at a time.
		//$content .= '<span class="fdPrintIncludeParentsNextSibling"></span>';
		//$content .= '<span class="fdPrintIncludeParentsNextSiblings"></span>';

		//if item exist after the post body that don't belong in the printout, uncomment out the next line
		//$content ,= '<span class="fdPrintExcludeNextSiblings"></span>';
	}

	return $content;
}


// Adds the CleanPrint script tags to the head section
function addCleanPrintScript() {
    global $optionsName;
    global $singleColumnTemplate;
    global $doubleColumnTemplate;
    global $defaultDivId;
    global $cleanPrintCcgUrl;
    global $defaultLogoUrl;
    global $cpProxyUrl;
    
	if (is_single()) {
		$options              = get_option($optionsName);
		$printSpecId          = $options['printSpecId'];
		$logoUrl              = $options['logoUrl'];
		$gravity              = $options['gravity'];
		$doubleChecked        = $printSpecId==$doubleColumnTemplate;
		$activationKey        = $options['activationKey'];
		$hasActivation        = !empty($activationKey);
		$enabled              = $hasActivation ? " disabled='disabled'" : "";
		$singleChecked        = !$doubleChecked;
		$keys                 = parseActivationKey($activationKey);
		$divId                = empty($keys) ? $defaultDivId : $keys[0];
		$printSpecId          = empty($keys) ? $singleChecked ? $singleColumnTemplate : $doubleColumnTemplate: $keys[1];
		$customChecked        = isset($logoUrl) && $logoUrl!="default";

		if (!isset($gravity)) $gravity = "center";

        printf( "<script type='text/javascript'>\n");
		printf( "   var cpProxyUrl = '%s';\n", $cpProxyUrl);
		printf( "   var cpLogoUrl  = '%s';\n", $customChecked ? $logoUrl : $defaultLogoUrl);
		printf( "   var cpGravity  = '%s';\n", $gravity);  // Gravity is currently unsetable but still required
		printf( "</script>\n");
		printf( "<script type='text/javascript' src='%s?divId=%s&ps=%s' name='cleanprintloader'></script>\n", $cleanPrintCcgUrl, $divId, $printSpecId);
	}
}


// Add the Settings menu link to the plugin page
function addCleanPrintActions($links, $file) {
	global $pluginName;
    global $pluginFile;
    
    if ($file == $pluginFile) {
		$links[] = sprintf("<a href='options-general.php?page=%s'>Settings</a>", $pluginName);
	}
	return $links;
}


// Remove the CleanPrint options from the database
function addCleanPrintUninstallHook() {
    // cannot use the global, chicken/egg problem
	delete_option('CleanPrintAdminOptions');
}


// Actions
add_action('admin_init',          'initCleanPrintAdmin');
add_action('admin_menu',          'addCleanPrintAdminMenu');
add_action('wp_head',             'addCleanPrintScript', 1);
add_action('parse_request',       'pluginParseRequest');

// Filters
add_filter('plugin_action_links', 'addCleanPrintActions', - 10, 2);
add_filter('the_content',         'addCleanPrintContentTags');
add_filter('query_vars',          'pluginQueryVars');

?>