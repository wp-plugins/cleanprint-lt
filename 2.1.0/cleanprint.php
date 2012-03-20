<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Brings print functionality to your blog
Version: 2.1.0
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


$pluginName             = 'cleanprint-lt';
$pluginFile             = $pluginName . '/cleanprint.php';
$pluginAttr             = 'plugin';
$printAttr              = 'print';
$defaultPrintBtnImg     = $pluginName . '/BlogPrintButton.png';
$defaultLocalBtnUrl     = plugins_url($defaultPrintBtnImg);
$defaultButtonColor     = 'black';
$defaultButtonPlacement = 'tr';
$cleanprintUrl          = 'http://cache-02.cleanprint.net/cpf/cleanprint';
$imagesUrl              = 'http://cache-02.cleanprint.net/media/pfviewer/images';
$defaultLogoUrl         = 'http://cache-02.cleanprint.net/media/logos/Default.png';
$defaultVipBtnUrl       = get_bloginfo('template_directory') . '/plugins/' . $defaultPrintBtnImg;
$publisherKey           = 'wpdefault15';
$readmeTxt              = $pluginName . '/readme.txt';
$readmeLocalUrl         = plugins_url($readmeTxt);
$readmeVipUrl           = get_bloginfo('template_directory') . '/plugins/' . $readmeTxt;
$readmeUrl              = function_exists(wpcom_is_vip) ? $readmeVipUrl : $readmeLocalUrl;
$optionsName            = 'CleanPrintAdminOptions';


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
    <p>Thanks for installing CleanPrint on your site and helping your users save paper, ink, money and trees!
    Below are a few options to customize CleanPrint and make it your own. You can use your logo and choose
    from a variety of button styles or use your own button. You may also select the corner of your post
    where the button(s) will appear.</p>
    <p>If you would like to place the button(s) in a custom position please see installation instructions.
    Also, if you choose to use Google Analytics custom event tracking for CleanPrint your site *MUST*
    have Google Analytics running.</p>
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
	printf("<tr><td  colspan='3'><h2>Button Styles</h2><hr /></td></tr>");
}

// WP callback for handling the Print Button URL (default/custom) option
function echoButtonColorSetting() {
    global $optionsName;
    global $readmeUrl;
    global $imagesUrl;
    global $defaultButtonColor;
    
	$options        = get_option($optionsName);
	$buttonColor    = $options['buttonColor'];
	
	if(!isset($buttonColor)) {
        $buttonColor = $defaultButtonColor;
    }
    
    $blackChecked   = $buttonColor=='black';
    $whiteChecked   = $buttonColor=='white';
    $transChecked   = $buttonColor=='transparent';
    $textChecked    = $buttonColor=='text';
	
	printf("<script>function changeButtons(select) {");
	printf("var index  = select.selectedIndex;");
	printf("var value  = select.options[index].value;");
	printf("cpUrl    = '$imagesUrl/CleanPrintBtn_' + value + '.png';");
	printf("pdfUrl   = '$imagesUrl/PdfBtn_'        + value + '.png';");
	printf("emailUrl = '$imagesUrl/EmailBtn_'      + value + '.png';");
	printf("document.getElementById('cpImg')   .src = cpUrl;");
	printf("document.getElementById('pdfImg')  .src = pdfUrl;");
	printf("document.getElementById('emailImg').src = emailUrl;");
	printf("}</script>");

	printf( "<select id='plugin_buttonColor' name='%s[buttonColor]' onchange='changeButtons(this); return false;'>", $optionsName);
	printf( "<option value='white'       %s>White</option>",       ($whiteChecked ? "selected='selected'" : ""));
	printf( "<option value='black'       %s>Black</option>",       ($blackChecked ? "selected='selected'" : ""));
	printf( "<option value='transparent' %s>Transparent</option>", ($transChecked ? "selected='selected'" : ""));
	printf( "<option value='text'        %s>Simple</option>",      ($textChecked  ? "selected='selected'" : ""));
//	printf( "<option value='none'        %s>None</option>",        ($removeChecked? "selected='selected'" : ""));
	printf( "</select>");
	
	printf( "<td>Button Preview<br /><div id='sampleArea' style='border: 1px solid #BBB; padding: 10px; text-align:center;'>");
	printf( "<img id='cpImg'    src='$imagesUrl/CleanPrintBtn_$buttonColor.png'>");
	printf( "<img id='pdfImg'   src='$imagesUrl/PdfBtn_$buttonColor.png'>");
    printf( "<img id='emailImg' src='$imagesUrl/EmailBtn_$buttonColor.png'>");
	printf( "</div></td>");
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
    global $defaultButtonPlacement;
    
	$options         = get_option($optionsName);
	$ButtonPlacement = $options['ButtonPlacement'];
	
	if (!isset($ButtonPlacement)) {
	   $ButtonPlacement = $defaultButtonPlacement;
	}
	
	$trChecked  = $ButtonPlacement=="tr";
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
	printf("<tr><td colspan='3'><h2>Display Button(s) on the Following:</h2><hr /></td></tr>");  
}

// WP callback for handling page type
function echoPageTypeHomePage() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $homepage    = $options['homepage'];
    $isChecked   = $homepage=="include" || !isset($homepage);
    
    printf( "<select id='plugin_homepage' name='%s[homepage]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");          
}

function echoPageTypeFrontpage() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $frontpage   = $options['frontpage'];
    $isChecked   = $frontpage=="include" || !isset($frontpage);
    
    printf( "<select id='plugin_frontpage' name='%s[frontpage]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
}

function echoPageTypeCategory() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $category    = $options['category'];
    $isChecked   = $category=="include" || !isset($category);
    
    printf( "<select id='plugin_category' name='%s[category]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
}

function echoPageTypePosts() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $posts       = $options['posts'];
    $isChecked   = $posts=="include" || !isset($posts);
    
    printf( "<select id='plugin_posts' name='%s[posts]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");    
}

function echoPageTypePages() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $pages       = $options['pages'];
    $isChecked   = $pages=="include" || !isset($pages);
    
    printf( "<select id='plugin_pages' name='%s[pages]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");    
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

	add_settings_section   ('plugin_main', '',        'echoSectionText',    $pluginName);
	add_settings_field     ('plugin_logoUrl',         '<strong>Image:</strong>',                     'echoLogoUrlSetting',     $pluginName, 'plugin_main');
	add_settings_field     ('plugin_buttonColor',     '<strong>Color:</strong>',                     'echoButtonColorSetting', $pluginName, 'plugin_main');
	add_settings_field     ('plugin_PrintInclude',    '<strong>Display Print Button:</strong>',      'echoPrintInclude',       $pluginName, 'plugin_main');
	add_settings_field     ('plugin_PDFInclude',      '<strong>Display PDF Button:</strong>',        'echoPDFInclude',         $pluginName, 'plugin_main');
	add_settings_field     ('plugin_EmailInclude',    '<strong>Display Email Button:</strong>',      'echoEmailInclude',       $pluginName, 'plugin_main');
	add_settings_field     ('plugin_buttonplacement', '<strong>Page Location:</strong>',             'echoButtonPlacement',    $pluginName, 'plugin_main');
	add_settings_field     ('plugin_homepage',        '<strong>Homepage:</strong>',                  'echoPageTypeHomePage',   $pluginName, 'plugin_main');
    add_settings_field     ('plugin_frontpage',       '<strong>Frontpage:</strong>',                 'echoPageTypeFrontpage',  $pluginName, 'plugin_main');
    add_settings_field     ('plugin_category',        '<strong>Categories:</strong>',                'echoPageTypeCategory',   $pluginName, 'plugin_main');    
    add_settings_field     ('plugin_posts',           '<strong>Posts:</strong>',                     'echoPageTypePosts',      $pluginName, 'plugin_main');
    add_settings_field     ('plugin_pages',           '<strong>Pages:</strong>',                     'echoPageTypePages',      $pluginName, 'plugin_main');
    add_settings_field     ('plugin_gaOption',        '<strong>CleanPrint event tracking:</strong>', 'echoGASetting',          $pluginName, 'plugin_main');
}


function showButton() {
    global $optionsName;

    $options       = get_option($optionsName);
    $homepage      = $options['homepage'];
    $frontpage     = $options['frontpage'];
    $category      = $options['category'];
    $posts         = $options['posts'];
    $pages         = $options['pages'];
    
    $isHomeChecked = $homepage =='include' || !isset($homepage);
    $isFrntChecked = $frontpage=='include' || !isset($frontpage);
    $isCatgChecked = $category =='include' || !isset($category);
    $isPostChecked = $posts    =='include' || !isset($posts);
    $isPageChecked = $pages    =='include' || !isset($pages);
    
    if (is_home()       && $isHomeChecked) return true;
    if (is_category()   && $isCatgChecked) return true;
    if (is_single()     && $isPostChecked) return true;
    if (is_front_page() && $isFrntChecked) return true;              
    if (is_page()       && $isPageChecked) return true;
    
    return false;
}

// Add the hooks for print functionality
function addCleanPrintContentTags($content) {
	
	global $optionsName;
	global $imagesUrl;
	global $defaultButtonColor;
	global $defaultButtonPlacement;
	 	    
	$options         = get_option($optionsName);
	$buttonColor     = $options['buttonColor'];
    $ButtonPlacement = $options['ButtonPlacement'];
    
    $showPrintBtn    = $options['PrintInclude']=='include' || !isset($options['PrintInclude']);
    $showPdfBtn      = $options['PDFInclude']  =='include' || !isset($options['PDFInclude']);
    $showEmailBtn    = $options['EmailInclude']=='include' || !isset($options['EmailInclude']);
    
    if (!isset($ButtonPlacement)) {
       $ButtonPlacement = $defaultButtonPlacement;
    }
    
    
	
	if (showButton()) {
	   if (!isset($buttonColor)) {
            $buttonColor = $defaultButtonColor;
        }

        if ($showPrintBtn) {
            $buttons .= "<a href='.' onClick='CleanPrint();return false' title='Print page'><img src='$imagesUrl/CleanPrintBtn_$buttonColor.png' /></a>";
        }

        if ($showPdfBtn) {
            $buttons .= "<a href='.' onClick='CleanPDF();return false' title='PDF page'><img src='$imagesUrl/PdfBtn_$buttonColor.png' /></a>";
        }

        if ($showEmailBtn) {
            $buttons .= "<a href='.' onClick='CleanEmail();return false' title='Email page'><img src='$imagesUrl/EmailBtn_$buttonColor.png' /></a>";
        }


        if (isset($buttons)) {
            if ($ButtonPlacement=="tl") {
                $content = sprintf("%s<br />%s", $buttons, $content);

            } else if ($ButtonPlacement=="tr") {
                $content = sprintf("<div style='text-align:right;'>%s</div><br />%s", $buttons, $content);

            } else if($ButtonPlacement=="bl") {
                $content = sprintf("%s<br />%s", $content, $buttons);

            } else {
                $content = sprintf("%s<br /><div style='text-align:right;'>%s</div>", $content, $buttons);
            }
        }
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