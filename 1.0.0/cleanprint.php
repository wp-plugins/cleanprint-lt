<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Brings print functionality to your blog
Version: 1.0.0
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/


// Display the options page
function plugin_options_page() {
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
			<?php settings_fields     ("CleanPrintAdminOptions"); ?>
			<?php do_settings_sections("cleanprint.php"); ?>

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
	itself defines the column format thus deactivating the choice.

	<p>If a header or print button image URL is provided it should be fully qualified.  Header images
	should also be 660x40.  Print buttons should be small.
<?php
}


// WP callback for handling the printSpec (single/double column) option
function echoPrintSpecSetting() {
	$options              = get_option("CleanPrintAdminOptions");
    $singleColumnTemplate = 107;
    $doubleColumnTemplate = 108;
    $defaultDivId         = 2434;

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
    printf( "<input type='radio' id='plugin_printSpecId' name='CleanPrintAdminOptions[printSpecId]' value='%s' %s %s />", $singleColumnTemplate, $disabledAttr, $singleCheckedAttr);
    printf( "Single Column<br />\n");

    printf( "<input type='radio' id='plugin_printSpecId' name='CleanPrintAdminOptions[printSpecId]' value='%s' %s %s />", $doubleColumnTemplate, $disabledAttr, $doubleCheckedAttr);
    printf( "Double Column<br />\n");

    printf( "</div>\n");
    printf( "<script>enableIt(%s)</script>\n", $hasActivation ? "false" : "true");
}


// WP callback for handling the Logo URL (default/custom) option
function echoLogoUrlSetting() {
	$options        = get_option("CleanPrintAdminOptions");
	$defaultLogoUrl = "http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg";
    $logoUrl        = $options['logoUrl'];
    $customChecked  = isset($logoUrl) && $logoUrl!=$defaultLogoUrl;
    $defaultChecked = !$customChecked;
    $defaultGravity = "center";

    printf( "<input type='radio' id='plugin_logoUrl' name='CleanPrintAdminOptions[logoUrl]' value='%s' %s />", $defaultLogoUrl, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='CleanPrintAdminOptions[logoUrl]' value='custom' %s />", $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='text'  id='plugin_logoUrl' name='CleanPrintAdminOptions[customLogo]' value='%s' /><br>\n", $customChecked ? $logoUrl : "");
	printf( "<img width='100%%' src='%s'>", $customChecked ? $logoUrl : $defaultLogoUrl);
}


// WP callback for handling the Print Button URL (default/custom) option
function echoButtonUrlSetting() {
	$options          = get_option("CleanPrintAdminOptions");
	$defaultButtonUrl = plugins_url('cleanprint-lt/BlogPrintButton.png');
    $buttonUrl        = $options['buttonUrl'];
    $customChecked    = isset($buttonUrl) && $buttonUrl!=$defaultButtonUrl;
    $defaultChecked   = !$customChecked;

    printf( "<input type='radio' id='plugin_buttonUrl' name='CleanPrintAdminOptions[buttonUrl]' value='%s' %s />", $defaultButtonUrl, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_buttonUrl' name='CleanPrintAdminOptions[buttonUrl]' value='custom'  %s />", $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='' name='CleanPrintAdminOptions[customButton]' value='%s' /><br>\n", $customChecked ? $buttonUrl : "");
	printf( "<img src='%s'>", $customChecked ? $buttonUrl : $defaultButtonUrl);
}


// WP callback for handling the activation key option, setting an key disables the printSpec controls
function echoActivationKeySetting() {
	$options       = get_option("CleanPrintAdminOptions");
	$activationKey = $options['activationKey'];

	printf( "<input type='text' id='plugin_activationKey' name='CleanPrintAdminOptions[activationKey]' value='%s' onKeyUp='checkIt(this)' />", $activationKey);
}


// Clean up the DB properties
function sanitizeSettings($options) {
   $defaultLogoUrl   = 'http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg';
   $defaultButtonUrl = plugins_url('cleanprint-lt/BlogPrintButton.png');
   $logoUrl          = $options['logoUrl'];
   $customLogo       = $options['customLogo'];
   $buttonUrl        = $options['buttonUrl'];
   $customButton     = $options['customButton'];


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
	add_options_page("CleanPrint Settings", "CleanPrint", 'manage_options', "cleanprint.php", "plugin_options_page");
}


// WP callback for initializing the options menu
function initCleanPrintAdmin() {
	register_setting    ("CleanPrintAdminOptions", "CleanPrintAdminOptions", "sanitizeSettings");
	add_settings_section("plugin_main", "",      "echoSectionText",  "cleanprint.php");
	add_settings_field  ("plugin_printSpecId",   "Column format",    "echoPrintSpecSetting",     "cleanprint.php", "plugin_main");
	add_settings_field  ("plugin_logoUrl",       "Header image URL", "echoLogoUrlSetting",       "cleanprint.php", "plugin_main");
	add_settings_field  ("plugin_buttonUrl",     "Print button URL", "echoButtonUrlSetting",     "cleanprint.php", "plugin_main");
	add_settings_field  ("plugin_activationKey", "Activation key",   "echoActivationKeySetting", "cleanprint.php", "plugin_main");
}


// Adds the CleanPrint button to the page
function addCleanPrintButton() {
	if(is_single()) {
	    $options          = get_option("CleanPrintAdminOptions");
		$defaultButtonUrl = plugins_url('cleanprint-lt/BlogPrintButton.png');
	    $buttonUrl        = $options['buttonUrl'];
	    $customChecked    = isset($buttonUrl) && $buttonUrl!="default";

		return sprintf("<br /><a href='#' onclick='FDCPUrl();return false;'><img src='%s'></a>", $customChecked ? $buttonUrl : $defaultButtonUrl);
	}
	return '';
}


// Add the hooks for print functionality
function addCleanPrintContentTags($content = '') {
	if(is_single()) {
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
		return $content;

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
		return $content;
	}
}


// ???  Not apparently used
function addCleanPrintTitleTags($title = ''){
	if(is_single()){
		return "<span class='fdPrintIncludeParent'></span>".$title;
	}
	return $title;
}


// Adds the CleanPrint script tags to the head section
function addCleanPrintScript() {
	if(is_single()) {
		$options              = get_option("CleanPrintAdminOptions");
		$singleColumnTemplate = 107;
		$doubleColumnTemplate = 108;
		$defaultDivId         = 2434;
		$printSpecId          = $options['printSpecId'];
		$logoUrl              = $options["logoUrl"];
		$gravity              = $options["gravity"];
		$doubleChecked        = $printSpecId==$doubleColumnTemplate;
		$activationKey        = $options['activationKey'];
		$hasActivation        = !empty($activationKey);
		$enabled              = $hasActivation ? " disabled='disabled'" : "";
		$singleChecked        = !$doubleChecked;
		$keys                 = parseActivationKey($activationKey);
		$divId                = empty($keys) ? $defaultDivId : $keys[0];
		$printSpecId          = empty($keys) ? $singleChecked ? $singleColumnTemplate : $doubleColumnTemplate: $keys[1];
		$defaultLogoUrl       = "http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg";
 		$customChecked        = isset($logoUrl) && $logoUrl!="default";

		if (!isset($gravity)) $gravity = "center";

        printf( "<script type='text/javascript'>\n");
		printf( "   var cpProxyUrl = '%s';\n", plugins_url('cleanprint-lt/proxy.php'));
		printf( "   var cpLogoUrl  = '%s';\n", $customChecked ? $logoUrl : $defaultLogoUrl);
		printf( "   var cpGravity  = '%s';\n", $gravity);  // Gravity is currently unsetable but still required
		printf( "</script>\n");
		printf( "<script type='text/javascript' src='http://cache-01.cleanprint.net/cp/ccg?divId=%s&ps=%s' name='cleanprintloader'></script>\n", $divId, $printSpecId);
	}
}


function addCleanPrintActions($links, $file) {
    if ($file == 'cleanprint-lt/cleanprint.php') {
	    $links[] = sprintf("<a href='options-general.php?page=%s'>Settings</a>", "cleanprint.php");
   	}
   	return $links;
}


// Actions
add_action('admin_init', 'initCleanPrintAdmin');
add_action('admin_menu', 'addCleanPrintAdminMenu');
add_action('wp_head',    'addCleanPrintScript', 1);
add_action('wp_meta',    'addCleanPrintButton');


// Filters
add_filter('plugin_action_links', 'addCleanPrintActions', - 10, 2);
add_filter('the_content',         'addCleanPrintContentTags');

?>