<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Brings print functionality to your blog
Version: 1.5.0
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


$pluginName           = 'cleanprint-lt';
$pluginFile           = $pluginName . '/cleanprint.php';
$pluginAttr           = 'plugin';
$printAttr            = 'print';
$publisherKey         = 'wpdefault15'; 
$cleanprintUrl        = 'http://cache-02.cleanprint.net/cpf/cleanprint';
$blackButtonUrl       = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_black.png';
$whiteButtonUrl       = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_white.png';
$opaqueButtonUrl      = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_transparent.png';
$textButtonUrl        = 'http://cache-02.cleanprint.net/media/pfviewer/images/CleanPrintBtn_text.png';
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
    Provided is the ability to use a variety of print buttons styles or your own button.  
    If Google Analytics is enabled for your site you also have the ability to track print activity (**The site must contain Google Analytic for tracking to function**).  
<?php
}


// WP callback for handling the Print Button URL (default/custom) option
function echoButtonUrlSetting() {
    global $optionsName;
    global $blackButtonUrl;
    global $whiteButtonUrl;
    global $opaqueButtonUrl;
    global $textButtonUrl;
    global $readmeUrl;
    
    $options        = get_option($optionsName);
    $buttonUrl      = $options['buttonUrl'];
    $blackChecked   = !isset($buttonUrl) || $buttonUrl==$blackButtonUrl;
    $whiteChecked   =  $buttonUrl==$whiteButtonUrl;
    $opaqueChecked  =  $buttonUrl==$opaqueButtonUrl;
    $textChecked    =  $buttonUrl==$textButtonUrl;
    $removeChecked  =  $buttonUrl=='none';
    $customChecked  =  $blackChecked==false && $whiteChecked==false && $opaqueChecked==false && $textChecked==false && $removeChecked==false;

    printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='%s' %s />", $optionsName, $blackButtonUrl, ($blackChecked?"checked='checked'":""));
    printf( " <img src='$blackButtonUrl'><br />\n");

    printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='%s' %s />", $optionsName, $whiteButtonUrl, ($whiteChecked?"checked='checked'":""));
    printf( "<img src='$whiteButtonUrl'><br />\n");

    printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='%s' %s />", $optionsName, $opaqueButtonUrl, ($opaqueChecked?"checked='checked'":""));
    printf( " <img src='$opaqueButtonUrl'><br />\n");

    printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='%s' %s />", $optionsName, $textButtonUrl, ($textChecked?"checked='checked'":""));
    printf( " <img src='$textButtonUrl'><br />\n");
    
    printf( "<input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='custom' %s />", $optionsName, ($customChecked ?"checked='checked'":""));
    printf( "Custom:");
    printf( "<input type='' name='%s[customButton]' value='%s' /><br>\n", $optionsName, $customChecked ? $buttonUrl : "");
    
    printf( "<br /><input type='radio' id='plugin_buttonUrl' name='%s[buttonUrl]' value='none' %s />", $optionsName, $removeChecked ?"checked='checked'":"");
    printf( "Remove (see <a href='%s' target='_wpinst'>installation</a> instructions)<br />", $readmeUrl);
    //  printf( "<img id='btnImg' src='%s' %s>", 
    //  $customChecked ? $buttonUrl : $blackButtonUrl, 
    //  $noneChecked   ? "style='opacity:0.25;filter:alpha(opacity=25);'" : ""); 
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
    printf( "Disabled\n");
}

function pluginQueryVars($vars) {
    global $pluginAttr;
    global $printAttr;
        
    array_push($vars, $printAttr,$pluginAttr);
    return $vars;
}


// Clean up the DB properties
function sanitizeSettings($options) {
   global $blackuttonUrl;
   global $whiteButtonUrl;
   global $opaqueButtonUrl;
   global $textButtonUrl;
   
   $buttonUrl       = $options['buttonUrl'];
   $customButton    = $options['customButton'];
   $GASetting       = $options['GASetting'];
   $ButtonPlacement = $options['ButtonPlacement'];

   if (isset($buttonUrl) && $buttonUrl=="custom") {
      $options['buttonUrl'] = $customButton;
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
    add_settings_field     ('plugin_buttonUrl',     'Print button image', 'echoButtonUrlSetting',     $pluginName, 'plugin_main');
    add_settings_field     ('plugin_buttonplacement',      'Button Placement',   'echoButtonPlacement',      $pluginName, 'plugin_main');
    add_settings_field     ('plugin_gaOption',      'Google analytics',   'echoGASetting',            $pluginName, 'plugin_main');
}

// Add the hooks for print functionality
function addCleanPrintContentTags($content) {
    
    global $optionsName;
    global $blackButtonUrl;
            
    $options        = get_option($optionsName);
    $buttonUrl      = $options['buttonUrl'];
    $nothingChecked = !isset($buttonUrl);
    $removeChecked  =  isset($buttonUrl) && $buttonUrl=="none";
    $ButtonPlacement = $options['ButtonPlacement'];
    $width          = '100%';
        
    if ($nothingChecked) {
        $buttonUrl = $blackButtonUrl;
    }

    if ($removeChecked) {

    } else if ($ButtonPlacement=="tl") {
        $content = sprintf("<a href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='%s'></a><br />%s", $buttonUrl, $content);
    } else if ($ButtonPlacement=="tr") {
        $content = sprintf("<div style='text-align: right;'><a href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='%s'></a></div><br />%s", $buttonUrl, $content);
    } else if($ButtonPlacement=="bl") {
        $content = sprintf("%s<br /><a href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='%s'></a>", $content, $buttonUrl);  
    } else {
        $content = sprintf("%s<br /><div style='text-align: right;'><a href='.' onClick='CleanPrint(); return false' class='button' title='Print page'><img src='%s'></a></div>", $content, $buttonUrl);    
    }
    return $content;
}


// Adds the CleanPrint script tags to the head section
function addCleanPrintScript() {
    global $optionsName;
    global $cleanprintUrl;
    global $publisherKey;
   
    $options              = get_option($optionsName);
    $GASetting            = $options['GASetting'];
    $gravity              = $options['gravity'];

    if (!isset($gravity)) $gravity = "center";
    
        printf( "<script type='text/javascript'>\n");
        printf( "   function CleanPrint() {");
        printf( "   CleanPrintPrintHtml();\n");
        if ($GASetting=="true") {
            printf( "   _gaq.push(['_trackEvent', 'CleanPrint', 'Print']);\n");
        }
        printf( "   }");
        printf( "</script>\n");
    
    printf( "<script language='javascript' type='text/javascript' src='%s?key=%s'></script>\n", $cleanprintUrl, $publisherKey);
        
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