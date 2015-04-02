<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Eco-friendly content output to print, PDF, text, email, Kindle, Google Cloud Print, Box, Google Drive and Dropbox
Version: 3.4.6
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

// Best not change these (internal-use only)
$cleanprint_loader_url        = $cleanprint_base_url . '/cpf/cleanprint?polite=no&key=wpdefault';
$cleanprint_btn_helper_url    = $cleanprint_base_url . '/cpf/publisherSignup/js/generateCPFTag.js';
$cleanprint_def_btn_style     = 'Btn_white';
$cleanprint_def_btn_placement = 'tr';
$cleanprint_post_id_format    = 'post-%s';



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
   <p>Thanks for installing CleanPrint on your site! Below are a few options to customize CleanPrint and
    make it your own.</p>
    
    <ol>
    <li>You can use our logo or your own<br>- use a <i>http-style</i> image URL with the image size no larger than 200 x 40.</li>
    
    <li>You choose from a variety of button styles or use your own custom buttons<br>
        - please see installation instructions for custom images.</li> 
    
    <li>You may also select the location where the buttons are placed or choose a custom position<br>
        - please see installation instructions for custom locations.</li>
    
    <li>You may select which page types that the buttons appear on.</li>     
    </ol>
    
    <p>NOTE: If you choose to use Google Analytics custom event tracking for CleanPrint your site <b>MUST</b>
    have Google Analytics running.</p>
    </ul>    <?php printf("<tr><td><h2>Logo</h2><hr /></td></tr>");?>
<?php
}


// WP callback for handling the Logo URL (default/custom) option
function cleanprint_add_settings_field_logo_url_() {
    global $cleanprint_options_name;
    
	$options        = get_option($cleanprint_options_name);
	$logoUrl        = isset($options['logoUrl']) ? $options['logoUrl'] : null;
    $defLogoUrl     = plugins_url('/CleanPrintSave.png',__FILE__);
    $customChecked  = isset($logoUrl) && $logoUrl!=$defLogoUrl;
    $defaultChecked = !$customChecked;

    printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='%s' %s />", $cleanprint_options_name, $defLogoUrl, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='custom' %s />", $cleanprint_options_name, $customChecked ?"checked='checked'":"");
	printf( "Custom (fully-qualified URL):");
	printf( "<input type='text'  id='plugin_logoUrl' name='%s[customLogo]' value='%s' /><br>\n", $cleanprint_options_name, $customChecked ? $logoUrl : "");
	printf( "<td>Logo Preview<br /><div style='background-color:#DDD; border: 1px solid #BBB; padding: 10px; text-align:center;'><img height='40px' src='%s'></div></td>", $customChecked ? $logoUrl : $defLogoUrl);
	printf("<tr><td  colspan='3'><h2>Button Styles/Locations</h2><hr /></td></tr>");
}


// WP callback for handling the Print Button URL (default/custom) option
function cleanprint_add_settings_field_button_color() {
    global $cleanprint_options_name;
    global $cleanprint_btn_helper_url;
    global $cleanprint_def_btn_style;
    
	$options     = get_option($cleanprint_options_name);
	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : $cleanprint_def_btn_style;
	$imagesUrl   = plugins_url("/images",__FILE__);
    
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
	printf("var cpUrl    = '$imagesUrl/CleanPrint' + value + '.png';");
	printf("var pdfUrl   = '$imagesUrl/Pdf'        + value + '.png';");
	printf("var emailUrl = '$imagesUrl/Email'      + value + '.png';");
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
	printf("<img id='cpImg'    src='$imagesUrl/CleanPrint$buttonStyle.png' style='%s'/>", ($printChecked ? "" : "display:none"));
	printf("<img id='pdfImg'   src='$imagesUrl/Pdf$buttonStyle.png'        style='%s'/>", ($pdfChecked   ? "" : "display:none"));
    printf("<img id='emailImg' src='$imagesUrl/Email$buttonStyle.png'      style='%s'/>", ($emailChecked ? "" : "display:none"));
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
	$ButtonPlacement = isset($options['ButtonPlacement']) ? $options['ButtonPlacement'] : $cleanprint_def_btn_placement;
	
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
	printf("<tr><td colspan='3'><h2>Page Types:</h2><hr /></td></tr>");  
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
}


function cleanprint_add_settings_field_taxs() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $taxs        = isset($options['TaxsInclude']) ? $options['TaxsInclude'] : null;
    $isChecked   = !isset($taxs) || $taxs=="include";
    
    printf( "<select id='plugin_taxs' name='%s[TaxsInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
}


function cleanprint_add_settings_field_others() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $others      = isset($options['OthersInclude']) ? $options['OthersInclude'] : null;
    $isChecked   = isset($others) && $others=="include";
    
    printf( "<select id='plugin_others' name='%s[OthersInclude]'>", $cleanprint_options_name);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
}


function cleanprint_add_settings_field_excludes() {
    global $cleanprint_options_name;
    
    $options     = get_option($cleanprint_options_name);
    $excludes    = isset($options['PagesExcludes']) ? $options['PagesExcludes'] : "";
    
    printf( "<input type='text' id='plugin_excludes' name='%s[PagesExcludes]' value='%s' /><br>\n", $cleanprint_options_name, $excludes);
    printf( "<i>(comma separated list of post IDs)</i>");
    printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");  
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
	printf("<tr><td colspan='3'><h2>Internal Use Only</h2><hr /></td></tr>");
}


function cleanprint_add_settings_field_debug() {
    global $cleanprint_options_name;
    
	$options      = get_option($cleanprint_options_name);
	$debug        = isset($options['Debug']) ? $options['Debug'] : null;
	$debugEnabled = isset($debug) && $debug=="true";

    printf( "<input type='checkbox' id='plugin_debug' name='%s[Debug]' value='true' %s/>", $cleanprint_options_name, $debugEnabled  ?"checked='checked'":"");
}


function cleanprint_add_query_vars($vars) {
	global $cleanprint_plugin_attr;
	global $cleanprint_print_attr;
		
	array_push($vars, $cleanprint_print_attr,$cleanprint_plugin_attr);
    return $vars;
}


// Clean up the DB properties
function cleanprint_sanitize_options($options) {
   global $optionsVersion;
   
   // Map the customLogo into logoUrl
   $logoUrl    = isset($options['logoUrl'])    ? $options['logoUrl']    : null;
   $customLogo = isset($options['customLogo']) ? $options['customLogo'] : null;
   $defLogoUrl = plugins_url('/CleanPrintSave.png',__FILE__);
      
   if (isset($logoUrl) && isset($customLogo) && $logoUrl!=$defLogoUrl) {
      $options['logoUrl'] = $customLogo;            
   }   
   unset($options['customLogo']);
   
   return $options;
}


function cleanprint_is_pagetype() {
    global $post;
	global $cleanprint_options_name;

    $options       = get_option($cleanprint_options_name);
    $homepage      = isset($options['HomepageInclude'])  ? $options['HomepageInclude']  : null;
    $frontpage     = isset($options['FrontpageInclude']) ? $options['FrontpageInclude'] : null;
    $category      = isset($options['CategoryInclude'])  ? $options['CategoryInclude']  : null;
    $posts         = isset($options['PostsInclude'])     ? $options['PostsInclude']     : null;
    $pages         = isset($options['PagesInclude'])     ? $options['PagesInclude']     : null;
    $tags          = isset($options['TagsInclude'])      ? $options['TagsInclude']      : null;
    $taxs          = isset($options['TaxsInclude'     ]) ? $options['TaxsInclude'     ] : null;
    $others        = isset($options['OthersInclude'   ]) ? $options['OthersInclude'   ] : null;
	$excludes      = isset($options['PagesExcludes'])    ? $options['PagesExcludes']    : null;

    if (isset($excludes) && isset($post) && isset($post->ID)) {
	   $ids = explode(",", $excludes);
       foreach ($ids as $id) {
          if ("$post->ID" === trim($id)) {
             return false;
          }
       }
    }

    $isHomeChecked  = !isset($homepage)  || $homepage =='include';
    $isFrntChecked  = !isset($frontpage) || $frontpage=='include';
    $isCatgChecked  = !isset($category)  || $category =='include';
    $isPostChecked  = !isset($posts)     || $posts    =='include';
    $isPageChecked  = !isset($pages)     || $pages    =='include';
    $isTagChecked   = !isset($tags)      || $tags     =='include';
    $isTaxChecked   = !isset($taxs)      || $taxs     =='include';
    $isOtherChecked =  isset($others)    && $others   =='include';
    
    $isOther        = !is_home() && !is_front_page() && !is_category() && !is_single() && !is_page() && !is_tag() && !is_tax();
    
    if ($isOther        && $isOtherChecked) return true;
    if (is_home()       && $isHomeChecked ) return true;
    if (is_front_page() && $isFrntChecked ) return true;
    if (is_category()   && $isCatgChecked ) return true;
    if (is_single()     && $isPostChecked ) return true;
    if (is_page()       && $isPageChecked ) return true;
    if (is_tag()        && $isTagChecked  ) return true;
    if (is_tax()        && $isTaxChecked  ) return true;

    return false;
}

// Add the hooks for print functionality
function cleanprint_add_content($content) {
    global $post;
    global $cleanprint_options_name;
	global $cleanprint_def_btn_style;
	global $cleanprint_def_btn_placement;
	global $cleanprint_post_id_format;
	 	    
	$options         = get_option($cleanprint_options_name);
	$buttonStyle     = isset($options['buttonStyle'])     ? $options['buttonStyle']     : $cleanprint_def_btn_style;
    $ButtonPlacement = isset($options['ButtonPlacement']) ? $options['ButtonPlacement'] : $cleanprint_def_btn_placement;
    $imagesUrl       = plugins_url("/images",__FILE__);
    $postId          = isset($post) && isset($post->ID) ? sprintf("'$cleanprint_post_id_format'",$post->ID) : null; 
    
    
    $showPrintBtn    = !isset($options['PrintInclude']) || $options['PrintInclude']=='include';
    $showPdfBtn      = !isset($options['PDFInclude'])   || $options['PDFInclude']  =='include';
    $showEmailBtn    = !isset($options['EmailInclude']) || $options['EmailInclude']=='include';
    $buttons         = "";

    if (cleanprint_is_pagetype()) {
		if ($showPrintBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCpCleanPrintPrintHtml($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" alt=\"Print page\"/></a>";
        }

        if ($showPdfBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCpCleanPrintGeneratePdf($postId);return false\" title=\"PDF page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Pdf$buttonStyle.png\" alt=\"PDF page\"/></a>";
        }

        if ($showEmailBtn) {
            $buttons .= "<a href=\".\" onClick=\"WpCpCleanPrintSendEmail($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Email$buttonStyle.png\" alt=\"Email page\"/></a>";
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
    global $cleanprint_def_btn_style;
    global $cleanprint_post_id_format;

	if (cleanprint_is_pagetype()) {	 	    
    	$options     = get_option($cleanprint_options_name);
    	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : $cleanprint_def_btn_style;
        $imagesUrl   = plugins_url("/images",__FILE__);
        $postId      = isset($post) && isset($post->ID) ? sprintf("'$cleanprint_post_id_format'",$post->ID) : null; 
        
    	return "<a href=\".\" onClick=\"WpCpCleanPrintPrintHtml($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" alt=\"Print page\"/></a>";
	}
}


// Adds the CleanPrint print button for use by a shortcode
function cleanprint_add_button($atts, $content, $tag) {
	global $post;
    global $cleanprint_options_name;
    global $cleanprint_def_btn_style;
    global $cleanprint_post_id_format;
	 	    
    extract( shortcode_atts( array(
		'print' => 'true',
        'pdf'   => 'false',
        'email' => 'false',
	), $atts ) );
	 	    
	if (cleanprint_is_pagetype()) {
    	$options     = get_option($cleanprint_options_name);
    	$buttonStyle = isset($options['buttonStyle']) ? $options['buttonStyle'] : $cleanprint_def_btn_style;
    	$imagesUrl   = plugins_url("/images",__FILE__);
    	$postId      = isset($post) && isset($post->ID) ? sprintf("'$cleanprint_post_id_format'",$post->ID) : null;
    	$rtn         = ""; 
        
    	if ("{$print}"=="true") $rtn .= "<a href=\".\" onClick=\"WpCpCleanPrintPrintHtml  ($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" alt=\"Print page\"/></a>";
    	if ("{$pdf}"  =="true") $rtn .= "<a href=\".\" onClick=\"WpCpCleanPrintGeneratePdf($postId);return false\" title=\"PDF page\"   class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Pdf$buttonStyle.png\"        alt=\"PDF page\"/></a>";
    	if ("{$email}"=="true") $rtn .= "<a href=\".\" onClick=\"WpCpCleanPrintSendEmail  ($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Email$buttonStyle.png\"      alt=\"Email page\"/></a>";

    	return $rtn;
	}
}


// Adds the CleanPrint script tags to the head section
function cleanprint_wp_head() {
	global $post;
    global $page_id;
    global $cleanprint_options_name;
    global $cleanprint_loader_url;
    global $cleanprint_debug;
   
	$options      = get_option($cleanprint_options_name);
	$GASetting    = isset($options['GASetting']) ? $options['GASetting'] : null;
	$logoUrl      = isset($options['logoUrl'])   ? $options['logoUrl']   : null;
	$debug        = isset($options['Debug']) ? $options['Debug'] : null;
	$debugEnabled = isset($debug) && $debug=="true";
	
    $showPrintBtn = !isset($options['PrintInclude']) || $options['PrintInclude']=='include';
    $showPdfBtn   = !isset($options['PDFInclude'  ]) || $options['PDFInclude'  ]=='include';
    $showEmailBtn = !isset($options['EmailInclude']) || $options['EmailInclude']=='include';

    if ($debugEnabled) {
		printf("\n\n\n<!--\n\tCleanPrint Debug\n\t\t%s\n\t\tpage_id:$page_id, post->ID:$post->ID, is_home:%d, is_front_page:%d, is_category:%d, is_single:%d, is_page:%d, is_tag:%d, is_tax:%d\n-->\n\n",
					               http_build_query($options,"","\n\t\t"), is_home(), is_front_page(), is_category(), is_single(), is_page(), is_tag(), is_tax());
	}
		
    printf( "<script id='cpf_wp_cp' type='text/javascript'>\n");
    printf( "   function WpCpCleanPrintPrintHtml(postId) {\n");
    printf( "   	CleanPrintPrintHtml(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Print']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function WpCpCleanPrintSendEmail(postId) {\n");
    printf( "   	CleanPrintSendEmail(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Email']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function WpCpCleanPrintGeneratePdf(postId) {\n");
    printf( "   	CleanPrintGeneratePdf(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'PDF']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "</script>\n");
	
	$loader = $cleanprint_loader_url;
	if ($logoUrl) $loader = "$loader&logo=" . urlencode($logoUrl);
	printf( "<script id='cpf_loader' type='text/javascript' src='%s'></script>\n", $loader);
}



// Add the Settings menu link to the plugin page
function cleanprint_add_action_links($links, $file) {
	global $cleanprint_plugin_name;
    global $cleanprint_plugin_file;
    
    if ($file == $cleanprint_plugin_file) {
		$links[] = "<a href='options-general.php?page=$cleanprint_plugin_name'>Settings</a>";
	}
	return $links;
}

function cleanprint_add_meta_links($links, $file) {
	global $cleanprint_plugin_name;
    global $cleanprint_plugin_file;
    
    if ($file == $cleanprint_plugin_file) {
        $links[] = "<a href='http://wordpress.org/plugins/$cleanprint_plugin_name/faq/'>FAQ</a>";
        $links[] = "<a href='http://wordpress.org/support/plugin/$cleanprint_plugin_name'>Support</a>";
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
   
      // Unset the logoUrl if we have the older default URL      
      $logoUrl = isset($options['logoUrl']) ? $options['logoUrl'] : null;      
      if (isset($logoUrl)) {
		if ($logoUrl=="http://cache-02.cleanprint.net/media/logos/Default.png" || $logoUrl=="http://cache-02.cleanprint.net/media/logos/CleanSave.png") {
			unset($options['logoUrl']); // Not sure this is working but its getting called
		}
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
	add_settings_field     ('plugin_taxs',            '<strong>Taxonies:</strong>',                  'cleanprint_add_settings_field_taxs',          $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_others',          '<strong>Others:</strong>',                    'cleanprint_add_settings_field_others',        $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_excludes',        '<strong>Excluded IDs:</strong>',              'cleanprint_add_settings_field_excludes',      $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_gaOption',        '<strong>CleanPrint Event Tracking:</strong>', 'cleanprint_add_settings_field_ga',            $cleanprint_plugin_name, 'plugin_main');
    add_settings_field     ('plugin_debug',           'debug',                                       'cleanprint_add_settings_field_debug',         $cleanprint_plugin_name, 'plugin_main');
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
add_filter('plugin_action_links', 'cleanprint_add_action_links', 10, 2);
add_filter('plugin_row_meta',     'cleanprint_add_meta_links',   10, 2);
add_filter('the_content',         'cleanprint_add_content');
add_filter('query_vars',          'cleanprint_add_query_vars');

// Shortcodes
add_shortcode('cleanprint_button',       'cleanprint_add_button');
add_shortcode('cleanprint_print_button', 'cleanprint_add_print_button');

?>