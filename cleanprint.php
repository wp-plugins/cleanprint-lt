<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Brings print functionality to your blog
Version: 2.0
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


$pluginName           = 'cleanprint-lt';
$pluginFile           = $pluginName . '/cleanprint.php';
$pluginAttr           = 'plugin';
$printAttr            = 'print';
$defaultPrintBtnImg   = $pluginName . '/BlogPrintButton.png';
$defaultLocalBtnUrl   = plugins_url($defaultPrintBtnImg);
$defaultLogoUrl       = 'http://cache-02.cleanprint.net/media/logos/Default.png';
$defaultVipBtnUrl     = get_bloginfo('template_directory') . '/plugins/' . $defaultPrintBtnImg;
$publisherKey         = 'wpdefault15';
$cleanprintUrl        = 'http://cache-02.cleanprint.net/cpf/cleanprint';
$buttonUrl            = 'http://cache-02.cleanprint.net/media/pfviewer/images';
$blackPrintButtonUrl  = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_black.png';
$blackPDFButtonUrl    = 'http://cache-02.cleanprint.net/media/pfviewer/images/PdfBtn_black.png';
$blackEmailButtonUrl  = 'http://cache-02.cleanprint.net/media/pfviewer/images/EmailBtn_black.png';
$whitePrintButtonUrl  = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_white.png';
$whitePDFButtonUrl    = 'http://cache-02.cleanprint.net/media/pfviewer/images/PdfBtn_white.png';
$whiteEmailButtonUrl  = 'http://cache-02.cleanprint.net/media/pfviewer/images/EmailBtn_white.png';
$opaquePrintButtonUrl = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_transparent.png';
$opaquePDFButtonUrl   = 'http://cache-02.cleanprint.net/media/pfviewer/images/PdfBtn_transparent.png';
$opaqueEmailButtonUrl = 'http://cache-02.cleanprint.net/media/pfviewer/images/EmailBtn_transparent.png';
$textPrintButtonUrl   = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_text.png';
$textPDFButtonUrl     = 'http://cache-02.cleanprint.net/media/pfviewer/images/PdfBtn_text.png';
$textEmailButtonUrl   = 'http://cache-02.cleanprint.net/media/pfviewer/images/EmailBtn_text.png';
$readmeTxt            = $pluginName . '/readme.txt';
$readmeLocalUrl       = plugins_url($readmeTxt);
$readmeVipUrl         = get_bloginfo('template_directory') . '/plugins/' . $readmeTxt;
$readmeUrl            = function_exists(wpcom_is_vip) ? $readmeVipUrl : $readmeLocalUrl;
$optionsName          = 'CleanPrintAdminOptions';


// Display the options page
function pluginOptionsPage() {
   global $optionsName;
   global $pluginName;
?>
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


// Outputs a section heading but we do not use it
function echoSectionText() {
?>
	Thanks for installing CleanPrint on your site and helping your users save paper, ink, money and trees! Below are a few options to customize CleanPrint and make it your own. You can use your logo and choose from a variety of button styles or use your own button. You may also select the corner of your post where the button(s) will appear. 
	If you would like to place the button(s) in a custom position please see intallation instructions. Also, if you choose to use Google Analytics custom event tracking for CleanPrint your site *MUST* have Google Analytics running.  
	<?php printf("<tr><td><h2>Logo</h2><hr /></td></tr>");?>
<?php
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
	printf( "<td>Logo Preview<br /><div style='background-color:#DDD; border: 1px solid #BBB; padding: 10px; text-align:center;'><img height='40px' src='%s'></div></td>", $customChecked ? $logoUrl : $defaultLogoUrl);
	printf("<tr><td  colspan='3'><h2>Buttons</h2><hr /></td></tr>");
}

// WP callback for handling the Print Button URL (default/custom) option
function echoButtonColorSetting() {
    global $optionsName;
    global $readmeUrl;
    global $buttonUrl;
    
	$options        = get_option($optionsName);
	$buttonColor    = $options['buttonColor'];
    $blackChecked   = $buttonColor=='black';
    $whiteChecked   = !isset($buttonColor) || $buttonColor=='white';
    $transparentChecked  =  $buttonColor=='transparent';
    $textChecked    =  $buttonColor=='text';
//    $removeChecked  =  $buttonColor=='none';
	$customChecked  =  $blackChecked==false && $whiteChecked==false && $opaqueChecked==false && $textChecked==false && $removeChecked==false;
	
	if(!isset($options['buttonColor'])) {
		$buttonColor = 'white';
	}
	
	printf("<script>function changeButtons(select) {");
	printf("var index  = select.selectedIndex;");
	printf("var value  = select.options[index].value;");
	printf("cpUrl    = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_' + value + '.png';");
	printf("pdfUrl   = 'http://cache-02.cleanprint.net/media/pfviewer/images/PdfBtn_'        + value + '.png';");
	printf("emailUrl = 'http://cache-02.cleanprint.net/media/pfviewer/images/EmailBtn_'      + value + '.png';");
	printf("document.getElementById('cpImg')   .src = cpUrl;");
	printf("document.getElementById('pdfImg')  .src = pdfUrl;");
	printf("document.getElementById('emailImg').src = emailUrl;");
	printf("}</script>");

	printf( "<select id='plugin_buttonColor' name='%s[buttonColor]' onchange='changeButtons(this); return false;'>", $optionsName);
	printf( "<option value='white' %s>White</option>", ($whiteChecked?"selected='selected'":""));
	printf( "<option value='black' %s>Black</option>", ($blackChecked?"selected='selected'":""));
	printf( "<option value='transparent' %s>Transparent</option>", ($transparentChecked?"selected='selected'":""));
	printf( "<option value='text' %s>Simple</option>", ($textChecked?"selected='selected'":""));
//	printf( "<option value='none' %s>None</option>", ($removeChecked?"selected='selected'":""));
	printf( "</select> <td>Button Preview<br /><div id='sampleArea' style='border: 1px solid #BBB; padding: 10px; text-align:center;'><img id='cpImg' src='$buttonUrl/CleanPrintBtn_$buttonColor.png'><img id='pdfImg' src='$buttonUrl/PdfBtn_$buttonColor.png'><img id='emailImg' src='$buttonUrl/EmailBtn_$buttonColor.png'></div></td>");

}

// WP callback for handling button include
function echoPrintInclude() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$PrintInclude    = $options['PrintInclude'];
	$printChecked    = !isset($PrintInclude) || $PrintInclude =="include";
	
	printf( "<select id='plugin_PrintInclude' name='%s[PrintInclude]'>", $optionsName);
	printf( "<option value='include' %s>Include</option>", ($printChecked ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Exclude</option>", (!$printChecked ?"selected='selected'":""));
	printf( "</select>");

}

// WP callback for handling button include
function echoPDFInclude() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$PDFInclude      = $options['PDFInclude'];
    $pdfChecked      = !isset($PDFInclude) || $PDFInclude =="include";
	
	printf( "<select id='plugin_PDFInclude' name='%s[PDFInclude]'>", $optionsName);
	printf( "<option value='include' %s>Include</option>", ($pdfChecked  ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Exclude</option>", (!$pdfChecked ?"selected='selected'":""));
	printf( "</select>");
	
}

// WP callback for handling button include
function echoEmailInclude() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$EmailInclude    = $options['EmailInclude'];
	$emailChecked    = !isset($EmailInclude) || $EmailInclude =="include";
	
	printf( "<select id='plugin_EmailInclude' name='%s[EmailInclude]'>", $optionsName);
	printf( "<option value='include' %s>Include</option>", ($emailChecked  ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Exclude</option>", (!$emailChecked  ?"selected='selected'":""));
	printf( "</select>");
}

// WP callback for handling button placement
function echoButtonPlacement() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$ButtonPlacement = $options['ButtonPlacement'];
	$trChecked  = !isset($ButtonPlacement) || $ButtonPlacement=="tr";
    $tlChecked  = $ButtonPlacement=="tl";
	$blChecked  = $ButtonPlacement=="bl";
	$brChecked  = $ButtonPlacement=="br";
	
    printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='tl' %s />", $optionsName, $tlChecked ?"checked='checked'":"");
	printf( "Top Left<br />\n");

	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='tr' %s />", $optionsName, $trChecked  ?"checked='checked'":"");
	printf( "Top Right<br />\n");
	
	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='bl' %s />", $optionsName, $blChecked  ?"checked='checked'":"");
	printf( "Bottom Left<br />\n");
	
	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='br' %s />", $optionsName, $brChecked  ?"checked='checked'":"");
	printf( "Bottom Right<br />\n");
	printf("<tr><td  colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");
}

// WP callback for handling the Google Analytics option
function echoGASetting() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$GASetting       = $options['GASetting'];
	$disabledChecked = !isset($GASetting) || $GASetting=="false";
    $enabledChecked  = $GASetting;
    
    printf( "<input type='radio' id='plugin_gaOption' name='%s[GASetting]' value='true' %s />", $optionsName, $enabledChecked?"checked='checked'":"");
	printf( "Enabled<br />\n");

	printf( "<input type='radio' id='plugin_gaOption' name='%s[GASetting]' value='false' %s />", $optionsName, $disabledChecked ?"checked='checked'":"");
	printf( "Disabled<br /><br />\n");
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
   
   $logoUrl      		 = $options['logoUrl'];
   $customLogo   		 = $options['customLogo'];
   $buttonColor       	 = $options['buttonColor'];
   $customPrintButton    = $options['customPrintButton'];
   $GASetting      		 = $options['GASetting'];
   $ButtonPlacement		 = $options['ButtonPlacement'];
   
    if (isset($logoUrl) && $logoUrl!=$defaultLogoUrl) {
      $options['logoUrl'] = $customLogo;
   }

   if (isset($buttonColor) && $buttonColor=="custom") {
      $options['buttonColor'] = $customPrintButton;
   }

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

	add_settings_section   ('plugin_main', '',      'echoSectionText',    $pluginName);
	add_settings_field     ('plugin_logoUrl',       '<strong>Logo Image:</strong>',       'echoLogoUrlSetting',       $pluginName, 'plugin_main');
	add_settings_field     ('plugin_buttonColor',     '<strong>Button Style:</strong>', 'echoButtonColorSetting',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_PrintInclude',     '<strong>Print Button:</strong>', 'echoPrintInclude',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_PDFInclude',     '<strong>PDF Button:</strong>', 'echoPDFInclude',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_EmailInclude',     '<strong>Email Button:</strong>', 'echoEmailInclude',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_buttonplacement',      '<strong>Button Placement:</strong>',   'echoButtonPlacement',      $pluginName, 'plugin_main');
	add_settings_field     ('plugin_gaOption',      '<strong>Google Analytics CleanPrint event tracking:</strong>',   'echoGASetting',            $pluginName, 'plugin_main');
}

// Add the hooks for print functionality
function addCleanPrintContentTags($content) {
	
	global $optionsName;
    global $blackPrintButtonUrl;
	global $buttonUrl;
	 	    
	$options         = get_option($optionsName);
	$buttonColor     = $options['buttonColor'];
    $nothingChecked  = !isset($buttonColor);
	$removeChecked   = $options['PrintInclude'] == 'exclude' && $options['PDFInclude'] == 'exclude' && $options['EmailInclude'] == 'exclude';
	$ButtonPlacement = $options['ButtonPlacement'];
	
	if ($options['PrintInclude'] == 'include') {
		$displayPrint = 'inline';
	}else{
		$displayPrint = 'none';
	}
	if ($options['PDFInclude'] == 'include') {
		$displayPDF = 'inline';
	}else{
		$displayPDF = 'none';
	}
	if ($options['EmailInclude'] == 'include') {
		$displayEmail = 'inline';
	}else{
		$displayEmail = 'none';
	}
	    
    if ($nothingChecked) {
        $buttonColor = 'white';
    }

	if ($removeChecked) {
		//dont add a button
    } else if ($ButtonPlacement=="tl") {
		$content = sprintf("<a style='display:$displayPrint' href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='$buttonUrl/CleanPrintBtn_$buttonColor.png' /></a><a style='display:$displayPDF' href='.' onClick='CleanPDF(); return false' class='button' title='PDF page'><img src='$buttonUrl/PdfBtn_$buttonColor.png' /></a><a style='display:$displayEmail' href='.' onClick='CleanEmail(); return false' class='button' title='Email page'><img src='$buttonUrl/EmailBtn_$buttonColor.png' /></a><br />%s", $content);
	} else if ($ButtonPlacement=="tr") {
		$content = sprintf("<div style='text-align: right;'><a style='display:$displayPrint' href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='$buttonUrl/CleanPrintBtn_$buttonColor.png' /></a><a style='display:$displayPDF' href='.' onClick='CleanPDF(); return false' class='button' title='PDF page'><img src='$buttonUrl/PdfBtn_$buttonColor.png' /></a><a style='display:$displayEmail' href='.' onClick='CleanEmail(); return false' class='button' title='Email page'><img src='$buttonUrl/EmailBtn_$buttonColor.png' /></a></div><br />%s", $content);
	} else if($ButtonPlacement=="bl") {
		$content = sprintf("%s<br /><a style='display:$displayPrint' href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='$buttonUrl/CleanPrintBtn_$buttonColor.png' /></a><a style='display:$displayPDF' href='.' onClick='CleanPDF(); return false' class='button' title='PDF page'><img src='$buttonUrl/PdfBtn_$buttonColor.png' /></a><a style='display:$displayEmail' href='.' onClick='CleanEmail(); return false' class='button' title='Email page'><img src='$buttonUrl/EmailBtn_$buttonColor.png' /></a>", $content);	
	} else {
		$content = sprintf("%s<br /><div style='text-align: right;'><a style='display:$displayPrint' href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='$buttonUrl/CleanPrintBtn_$buttonColor.png' /></a><a style='display:$displayPDF' href='.' onClick='CleanPDF(); return false' class='button' title='PDF page'><img src='$buttonUrl/PdfBtn_$buttonColor.png' /></a><a style='display:$displayEmail' href='.' onClick='CleanEmail(); return false' class='button' title='Email page'><img src='$buttonUrl/EmailBtn_$buttonColor.png' /></a></div>", $content);	
	}
	return $content;
}


// Adds the CleanPrint script tags to the head section
function addCleanPrintScript() {
    global $optionsName;
    global $cleanprintUrl;
    global $publisherKey;
	global $defaultLogoUrl;
   
	$options              = get_option($optionsName);
	$GASetting            = $options['GASetting'];
	$gravity              = $options['gravity'];
	$logoUrl              = $options['logoUrl'];
	$customChecked		  = isset($logoUrl) && $logoUrl!=$defaultLogoUrl;
	
	if (!isset($gravity)) $gravity = "center";
		
		printf( "<script type='text/javascript'>\n");
		printf( "   function CleanPrint() {");
		printf( "   	CleanPrintPrintHtml();\n");
						if ($GASetting=="true") {
							printf( "   _gaq.push(['_trackEvent', 'CleanPrint', 'Print']);\n");
						}
		printf( "   }");
		printf( "   function CleanEmail() {");
		printf( "   	CleanPrintSendEmail();\n");
						if ($GASetting=="true") {
							printf( "   _gaq.push(['_trackEvent', 'CleanPrint', 'Email']);\n");
						}
		printf( "   }");
		printf( "   function CleanPDF() {");
		printf( "   	CleanPrintGeneratePdf();\n");
						if ($GASetting=="true") {
							printf( "   _gaq.push(['_trackEvent', 'CleanPrint', 'PDF']);\n");
						}
		printf( "   }");
		printf( "</script>\n");
	
	printf( "<script language='javascript' type='text/javascript' src='%s?key=%s&logo=%s'></script>\n", $cleanprintUrl, $publisherKey, $customChecked ? $logoUrl : $defaultLogoUrl);
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

// Filters
add_filter('plugin_action_links', 'addCleanPrintActions', - 10, 2);
add_filter('the_content',         'addCleanPrintContentTags');
add_filter('query_vars',          'pluginQueryVars');

?>