<?php
/*
Plugin Name: CleanPrint
Plugin URI: http://www.formatdynamics.com
Description: Eco-friendly content output to print, PDF, text, email, Box.net, Google Docs, Google Drive, Google Cloud Print and Dropbox
Version: 3.2.5
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if( !class_exists( 'WP_Http' ) ) 
   include_once( ABSPATH . WPINC. '/class-http.php' );


// Plug-in parameters (do not change these)
$pluginName             = 'cleanprint-lt';
$pluginFile             = $pluginName . '/cleanprint.php';
$pluginAttr             = 'plugin';
$printAttr              = 'print';
$optionsName            = 'CleanPrintAdminOptions';

// CleanPrint parameters (change these *only* if you know what you're doing)
$baseUrl                = 'http://cache-02.cleanprint.net';
$publisherKey           = 'wpdefault';

// Best not change these (internal-use only)
$cleanprintUrl          = $baseUrl . '/cpf/cleanprint';
$imagesUrl              = $baseUrl . '/media/pfviewer/images';
$buttonHelperUrl        = $baseUrl . '/cpf/publisherSignup/js/generateCPFTag.js';
$defaultLogoUrl         = $baseUrl . '/media/logos/Default.png';
$defaultButtonStyle     = 'Btn_white';
$defaultButtonPlacement = 'tr';
$cleanprintDebug        = false;



// Display the options page
function cleanprint_add_options_page() {
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
    global $optionsName;
    global $defaultLogoUrl;
    
	$options        = get_option($optionsName);
	$logoUrl        = $options['logoUrl'];
    $customChecked  = isset($logoUrl) && $logoUrl!=$defaultLogoUrl;
    $defaultChecked = !$customChecked;

    printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='%s' %s />", $optionsName, $defaultLogoUrl, $defaultChecked?"checked='checked'":"");
	printf( "Default<br />\n");

	printf( "<input type='radio' id='plugin_logoUrl' name='%s[logoUrl]' value='custom' %s />", $optionsName, $customChecked ?"checked='checked'":"");
	printf( "Custom:");
	printf( "<input type='text'  id='plugin_logoUrl' name='%s[customLogo]' value='%s' /><br>\n", $optionsName, $customChecked ? $logoUrl : "");
	printf( "<td>Logo Preview<br /><div style='background-color:#DDD; border: 1px solid #BBB; padding: 10px; text-align:center;'><img height='40px' src='%s'></div></td>", $customChecked ? $logoUrl : $defaultLogoUrl);
	printf("<tr><td  colspan='3'><h2>Button Styles</h2><hr /></td></tr>");
}


// WP callback for handling the Print Button URL (default/custom) option
function cleanprint_add_settings_field_button_color() {
    global $optionsName;
    global $imagesUrl;
    global $buttonHelperUrl;
    global $defaultButtonStyle;
    
	$options     = get_option($optionsName);
	$buttonStyle = $options['buttonStyle'];
	
	if(!isset($buttonStyle)) {
        $buttonStyle = $defaultButtonStyle;
    }
    
    printf("<script type='text/javascript' src='%s'></script>", $buttonHelperUrl);    
    printf("<script type='text/javascript'>function buildButtonSelect() {");
    printf("var select = document.createElement('select');");
    printf("select.setAttribute('id',       'plugin_buttonStyle');");
    printf("select.setAttribute('name',     '%s[buttonStyle]');", $optionsName);
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
    
	
	$PrintInclude    = $options['PrintInclude'];
    $PDFInclude      = $options['PDFInclude'];
    $EmailInclude    = $options['EmailInclude'];
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
    global $optionsName;
    
	$options         = get_option($optionsName);
	$PrintInclude    = $options['PrintInclude'];
	$printChecked    = !isset($PrintInclude) || $PrintInclude == "include";
	
	printf( "<select id='plugin_PrintInclude' name='%s[PrintInclude]' onchange='changeButton(this,\"cpImg\"); return false;'>", $optionsName);
	printf( "<option value='include' %s>Show</option>", ( $printChecked ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Hide</option>", (!$printChecked ?"selected='selected'":""));
	printf( "</select>");
}


// WP callback for handling button include
function cleanprint_add_settings_field_pdf_btn() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$PDFInclude      = $options['PDFInclude'];
    $pdfChecked      = !isset($PDFInclude) || $PDFInclude == "include";
	
	printf( "<select id='plugin_PDFInclude' name='%s[PDFInclude]' onchange='changeButton(this,\"pdfImg\"); return false;'>", $optionsName);
	printf( "<option value='include' %s>Show</option>", ( $pdfChecked  ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Hide</option>", (!$pdfChecked ?"selected='selected'":""));
	printf( "</select>");
}


// WP callback for handling button include
function cleanprint_add_settings_field_email_btn() {
    global $optionsName;
    
	$options         = get_option($optionsName);
	$EmailInclude    = $options['EmailInclude'];
	$emailChecked    = !isset($EmailInclude) || $EmailInclude == "include";
	
	printf( "<select id='plugin_EmailInclude' name='%s[EmailInclude]' onchange='changeButton(this,\"emailImg\"); return false;'>", $optionsName);
	printf( "<option value='include' %s>Show</option>", ( $emailChecked  ?"selected='selected'":""));
	printf( "<option value='exclude' %s>Hide</option>", (!$emailChecked  ?"selected='selected'":""));
	printf( "</select>");
}


// WP callback for handling button placement
function cleanprint_add_settings_field_btn_placement() {
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
function cleanprint_add_settings_field_homepage() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $homepage    = $options['HomepageInclude'];
    $isChecked   = $homepage=="include" || !isset($homepage);
    
    printf( "<select id='plugin_homepage' name='%s[HomepageInclude]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_home()</i>");  
}


function cleanprint_add_settings_field_frontpage() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $frontpage   = $options['FrontpageInclude'];
    $isChecked   = $frontpage=="include" || !isset($frontpage);
    
    printf( "<select id='plugin_frontpage' name='%s[FrontpageInclude]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_front_page()</i>");
}


function cleanprint_add_settings_field_category() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $category    = $options['CategoryInclude'];
    $isChecked   = $category=="include" || !isset($category);
    
    printf( "<select id='plugin_category' name='%s[CategoryInclude]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_category()</i>");
}


function cleanprint_add_settings_field_posts() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $posts       = $options['PostsInclude'];
    $isChecked   = $posts=="include" || !isset($posts);
    
    printf( "<select id='plugin_posts' name='%s[PostsInclude]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_single()</i>");
}


function cleanprint_add_settings_field_pages() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $pages       = $options['PagesInclude'];
    $isChecked   = $pages=="include" || !isset($pages);
    
    printf( "<select id='plugin_pages' name='%s[PagesInclude]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_page()</i>");
}


function cleanprint_add_settings_field_tags() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $tags        = $options['TagsInclude'];
    $isChecked   = $tags=="include" || !isset($tags);
    
    printf( "<select id='plugin_tags' name='%s[TagsInclude]'>", $optionsName);
    printf( "<option value='include' %s>Include</option>", ( $isChecked ?"selected='selected'":""));
    printf( "<option value='exclude' %s>Exclude</option>", (!$isChecked ?"selected='selected'":""));
    printf( "</select>");
    printf( "<i> - i.e. is_tag()</i>");
    printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");
}


function cleanprint_add_settings_field_excludes() {
    global $optionsName;
    
    $options     = get_option($optionsName);
    $excludes    = $options['PagesExcludes'];
    
    printf( "<input type='text' id='plugin_excludes' name='%s[PagesExcludes]' value='%s' /><br>\n", $optionsName, $excludes);
//  printf("<tr><td colspan='3'><h2>Google Analytics</h2><hr /></td></tr>");  
}


// WP callback for handling the Google Analytics option
function cleanprint_add_settings_field_ga() {
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


function cleanprint_add_query_vars($vars) {
	global $pluginAttr;
	global $printAttr;
		
	array_push($vars, $printAttr,$pluginAttr);
    return $vars;
}


// Clean up the DB properties
function cleanprint_sanitize_options($options) {
   global $defaultLogoUrl;
   global $optionsVersion;
   
   // Map the customLogo into logoUrl
   $logoUrl    = $options['logoUrl'];
   $customLogo = $options['customLogo'];
   if (isset($logoUrl) && isset($customLogo) && $logoUrl!=$defaultLogoUrl) {
      $options['logoUrl'] = $customLogo;            
   }   
   unset($options['customLogo']);
   
   return $options;
}


function cleanprint_is_pagetype() {
    global $page_id;
	global $optionsName;

    $options       = get_option($optionsName);
    $homepage      = $options['HomepageInclude'];
    $frontpage     = $options['FrontpageInclude'];
    $category      = $options['CategoryInclude'];
    $posts         = $options['PostsInclude'];
    $pages         = $options['PagesInclude'];
    $tags          = $options['TagsInclude'];
    $excludes      = $options['PagesExcludes'];

/*
    if (isset($excludes) && isset($page_id)) {
       $IDs = explode(",", $excludes);
       $len = count($IDs);
       for ($i=0; $i<$len; $i++) {
          if ($page_id == $IDs[$i]) return false;
       }
    }
*/
    $isHomeChecked = $homepage =='include' || !isset($homepage);
    $isFrntChecked = $frontpage=='include' || !isset($frontpage);
    $isCatgChecked = $category =='include' || !isset($category);
    $isPostChecked = $posts    =='include' || !isset($posts);
    $isPageChecked = $pages    =='include' || !isset($pages);
    $isTagChecked  = $tags     =='include' || !isset($tags);
    
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
    global $optionsName;
	global $imagesUrl;
	global $defaultButtonStyle;
	global $defaultButtonPlacement;
	 	    
	$options         = get_option($optionsName);
	$buttonStyle     = $options['buttonStyle'];
    $ButtonPlacement = $options['ButtonPlacement'];
    
    $showPrintBtn    = $options['PrintInclude']=='include' || !isset($options['PrintInclude']);
    $showPdfBtn      = $options['PDFInclude']  =='include' || !isset($options['PDFInclude']);
    $showEmailBtn    = $options['EmailInclude']=='include' || !isset($options['EmailInclude']);
    $postId          = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : ""; 
    
    if (!isset($ButtonPlacement)) {
       $ButtonPlacement = $defaultButtonPlacement;
    }
    
    if (cleanprint_is_pagetype()) {
	   if (!isset($buttonStyle)) {
            $buttonStyle = $defaultButtonStyle;
        }

        if ($showPrintBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPrint4WP_Print($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" /></a>";
        }

        if ($showPdfBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPrint4WP_PDF($postId);return false\" title=\"PDF page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Pdf$buttonStyle.png\" /></a>";
        }

        if ($showEmailBtn) {
            $buttons .= "<a href=\".\" onClick=\"CleanPrint4WP_Email($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Email$buttonStyle.png\" /></a>";
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
    global $optionsName;
    global $imagesUrl;
    global $defaultButtonStyle;
	 	    
    $options     = get_option($optionsName);
    $buttonStyle = $options['buttonStyle'];
    $postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : ""; 
        
    if (!isset($buttonStyle)) {
        $buttonStyle = $defaultButtonStyle;
    }

    return "<a href=\".\" onClick=\"CleanPrint4WP_Print($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" /></a>";
}


// Adds the CleanPrint print button for use by a shortcode
function cleanprint_add_button($atts, $content, $tag) {
    global $post;
    global $optionsName;
    global $imagesUrl;
    global $defaultButtonStyle;
	 	    
    extract( shortcode_atts( array(
		'print' => 'true',
        'pdf'   => 'false',
        'email' => 'false',
	), $atts ) );
	 	    
    $options     = get_option($optionsName);
    $buttonStyle = $options['buttonStyle'];
    $postId      = isset($post) && isset($post->ID) ? sprintf("'post-%s'", $post->ID) : "";
    $rtn         = ""; 
        
    if (!isset($buttonStyle)) {
        $buttonStyle = $defaultButtonStyle;
    }

    if ("{$print}"=="true") $rtn .= "<a href=\".\" onClick=\"CleanPrint4WP_Print($postId);return false\" title=\"Print page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/CleanPrint$buttonStyle.png\" /></a>";
    if ("{$pdf}"  =="true") $rtn .= "<a href=\".\" onClick=\"CleanPrint4WP_PDF  ($postId);return false\" title=\"PDF page\"   class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Pdf$buttonStyle.png\"        /></a>";
    if ("{$email}"=="true") $rtn .= "<a href=\".\" onClick=\"CleanPrint4WP_Email($postId);return false\" title=\"Email page\" class=\"cleanprint-exclude\"><img src=\"$imagesUrl/Email$buttonStyle.png\"      /></a>";

    return $rtn;
}


// Adds the CleanPrint script tags to the head section
function cleanprint_wp_head() {
    global $page_id;
    global $optionsName;
    global $cleanprintUrl;
    global $publisherKey;
	global $defaultLogoUrl;
    global $cleanprintDebug;
   
	$options      = get_option($optionsName);
	$GASetting    = $options['GASetting'];
	$logoUrl      = $options['logoUrl'];

    $showPrintBtn = $options['PrintInclude']=='include' || !isset($options['PrintInclude']);
    $showPdfBtn   = $options['PDFInclude'  ]=='include' || !isset($options['PDFInclude']);
    $showEmailBtn = $options['EmailInclude']=='include' || !isset($options['EmailInclude']);

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
    if ($cleanprintDebug) {
		printf("\n\n\n<!-- CleanPrint Debug\n\t\t%s\n\t\tpage_id:%s, home:%d, front:%d, category:%d, single:%d, page:%d, tag:%d\n-->\n\n\n",
					               http_build_query($options,"","\n\t\t"), $page_id, is_home(), is_front_page(), is_category(), is_single(), is_page(), is_tag());
	}
		
    printf( "<script id='cpf_wp' type='text/javascript'>\n");
    printf( "   function CleanPrint4WP_Print(postId) {\n");
    printf( "   	CleanPrintPrintHtml(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Print']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanPrint4WP_Email(postId) {\n");
    printf( "   	CleanPrintSendEmail(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'Email']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "   function CleanPrint4WP_PDF(postId) {\n");
    printf( "   	CleanPrintGeneratePdf(null,postId);\n");
						if ($GASetting=="true") {
							printf( "   try { _gaq.push(['_trackEvent', 'CleanPrint', 'PDF']); } catch(e) {}\n");
						}
    printf( "   }\n");
    printf( "</script>\n");
	
	printf( "<script id='cpf_loader' type='text/javascript' src='%s?key=%s&logo=%s'></script>\n", 
	           $cleanprintUrl, urlencode($publisherKey), urlencode($logoUrl));
}



// Add the Settings menu link to the plugin page
function cleanprint_add_action_links($links, $file) {
	global $pluginName;
    global $pluginFile;
    
    if ($file == $pluginFile) {
		$links[] = sprintf("<a href='options-general.php?page=%s'>Settings</a>", $pluginName);
	}
	return $links;
}


// Activate CleanPrint, migrate any old options here
function cleanprint_activate() {
   // cannot use the global, chicken/egg problem
   $options        = get_option('CleanPrintAdminOptions');
   $optionsVersion = '2.1';
   
   if (isset($options)) {
      $version  = $options['version'];   
   
      // Don't know what version we looking at (0.97, 1.0.0, 1.0.1, or 2.0.0) so there is only
      // so much we can do.  The biggest issue of the logoUrl which was hijacked in 2.0.0 and
      // now we cannot tell it use apart from earlier releases.
      if (!isset($version)) {      
         $logoUrl = $options['logoUrl'];
         // Get rid of the old CP3/WP leader board header
         if (isset($logoUrl) && $logoUrl == 'http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg') {      
            unset($options['logoUrl']);
         }
         
         $buttonColor = $options['buttonColor'];
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
    global $pluginName;
    global $pluginFile;
    global $optionsName;
    
    register_setting       ($optionsName, $optionsName, 'cleanprint_sanitize_options');
    register_uninstall_hook($pluginFile, 'cleanprint_uninstall');

    add_settings_section   ('plugin_main', '', 'cleanprint_add_settings_section', $pluginName);
    add_settings_field     ('plugin_logoUrl',         '<strong>Image:</strong>',                     'cleanprint_add_settings_field_logo_url_',     $pluginName, 'plugin_main');
    add_settings_field     ('plugin_buttonStyle',     '<strong>Size / Color:</strong>',              'cleanprint_add_settings_field_button_color',  $pluginName, 'plugin_main');
    add_settings_field     ('plugin_PrintInclude',    '<strong>Print Button:</strong>',              'cleanprint_add_settings_field_print_btn',     $pluginName, 'plugin_main');
    add_settings_field     ('plugin_PDFInclude',      '<strong>PDF Button:</strong>',                'cleanprint_add_settings_field_pdf_btn',       $pluginName, 'plugin_main');
    add_settings_field     ('plugin_EmailInclude',    '<strong>Email Button:</strong>',              'cleanprint_add_settings_field_email_btn',     $pluginName, 'plugin_main');
    add_settings_field     ('plugin_buttonplacement', '<strong>Page Location:</strong>',             'cleanprint_add_settings_field_btn_placement', $pluginName, 'plugin_main');
    add_settings_field     ('plugin_homepage',        '<strong>Home Page:</strong>',                 'cleanprint_add_settings_field_homepage',      $pluginName, 'plugin_main');
    add_settings_field     ('plugin_frontpage',       '<strong>Front Page:</strong>',                'cleanprint_add_settings_field_frontpage',     $pluginName, 'plugin_main');
    add_settings_field     ('plugin_category',        '<strong>Categories:</strong>',                'cleanprint_add_settings_field_category',      $pluginName, 'plugin_main');    
    add_settings_field     ('plugin_posts',           '<strong>Posts:</strong>',                     'cleanprint_add_settings_field_posts',         $pluginName, 'plugin_main');
    add_settings_field     ('plugin_pages',           '<strong>Pages:</strong>',                     'cleanprint_add_settings_field_pages',         $pluginName, 'plugin_main');
    add_settings_field     ('plugin_tags',            '<strong>Tags:</strong>',                      'cleanprint_add_settings_field_tags',          $pluginName, 'plugin_main');
//  add_settings_field     ('plugin_excludes',        '<strong>Excluded Page IDs:</strong>',         'cleanprint_add_settings_field_excludes',      $pluginName, 'plugin_main');
    add_settings_field     ('plugin_gaOption',        '<strong>CleanPrint Event Tracking:</strong>', 'cleanprint_add_settings_field_ga',            $pluginName, 'plugin_main');
}


// WP callback for launching the options menu
function cleanprint_admin_menu() {
   global $pluginName;
   add_options_page('CleanPrint Settings', 'CleanPrint', 'manage_options', $pluginName, 'cleanprint_add_options_page');
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