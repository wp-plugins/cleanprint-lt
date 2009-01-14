<?php
/*
Plugin Name: Clean Print
Plugin URI: http://www.formatdynamics.com
Description: Brings print functionality to your blog
Version: 0.9.4b
Author: Format Dynamics
Author URI: http://www.formatdynamics.com
*/

if (!class_exists("CleanPrint")) {
	class CleanPrint {
		var $adminOptionsName = "CleanPrintAdminOptions";
		var $singleColumnTemplate = "107";
		var $doubleColumnTemplate = "108";
		var $defaultDivId = "2434";
		var $defaultLogoUrl = "http://cache-01.cleanprint.net/media/2434/1229027745109_699.jpg";
		var $gravityDefault = "center";

		function CleanPrint() { //constructor
			
		}
		function init() {
			$this->getAdminOptions();
		}
		function getAdminOptions() {
			$cleanPrintAdminOptions = array("printSpecId" => $this->singleColumnTemplate,
				"divId" => $this->defaultDivId,
				"logoUrl" => $this->defaultLogoUrl,
				"gravity" => $this->gravityDefault,
				"activationKey" => "");
			$devOptions = get_option($this->adminOptionsName);
			if (!empty($devOptions)) {
				foreach ($devOptions as $key => $option){
					$cleanPrintAdminOptions[$key] = $option;
				}
			}
			//see if there is an activation key present and override the printSpecId and divId
			if(!empty($cleanPrintAdminOptions["activationKey"])){
				$this->updateActivationKey($cleanPrintAdminOptions, $cleanPrintAdminOptions["activationKey"]);
			}
			update_option($this->adminOptionsName, $cleanPrintAdminOptions);
			return $cleanPrintAdminOptions;
		}
		function parseKey($theKey){
			$rv = explode("-", $theKey);
			if(count($rv) != 2){
				return null;
			}
			if(!is_numeric($rv[0]) || !is_numeric($rv[1])){
				return null;
			}
			return $rv;
		}
		function updateActivationKey(&$cpOptions, $theKey){
			//see if there is an activation key present and override the printSpecId and divId
			$theKeys = $this->parseKey($theKey);
			if(!empty($theKeys)){
				$cpOptions["divId"] = $theKeys[0];
				$cpOptions["printSpecId"] = $theKeys[1];
			}
			else{
				//change divId and printSpecId if they are not one of the default values, catching removal of activation key
				if($cpOptions["printSpecId"] != $this->singleColumnTemplate && $cpOptions["printSpecId"] != $this->doubleColumnTemplate){
					$cpOptions["printSpecId"] = $this->singleColumnTemplate;
				}
				if($cpOptions["printSpecId"] != $this->defaultDivId){
					$cpOptions["divId"] = $this->defaultDivId;
				}
				
			}
		}
		function printCleanprintAdminPage(){
		        $devOptions = $this->getAdminOptions();
		        $devOptions = $this->getAdminOptions();
		        if (isset($_POST['updateCleanprintSettings'])) {
		                if (isset($_POST['printSpecId'])) {
		                        $devOptions['printSpecId'] = $_POST['printSpecId'];
		                }
		                if (isset($_POST['logo'])) {
		                        if($_POST['logo'] == "default"){
		                                $devOptions['logoUrl'] = $this->defaultLogoUrl;
		                        }
		                        else if($_POST['logo'] == "custom"){
		                                if (isset($_POST['customLogo'])) {
		                                        $devOptions['logoUrl'] = $_POST['customLogo'];
		                                }
		                        }
		                }
				//if (isset($_POST['gravity'])) {
				//	$devOptions['gravity'] = $_POST['gravity'];
				//}
				if(isset($_POST['activationKey'])) {
                                        $devOptions['activationKey'] = $_POST['activationKey'];
					$this->updateActivationKey($devOptions, $_POST['activationKey']);
                                }
		                update_option($this->adminOptionsName, $devOptions);
		                ?>
		                <div class="updated"><p><strong>Settings Updated.</strong></p></div>
		                <?php
		        }
		        $selectedPrintSpec = $devOptions["printSpecId"];
		        $singleColumnTemplate = $this->singleColumnTemplate;
		        $doubleColumnTemplate = $this->doubleColumnTemplate;
			$selectedGravity = $devOptions["gravity"];

		        $logoUrl = $devOptions["logoUrl"];
		        $defaultLogoUrl = $this->defaultLogoUrl;
		        ?>
			<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		                <DIV class="wrap">
				<H2>Cleanprint Settings Menu</H2>
				<?php if(empty($devOptions["activationKey"])){ ?>
				<h3>Pick a template</h3>
		                <TABLE class="form-table">
				<TR>
				<TH class="th-full" scope="row">
				<input type="radio" name="printSpecId" value="<?php echo $singleColumnTemplate; ?>" <?php if ($selectedPrintSpec == $singleColumnTemplate) { echo 'checked="checked"'; }?> />
		                Single Column <br />
		                <input type="radio" name="printSpecId" value="<?php echo $doubleColumnTemplate; ?>" <?php if ($selectedPrintSpec == $doubleColumnTemplate) { echo 'checked="checked"'; }?> />
		                Double Column
				</TH>
				</TR>
				</TABLE>
				<?php } ?>
				<!-- <h3>Pick gravity</h3>
                                <TABLE class="form-table">
                                <TR>
                                <TH class="th-full" scope="row">
				<input type="radio" name="gravity" value="left" <?php if ($selectedGravity == "left") { echo 'checked="checked"'; }?> />
                                Left <br />
                                <input type="radio" name="gravity" value="center" <?php if ($selectedGravity == "center") { echo 'checked="checked"'; }?> />
                                Center
				 </TH>
                                </TR>
                                </TABLE> -->
		                <h3>Header Image</h3>
		                <TABLE class="form-table">
                                <TR>
                                <TH class="th-full" scope="row">
				<input type="radio" name="logo" value="default" <?php if ($defaultLogoUrl == $logoUrl) { echo 'checked="checked"'; }?> />
		                Default<br />
		                <input type="radio" name="logo" value="custom" <?php if ($defaultLogoUrl != $logoUrl) { echo 'checked="checked"'; }?> />
		                Custom:
		                <input type="" name="customLogo" value="<?php echo $defaultLogoUrl == $logoUrl ? "" : $logoUrl; ?>"><small>Example:http://www.someurl.com/image.png Image should be 660 X 40.</small>
		                 </TH>
                                </TR>
                                </TABLE>
				<h3>Activation key</h3>
                                <TABLE class="form-table">
                                <TR>
                                <TH class="th-full" scope="row">
				Key:
                                <input type="" name="activationKey" value="<?php echo $devOptions['activationKey'] ?>">
                                 </TH>
                                </TR>
                                </TABLE>
				<P class="submit">
		                <input type="submit" name="updateCleanprintSettings" value="Update Settings" />
				</P>
				</DIV>
			</form>
		        <?php
		}
	
		function addCpScript(){
			if(is_single()){//comment out this line for mulitple post printing
			$devOptions = $this->getAdminOptions();
			$selectedPrintSpec = $devOptions["printSpecId"];
			$logoUrl = $devOptions["logoUrl"];
			$divId = $devOptions["divId"];
			?>
			<script type="text/javascript">var cpProxyUrl = "<?php echo get_bloginfo('wpurl').'/wp-content/plugins/cleanprint-lt/proxy.php'; ?>";
			var cpLogoUrl = "<?php echo $logoUrl; ?>";
			var cpGravity = "<?php echo $devOptions['gravity']; ?>";</script>
			<script type="text/javascript" src="http://cache-01.cleanprint.net/cp/ccg?divId=<?php echo $divId ?>&ps=<?php echo $selectedPrintSpec ?>" name="cleanprintloader"></script>
			<?php
			}//comment out this line for mulitple post printing
		}
	}
}
function cleanprintButtonInsert(){
	if(is_single()){//comment out this line for mulitple post printing
		return '<br /><a href="#" onclick="FDCPUrl();return false;"><img src="'.get_bloginfo('wpurl').'/wp-content/plugins/cleanprint-lt/BlogPrintButton.png"></a>';
	}//comment out this line for mulitple post printing
	return '';
}
function cpContentTags($content = '') {
	if(is_single()){
		//single post content selection tags
		//select title and other elements preceding post body
		$content = $content.'<span class="fdPrintIncludeParentsPreviousSiblings"></span>';
		//if title and other elements aren't sized correctly in printout, comment out previous line and un-comment out next line
		//$content = $content.'<span class="fdPrintIncludeParentsPreviousSiblingssChildren"></span>';
		//grab all the nodes of the post body
		$content = $content.'<span class="fdPrintIncludeParentsChildren"></span>';
		//uncomment out next line if to include node immediately following the post node. Uncomment out line after that for all nodes following post node. Only uncomment out one at a time.
		//$content = $content.'<span class="fdPrintIncludeParentsNextSibling"></span>';
		//$content = $content.'<span class="fdPrintIncludeParentsNextSiblings"></span>';
		//if item exist after the post body that belong in the printout, comment out the next line
		$content = $content.'<span class="fdPrintExcludeNextSiblings"></span>';
		$content = $content.cleanprintButtonInsert();
		return $content;
	}
	else{
		//Multiple blog posts on the page
		//select title and other elements preceding post body
                $content = $content.'<span class="fdPrintIncludeParentsPreviousSiblings"></span>';
		//if title and other elements aren't sized correctly in printout, comment out previous line and un-comment out next line
                //$content = $content.'<span class="fdPrintIncludeParentsPreviousSiblingssChildren"></span>';
		//grab all the nodes of the post body
                $content = $content.'<span class="fdPrintIncludeParentsChildren"></span>';
		//uncomment out next line if to include node immediately following the post node. Uncomment out line after that for all nodes following post node. Only uncomment out one at a time.
                //$content = $content.'<span class="fdPrintIncludeParentsNextSibling"></span>';
                //$content = $content.'<span class="fdPrintIncludeParentsNextSiblings"></span>';
                //if item exist after the post body that don't belong in the printout, uncomment out the next line
		//$content = $content.'<span class="fdPrintExcludeNextSiblings"></span>';
		return $content;
	}
}
function cpTitleTags($title = ''){
	if(is_single()){
		return "<span class='fdPrintIncludeParent'></span>".$title;
	}
	return $title;
}
if(class_exists("CleanPrint")) {
	$cleanPrint = new CleanPrint();
}

if(!function_exists("cleanPrintAdminPage")){
	function cleanPrintAdminPage() {
		global $cleanPrint;
		if(!isset($cleanPrint)){
			return;
		}
		if(function_exists('add_options_page')){
			add_options_page('Cleanprint Settings', 'Cleanprint Settings Menu', 9, basename(__FILE__), array(&$cleanPrint, 'printCleanprintAdminPage'));
		}
	}
}

if (isset($cleanPrint)) {
	//Actions
	add_action('admin_menu', 'cleanPrintAdminPage');
	add_action('cleanprint-lt/cleanprint.php', array(&$cleanPrint, 'init'));
	add_action('wp_head', array(&$cleanPrint, 'addCpScript'), 1);
	add_action('wp_meta', 'cleanprintButtonInsert');
	//Filters
	add_filter('the_content', 'cpContentTags');
}

?>
