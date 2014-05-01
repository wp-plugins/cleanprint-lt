<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Eco-friendly content output to print, PDF, text, email, Kindle, Google Cloud Print, Box, Google Drive and Dropbox
Version: 3.4.1
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


// Plug-in parameters (do not change these)
$cleanprint_plugin_name       = 'cleanprint-lt';
$cleanprint_plugin_file        = $cleanprint_plugin_name . '/cleanprint.php';
$cleanprint_plugin_attr       = 'plugin';
$cleanprint_print_attr        = 'print';
$cleanprint_options_name      = 'CleanPrintAdminOptions';

// CleanPrint parameters (change these *only* if you know what you're doing)
$cleanprint_base_url          = is_ssl() ? 'https://cache-02.cleanprint.net' : 'http://cache-02.cleanprint.net';
$cleanprint_publisher_key     = 'wpdefault';

// Best not change these (internal-use only)
$cleanprint_loader_url        = $cleanprint_base_url . '/cpf/cleanprint';
$cleanprint_images_base_url   = $cleanprint_base_url . '/media/pfviewer/images';
$cleanprint_btn_helper_url    = $cleanprint_base_url . '/cpf/publisherSignup/js/generateCPFTag.js';
$cleanprint_def_logo_url      = $cleanprint_base_url . '/media/logos/Default.png';
$cleanprint_def_btn_style     = 'Btn_white';
$cleanprint_def_btn_placement = 'tr';
$cleanprint_debug             = false;



// Display the options page
function cleanprint_add_options_page() {
   global $cleanprint_options_name;
   global $cleanprint_plugin_name;
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>CleanPrint Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields     ($cleanprint_options_name); ?>
			<?php do_settings_sections($cleanprint_plugin_name); ?>

			<input name="Submit" type="submit" value="Save Changes" />
		</form>
	</div>
<?php
}


// Outputs a section heading but we do not use it
function cleanprint_add_settings_section() {
?>
    <p>Thanks for installing CleanPrint on your site and helping your users save paper, ink, money and trees!
    Below are a few options to customize CleanPrint and make it your own. You can use your logo and choose
    from a variety of button styles or use your own buttons.  You may also select the location within the page
    where the button(s) are placed.</p>
    
    <p>You may select which page types that the button(s) should appear on.  <!-- You may also exclude specific
    pages by entering their comma separated IDs.  NOTE: The ID is visible in the URL when you navigate to
    that page. --></p>
    
    <p>If you would like to place the button(s) in a custom position please see installation instructions.
    Also, if you choose to use Google Analytics custom event tracking for CleanPrint your site <b>MUST</b>
    have Google Analytics running.</p>
    
    <p>You can also turn off advertising, visit our site and sign up
    <a href="http://www.formatdynamics.com/diypub-adfree/" target="adfree">http://www.formatdynamics.com/diypub-adfree/</a>.</p>
    <?php printf("<tr><td><h2>Logo</h2><hr /></td></tr>");?>
<?php
}


// WP callback for handling the Logo URL (default/custom) option
function cleanprint_add_settings_field_logo_url_() {
    global $cleanprint_options_name;
    global $cleanprint_def_logo_url;
    
	$options        = get_option($cleanprint_options_name);
	$logoUrl        = isset($options['logoUrl']) ? $options['logoUrl'] : null;
    $customChecked  = isset($logoUrl) && $logoUrl!=$cleanprint_def_logo_url;
    $defaultChecked = !$customChecked;

    printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='%s' %s />", $cleanprint_options_name, $cleanprint_def_logo_url, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='custom' %s />", $cleanprint_options_name, $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='text'  id='plugin_logoUrl' name='%s[customLogo]' value='%s' /><br>\n", $cleanprint_options_name, $customChecked ? $logoUrl : "");
	printf( "<td>Logo Preview<br /><div style='background-color:#DDD; border: 1px solid #BBB; padding: 10px; text-align:center;'><img height='40px' src='%s'></div></td>", $customChecked ? $logoUrl : $cleanprint_def_logo_url);
	printf("<tr><td  colspan='3'><h2>Button Styles</h2><hr /></td></tr>");
}


// WP callback for handling the Print Button URL (default/custom) option
function cleanprint_add_settings_field_button_color() {
    global $cleanprint_options_name;
    global $cleanprint_images_base_url;
    global $cleanprint_btn_helper_url;
    global $cleanprint_def_btn_style;
    
	$options     = get_option($cleanprint_options_name);
	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : null;
	
	if(!isset($buttonStyle)) {
        $buttonStyle = $cleanprint_def_btn_style;
    }
    
    printf("<script type='text/javascript' src='%s'></script>", $cleanprint_btn_helper_url);    
    printf("<script type='text/javascript'>function buildButtonSelect() {");
    printf("var select = document.createElement('select');");
    printf("select.setAttribute('id',       'plugin_buttonStyle');");
    printf("select.setAttribute('name',     '%s[buttonStyle]');", $cleanprint_options_name);
    printf("select.setAttribute('onchange', 'changeButtons(this);return false;');");
    printf("var styles = getCPFButtonStyles();");
    printf("for (style in styles) {");
    printf("var label  = styles[style];");
    printf("var option = document.createElement('option');");
    printf("option.setAttribute('value', style);");
    printf("if (style=='%s') option.setAttribute('selected', 'selected');", $buttonStyle);
    printf("option.innerHTML = label;");
    printf("select.appendChild(option);");
    printf("}");
    printf("return select;");
    printf("}");
    
    printf("function changeButtons(select) {");
	printf("var index = select.selectedIndex;");
	printf("var value = select.options[index].value;");
	printf("var cpUrl    = '$cleanprint_images_base_url/CleanPrint' + value + '.png';");
	printf("var pdfUrl   = '$cleanprint_images_base_url/Pdf'        + value + '.png';");
	printf("var emailUrl = '$cleanprint_images_base_url/Email'      + value + '.png';");
	printf("document.getElementById('cpImg')   .src = cpUrl;");
	printf("document.getElementById('pdfImg')  .src = pdfUrl;");
	printf("document.getElementById('emailImg').src = emailUrl;");
	printf("}");
	
	printf("function changeButton(select,button) {");
    printf("var index  = select.selectedIndex;");
    printf("var value  = select.options[index].value;");
    printf("var elem   = document.getElementById(button);");
    printf("if (value=='include') {elem.style.display='inline';}");
    printf("else                  {elem.style.display='none';}");
    printf("}</script>");

    printf("<span id='cpf_button_selector'></span>");
    printf("<script>document.getElementById('cpf_button_selector').appendChild(buildButtonSelect());</script>");
    
	
	$PrintInclude    = isset($options['PrintInclude']) ? $options['PrintInclude'] : null;
    $PDFInclude      = isset($options['PDFInclude'])   ? $options['PDFInclude']   : null;
    $EmailInclude    = isset($options['EmailInclude']) ? $options['EmailInclude'] : null;
    $printChecked    = !isset($PrintInclude) || $PrintInclude=="include";
    $pdfChecked      = !isset($PDFInclude)   || $PDFInclude  =="include";
    $emailChecked    = !isset($EmailInclude) || $EmailInclude=="include";
    
	printf("<td>Button Preview<br /><div id='sampleArea' style='border: 1px solid #BBB; padding: 10px; text-align:center;'>");
	printf("<img id='cpImg'    src='$cleanprint_images_base_url/CleanPrint$buttonStyle.png' style='%s'/>", ($printChecked ? "" : "display:none"));
	printf("<img id='pdfImg'   src='$cleanprint_images_base_url/Pdf$buttonStyle.png'        style='%s'/>", ($pdfChecked   ? "" : "display:none"));
    printf("<img id='emailImg' src='$cleanprint_images_base_url/Email$buttonStyle.png'      style='%s'/>", ($emailChecked ? "" : "display:none"));
	printf("</div></td>");
}


// WP callback for handling button include
function cleanprint_add_settings_field_print_btn() {
    global $cleanprint_options_name;
    
	$options         = get_option($cleanprint_options_name);
	$PrintInclude    = isset($options['PrintInclude']) ? $options['PrintInclude'] : null;
	$printChecked    = !isset($PrintInclude) || $PrintInclude == "include";
	
	printf( "<select id='plugin_PrintInclude' name='%s[PrintInclude]' onchange='changeButton(this,\"cpImg\"); return false;'>", $cleanprint_options_name);
	printf( "<option value='include' %s>Show</option>", ( $printChecked ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Hide</option>", (!$printChecked ?"selected='selected'":""));
	printf( "</select>");
}


// WP callback for handling button include
function cleanprint_add_settings_field_pdf_btn() {
    global $cleanprint_options_name;
    
	$options         = get_option($cleanprint_options_name);
	$PDFInclude      = isset($options['PDFInclude'])   ? $options['PDFInclude']   : null;
    $pdfChecked      = !isset($PDFInclude) || $PDFInclude == "include";
	
	printf( "<select id='plugin_PDFInclude' name='%s[PDFInclude]' onchange='changeButton(this,\"pdfImg\"); return false;'>", $cleanprint_options_name);
	printf( "<option value='include' %s>Show</option>", ( $pdfChecked  ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Hide</option>", (!$pdfChecked ?"selected='selected'":""));
	printf( "</select>");
}


// WP callback for handling button include
function cleanprint_add_settings_field_email_btn() {
    global $cleanprint_options_name;
    
	$options         = get_option($cleanprint_options_name);
	$EmailInclude    = isset($options['EmailInclude']) ? $options['EmailInclude'] : null;
	$emailChecked    = !isset($EmailInclude) || $EmailInclude == "include";
	
	printf( "<select id='plugin_EmailInclude' name='%s[EmailInclude]' onchange='changeButton(this,\"emailImg\"); return false;'>", $cleanprint_options_name);
	printf( "<option value='include' %s>Show</option>", ( $emailChecked  ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Hide</option>", (!$emailChecked  ?"selected='selected'":""));
	printf( "</select>");
}


// WP callback for handling button placement
function cleanprint_add_settings_field_btn_placement() {
    global $cleanprint_options_name;
    global $cleanprint_def_btn_placement;
    
	$options         = get_option($cleanprint_options_name);
	$ButtonPlacement = isset($options['ButtonPlacement']) ? $options['ButtonPlacement'] : null;
	
	if (!isset($ButtonPlacement)) {
	   $ButtonPlacement = $cleanprint_def_btn_placement;
	}
	
	$trChecked  = $ButtonPlacement=="tr";
    $tlChecked  = $ButtonPlacement=="tl";
	$blChecked  = $ButtonPlacement=="bl";
	$brChecked  = $ButtonPlacement=="br";
	
	
    printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='tl' %s />", $cleanprint_options_name, $tlChecked ?"checked='checked'":"");
	printf( "Top Left<br />\n");

	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='tr' %s />", $cleanprint_options_name, $trChecked  ?"checked='checked'":"");
	printf( "Top Right<br />\n");
	
	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='bl' %s />", $cleanprint_options_name, $blChecked  ?"checked='checked'":"");
	printf( "Bottom Left<br />\n");
	
	printf( "<input type='radio' id='plugin_buttonplacement' name='%s[ButtonPlacement]' value='br' %s />", $cleanprint_options_name, $brChecked  ?"checked='checked'":"");
	printf( "Bottom Right<br />\n");
	printf("<tr><td colspan='3'><h2>Display Button(s) on the Following:</h2><hr /></td></tr>");  
}


// WP callback for handling page type
function cleanprint_add_settings_field_homepage() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $homepage    = isset($options['HomepageInclude']) ? $options['HomepageInclude'] : null;
    $isChecked   = !isset($homepage) || $homepage=="include";
    
    printf( "<select id='plugin_homepage' name='%s[HomepageInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_home()</i>");  
}


function cleanprint_add_settings_field_frontpage() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $frontpage   = isset($options['FrontpageInclude']) ? $options['FrontpageInclude'] : null;
    $isChecked   = !isset($frontpage) || $frontpage=="include";
    
    printf( "<select id='plugin_frontpage' name='%s[FrontpageInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_front_page()</i>");
}


function cleanprint_add_settings_field_category() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $category    = isset($options['CategoryInclude']) ? $options['CategoryInclude'] : null;
    $isChecked   = !isset($category) || $category=="include";
    
    printf( "<select id='plugin_category' name='%s[CategoryInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_category()</i>");
}


function cleanprint_add_settings_field_posts() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $posts       = isset($options['PostsInclude']) ? $options['PostsInclude'] : null;
    $isChecked   = !isset($posts) || $posts=="include";
    
    printf( "<select id='plugin_posts' name='%s[PostsInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_single()</i>");
}


function cleanprint_add_settings_field_pages() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $pages       = isset($options['PagesInclude']) ? $options['PagesInclude'] : null;
    $isChecked   = !isset($pages) || $pages=="include";
    
    printf( "<select id='plugin_pages' name='%s[PagesInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_page()</i>");
}


function cleanprint_add_settings_field_tags() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $tags        = isset($options['TagsInclude']) ? $options['TagsInclude'] : null;
    $isChecked   = !isset($tags) || $tags=="include";
    
    printf( "<select id='plugin_tags' name='%s[TagsInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_tag()</i>");
    printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");
}


function cleanprint_add_settings_field_excludes() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $excludes    = isset($options['PagesExcludes']) ? $options['PagesExcludes'] : "";
    
    printf( "<input type='text' id='plugin_excludes' name='%s[PagesExcludes]' value='%s' /><br>\n", $cleanprint_options_name, $excludes);
//  printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");  
}


// WP callback for handling the Google Analytics option
function cleanprint_add_settings_field_ga() {
    global $cleanprint_options_name;
    
	$options         = get_option($cleanprint_options_name);
	$GASetting       = isset($options['GASetting']) ? $options['GASetting'] : null;
	$disabledChecked = !isset($GASetting) || $GASetting=="false";
    $enabledChecked  = !$disabledChecked;
    
    printf( "<input type='radio' id='plugin_gaOption' name='%s[GASetting]' value='true' %s />", $cleanprint_options_name, $enabledChecked?"checked='checked'":"");
	printf( "Enabled<br />\n");

	printf( "<input type='radio' id='plugin_gaOption' name='%s[GASetting]' value='false' %s />", $cleanprint_options_name, $disabledChecked ?"checked='checked'":"");
	printf( "Disabled<br /><br />\n");
}


function cleanprint_add_query_vars($vars) {
	global $cleanprint_plugin_attr;
	global $cleanprint_print_attr;
		
	array_push($vars, $cleanprint_print_attr,$cleanprint_plugin_attr);
    return $vars;
}


// Clean up the DB properties
function cleanprint_sanitize_options($options) {
   global $cleanprint_def_logo_url;
   global $optionsVersion;
   
   // Map the customLogo into logoUrl
   $logoUrl    = isset($options['logoUrl'])    ? $options['logoUrl']    : null;
   $customLogo = isset($options['customLogo']) ? $options['customLogo'] : null;
   if (isset($logoUrl) && isset($customLogo) && $logoUrl!=$cleanprint_def_logo_url) {
      $options['logoUrl'] = $customLogo;            
   }   
   unset($options['customLogo']);
   
   return $options;
}


function cleanprint_is_pagetype() {
    global $page_id;
	global $cleanprint_options_name;

    $options       = get_option($cleanprint_options_name);
    $homepage      = isset($options['HomepageInclude'])  ? $options['HomepageInclude']  : null;
    $frontpage     = isset($options['FrontpageInclude']) ? $options['FrontpageInclude'] : null;
    $category      = isset($options['CategoryInclude'])  ? $options['CategoryInclude']  : null;
    $posts         = isset($options['PostsInclude'])     ? $options['PostsInclude']     : null;
    $pages         = isset($options['PagesInclude'])     ? $options['PagesInclude']     : null;
    $tags          = isset($options['TagsInclude'])      ? $options['TagsInclude']      : null;
/*  $excludes      = isset($options['PagesExcludes'])    ? $options['PagesExcludes']    : null;

    if (isset($excludes) && isset($page_id)) {
       $IDs = explode(",", $excludes);
       $len = count($IDs);
       for ($i=0; $i<$len; $i++) {
          if ($page_id == $IDs[$i]) return false;
       }
    }
*/
    $isHomeChecked = !isset($homepage)  || $homepage =='include';
    $isFrntChecked = !isset($frontpage) || $frontpage=='include';
    $isCatgChecked = !isset($category)  || $category =='include';
    $isPostChecked = !isset($posts)     || $posts    =='include';
    $isPageChecked = !isset($pages)     || $pages    =='include';
    $isTagChecked  = !isset($tags)      || $tags     =='include';
    
    if (is_home()       && $isHomeChecked) return true;
    if (is_front_page() && $isFrntChecked) return true;              
    if (is_category()   && $isCatgChecked) return true;
    if (is_single()     && $isPostChecked) return true;
    if (is_page()       && $isPageChecked) return true;
    if (is_tag()        && $isTagChecked ) return true;
    
    return false;
}

// Add the hooks for print functionality
function cleanprint_add_content($content) {
	global $post;
    global $cleanprint_options_name;
	global $cleanprint_images_base_url;
	global $cleanprint_def_btn_style;
	global $cleanprint_def_btn_placement;
	 	    
	$options         = get_option($cleanprint_options_name);
	$buttonStyle     = isset($options['buttonStyle'])     ? $options['buttonStyle']     : null;
    $ButtonPlacement = isset($options['ButtonPlacement']) ? $options['ButtonPlacement'] : null;
    
    $showPrintBtn    = !isset($options['PrintInclude']) || $options['PrintInclude']=='include';
    $showPdfBtn      = !isset($options['PDFInclude'])   || $options['PDFInclude']  =='include';
    $showEmailBtn    = !isset($options['EmailInclude']) || $options['EmailInclude']=='include';
    $postId          = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : ""; 
    $buttons         = "";
    
    if (!isset($ButtonPlacement)) {
       $ButtonPlacement = $cleanprint_def_btn_placement;
    }
    
    if (cleanprint_is_pagetype()) {
	   if (!isset($buttonStyle)) {
            $buttonStyle = $cleanprint_def_btn_style;
        }

        if ($showPrintBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPrint($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/CleanPrint$buttonStyle.png\" /></a>";
        }

        if ($showPdfBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPDF($postId);return false\" title=\"PDF page\" class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/Pdf$buttonStyle.png\" /></a>";
        }

        if ($showEmailBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanEmail($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/Email$buttonStyle.png\" /></a>";
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


// Adds the CleanPrint print button for use by a shortcode
function cleanprint_add_print_button() {
    global $post;
    global $cleanprint_options_name;
    global $cleanprint_images_base_url;
    global $cleanprint_def_btn_style;
	 	    
    $options     = get_option($cleanprint_options_name);
    $buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : null;
    $postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : ""; 
        
    if (!isset($buttonStyle)) {
        $buttonStyle = $cleanprint_def_btn_style;
    }

    return "<a href=\".\" onClick=\"CleanPrint($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/CleanPrint$buttonStyle.png\" /></a>";
}


// Adds the CleanPrint print button for use by a shortcode
function cleanprint_add_button($atts, $content, $tag) {
    global $post;
    global $cleanprint_options_name;
    global $cleanprint_images_base_url;
    global $cleanprint_def_btn_style;
	 	    
    extract( shortcode_atts( array(
		'print' => 'true',
        'pdf'   => 'false',
        'email' => 'false',
	), $atts ) );
	 	    
    $options     = get_option($cleanprint_options_name);
    $buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : null;
    $postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : "";
    $rtn         = ""; 
        
    if (!isset($buttonStyle)) {
        $buttonStyle = $cleanprint_def_btn_style;
    }

    if ("{$print}"=="true") $rtn .= "<a href=\".\" onClick=\"CleanPrint($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/CleanPrint$buttonStyle.png\" /></a>";
    if ("{$pdf}"  =="true") $rtn .= "<a href=\".\" onClick=\"CleanPDF  ($postId);return false\" title=\"PDF page\"   class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/Pdf$buttonStyle.png\"        /></a>";
    if ("{$email}"=="true") $rtn .= "<a href=\".\" onClick=\"CleanEmail($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$cleanprint_images_base_url/Email$buttonStyle.png\"      /></a>";

    return $rtn;
}


// Adds the CleanPrint script tags to the head section
function cleanprint_wp_head() {
    global $page_id;
    global $cleanprint_options_name;
    global $cleanprint_loader_url;
    global $cleanprint_publisher_key;
	global $cleanprint_def_logo_url;
    global $cleanprint_debug;
   
	$options      = get_option($cleanprint_options_name);
	$GASetting    = isset($options['GASetting']) ? $options['GASetting'] : null;
	$logoUrl      = isset($options['logoUrl'])   ? $options['logoUrl']   : null;

    $showPrintBtn = !isset($options['PrintInclude']) || $options['PrintInclude']=='include';
    $showPdfBtn   = !isset($options['PDFInclude'  ]) || $options['PDFInclude'  ]=='include';
    $showEmailBtn = !isset($options['EmailInclude']) || $options['EmailInclude']=='include';

    if (cleanprint_is_pagetype() == false) {
       // Disabled page type
       return;
    }
/*
    if (!($showPrintBtn || $showPdfBtn || $showEmailBtn)) {
       // All the buttons are excluded
       return;
    }
*/
    if ($cleanprint_debug) {
		printf("\n\n\n<!-- CleanPrint Debug\n\t\t%s\n\t\tpage_id:%s, home:%d, front:%d, category:%d, single:%d, page:%d, tag:%d\n-->\n\n\n",
					               http_build_query($options,"","\n\t\t"), $page_id, is_home(), is_front_page(), is_category(), is_single(), is_page(), is_tag());
	}
		
    printf( "<script id='cpf_wp' type='text/javascript'>\n");
    printf( "   function CleanPrint(postId) {\n");
    printf( "   	CleanPrintPrintHtml(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Print']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanEmail(postId) {\n");
    printf( "   	CleanPrintSendEmail(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Email']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanPDF(postId) {\n");
    printf( "   	CleanPrintGeneratePdf(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'PDF']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "</script>\n");
	
	printf( "<script id='cpf_loader' type='text/javascript' src='%s?key=%s&logo=%s'></script>\n", 
	           $cleanprint_loader_url, urlencode($cleanprint_publisher_key), urlencode($logoUrl));
}



// Add the Settings menu link to the plugin page
function cleanprint_add_action_links($links, $file) {
	global $cleanprint_plugin_name;
    global $cleanprint_plugin_file;
    
    if ($file == $cleanprint_plugin_file) {
		$links[] = sprintf("<a href='options-general.php?page=%s'>Settings</a>", $cleanprint_plugin_name);
	}
	return $links;
}


// Activate CleanPrint, migrate any old options here
function cleanprint_activate() {
   // cannot use the global, chicken/egg problem
   $options        = get_option('CleanPrintAdminOptions');
   $optionsVersion = '2.1';
   
   if (isset($options)) {
      $version  = isset($options['version']) ? $options['version'] : null;   
   
      // Don't know what version we looking at (0.97, 1.0.0, 1.0.1, or 2.0.0) so there is only
      // so much we can do.  The biggest issue of the logoUrl which was hijacked in 2.0.0 and
      // now we cannot tell it use apart from earlier releases.
      if (!isset($version)) {      
         $logoUrl = isset($options['logoUrl']) ? $options['logoUrl'] : null;
         // Get rid of the old CP3/WP leader board header
         if (isset($logoUrl) && $logoUrl == 'http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg') {      
            unset($options['logoUrl']);
         }
         
         $buttonColor = isset($options['buttonColor']) ? $options['buttonColor'] : null;
         if (isset($buttonColor)) {
            $options['buttonStyle'] = 'Btn_' . $buttonColor;
         }
   
         // Get rid of the old options
         unset($options['printSpecId']);
         unset($options['activationKey']);
         unset($options['buttonUrl']);
         unset($options['customButton']);
         unset($options['customLogo']);
         unset($options['buttonColor']);
      }
   
      // Set the version and commit the changes
      $options['version'] = $optionsVersion;      
      update_option('CleanPrintAdminOptions', $options);
   }
}


// Remove the CleanPrint options from the database
function cleanprint_uninstall() {
    // cannot use the global, chicken/egg problem
	delete_option('CleanPrintAdminOptions');
}


// WP callback for initializing the options menu
function cleanprint_admin_init() {
    global $cleanprint_plugin_name;
    global $cleanprint_plugin_file;
    global $cleanprint_options_name;
    
    register_setting       ($cleanprint_options_name, $cleanprint_options_name, 'cleanprint_sanitize_options');
    register_uninstall_hook($cleanprint_plugin_file, 'cleanprint_uninstall');

    add_settings_section   ('plugin_main', '', 'cleanprint_add_settings_section', $cleanprint_plugin_name);
    add_settings_field     ('plugin_logoUrl',         '<strong>Image:</strong>',                     'cleanprint_add_settings_field_logo_url_',     $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_buttonStyle',     '<strong>Size / Color:</strong>',              'cleanprint_add_settings_field_button_color',  $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_PrintInclude',    '<strong>Print Button:</strong>',              'cleanprint_add_settings_field_print_btn',     $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_PDFInclude',      '<strong>PDF Button:</strong>',                'cleanprint_add_settings_field_pdf_btn',       $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_EmailInclude',    '<strong>Email Button:</strong>',              'cleanprint_add_settings_field_email_btn',     $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_buttonplacement', '<strong>Page Location:</strong>',             'cleanprint_add_settings_field_btn_placement', $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_homepage',        '<strong>Home Page:</strong>',                 'cleanprint_add_settings_field_homepage',      $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_frontpage',       '<strong>Front Page:</strong>',                'cleanprint_add_settings_field_frontpage',     $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_category',        '<strong>Categories:</strong>',                'cleanprint_add_settings_field_category',      $cleanprint_plugin_name, 'plugin_main');    
    add_settings_field     ('plugin_posts',           '<strong>Posts:</strong>',                     'cleanprint_add_settings_field_posts',         $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_pages',           '<strong>Pages:</strong>',                     'cleanprint_add_settings_field_pages',         $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_tags',            '<strong>Tags:</strong>',                      'cleanprint_add_settings_field_tags',          $cleanprint_plugin_name, 'plugin_main');
//  add_settings_field     ('plugin_excludes',        '<strong>Excluded Page IDs:</strong>',         'cleanprint_add_settings_field_excludes',      $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_gaOption',        '<strong>CleanPrint Event Tracking:</strong>', 'cleanprint_add_settings_field_ga',            $cleanprint_plugin_name, 'plugin_main');
}


// WP callback for launching the options menu
function cleanprint_admin_menu() {
   global $cleanprint_plugin_name;
   add_options_page('CleanPrint Settings', 'CleanPrint', 'manage_options', $cleanprint_plugin_name, 'cleanprint_add_options_page');
}


// Activate
register_activation_hook(__FILE__, 'cleanprint_activate');

// Actions
add_action('admin_init',          'cleanprint_admin_init');
add_action('admin_menu',          'cleanprint_admin_menu');
add_action('wp_head',             'cleanprint_wp_head', 1);

// Filters
add_filter('plugin_action_links', 'cleanprint_add_action_links', - 10, 2);
add_filter('the_content',         'cleanprint_add_content');
add_filter('query_vars',          'cleanprint_add_query_vars');

?>