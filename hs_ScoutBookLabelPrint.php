<?php
/**
* Plugin Name: HoweScape ScoutBook Label Print
* Plugin URI: http://howescape.com/
* Description: ScoutBook does not provide a option to create labels. This plugin takes the CSV file created by ScoutBoox and creates a printable PDF file.   
* Version: 1.7.2
* Author: P.T.Howe
* Text Domain: hs_ScoutBookLabelPrint
* Domain Path: /languages
* Author URI: http://HoweScape.com/
* License: GPL2
*/
	// Constants
	include_once ( plugin_dir_path( __FILE__ ) . "./include/hs_ScoutBookLabelPrint_constants.php");
	// Include the main TCPDF library (search for installation path).
	require_once( plugin_dir_path( __FILE__ ) . 'tcpdf/tcpdf_import.php');
		
class hs_ScoutBookLabelPrint_pdf extends TCPDF {
	// Extend the TCPDF, Define function to override the header
	// This allows a title and page number to be included.
	static $fileNameLocal = "";
	static $pagenum = 1;
	static $shouldPageNumInc = true;
	static $pageType = 'PlainPaper';	// BSA Stock
	
	function SetTitleFileName ($inFileName) {
		global $fileNameLocal;
		global $pagenum;
		
		$fileName = $inFileName;
		$fileNameLocal = $fileName;
		$pagenum = 1;
	}
	
	function SetPageType($inPageType) {
		global	$pageType;
		$pageType = $inPageType;
	}
	
	function Header() {
		global $fileNameLocal;
		global $pagenum;
		global $pageType;
		
		if (strcmp($pageType, Extension_PageType_PlainPaper) == 0) {

			$headerHeight = 5; 
			$this->Ln(10); // Add space above file name in title bar
			// Select Ariel bold 15
			$this->SetFont(HS_PDF_SETTING_FONT,HS_PDF_SETTING_BOLD,9);
			// Move to the right
			$this->Cell(126,$headerHeight," ");
			// Framed title
			$this->Cell(126,$headerHeight,$fileNameLocal,0,0,'C');
			$this->Cell(126,$headerHeight,"Page: ".$pagenum,0,0,'R');
			$pagenum = $pagenum + 1;
			// Line break
			$this->Ln(4);
		} else if (strcmp($pageType, Extension_PageType_BSAStock) == 0) {
			$this->SetY(-150);
		}
	}
	
	function Footer() {
		global $fileNameLocal;
		global $pagenum;
		global $pageType;

		if (strcmp($pageType, Extension_PageType_BSAStock) == 0) {
			// Position at 15 mm from bottom
			$this->SetY(-15);			
			$footerHeight = 5; 
			//$this->Ln(10); // Add space above file name in title bar
			// Select Ariel bold 15
			$this->SetFont(HS_PDF_SETTING_FONT,HS_PDF_SETTING_BOLD,9);
			// Move to the right
			$this->Cell(126,$footerHeight," ");
			// Framed title
			$this->Cell(126,$footerHeight,$fileNameLocal,0,0,'C');
			$this->Cell(126,$footerHeight,"Page: ".$pagenum,0,0,'R');
			$pagenum = $pagenum + 1;
			// Line break
			//$this->Ln(4);
		}
	}
	
	function TextWithRotation($w, $h, $txt, $txt_angle, $font_angle=0) {
		//$font_angle+=90+$txt_angle;
		//$txt_angle*=M_PI/180;
		//$font_angle*=M_PI/180;

		//$txt_dx=cos($txt_angle);
		//$txt_dy=sin($txt_angle);
		//$font_dx=cos($font_angle);
		//$font_dy=sin($font_angle);

		//$s=sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm (%s) Tj ET',$txt_dx,$txt_dy,$font_dx,$font_dy,$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
		///if ($this->ColorFlag)
		//	$s='q '.$this->TextColor.' '.$s.' Q';
		//$this->_out($s);

		$local_x = $this->GetX();
		$local_y = $this->GetY();
		$local_y = $local_y + 80; //(80 * $inColNum);
		
		$this->StartTransform();
		$this->SetXY($local_x, $local_y);
		$this->Rotate($txt_angle);
		$this->Cell($w,$h,'Cub Scout',TCPDF_PDF_BORDER_NODRAW,1,'L',0,'');
		$this->StopTransform();
	}	
}

class hs_ScoutBookLabelPrint {

	// Debug function 
	function send_to_console($debug_output) {

		$cleaned_string = '';
		if (!is_string($debug_output))
			$debug_output = print_r($debug_output,true);

		$str_len = strlen($debug_output);
		for($i = 0; $i < $str_len; $i++) {
			$cleaned_string .= '\\x' . sprintf('%02x', ord(substr($debug_output, $i, 1)));
		}
		$javascript_ouput = "<script>console.log('Debug Info: " .$cleaned_string. "');</script>";
		echo $javascript_ouput;
	}

	// 
	function hs_sblp_load_js_css($hook) {
		//wp_register_style ( 'prefix-style', plugins_url('include/hs_ScoutBookLabelPrint_style.css', __FILE__));
		wp_register_style ( 'hs_ScoutBookLabelPrint', plugins_url('include/hs_ScoutBookLabelPrint_style.css', __FILE__));
		wp_enqueue_style( 'hs_ScoutBookLabelPrint' );	
		wp_register_style ( 'hs_ScoutBookLabelPrint_popup', plugins_url('include/hs_ScoutBookLabelPosition.css', __FILE__));
		wp_enqueue_style( 'hs_ScoutBookLabelPrint_popup' );	
		wp_register_script( 'hs_ScoutBookLabelPrint_popupJS', plugins_url('include/hs_ScoutBookLabelPosition.js', __FILE__), '', false, false);
		wp_enqueue_script( 'hs_ScoutBookLabelPrint_popupJS' );
	}

	function hs_ScoutBookLabelPrint_settings_description ($links, $file) {
		$settings_link = 'options-general.php?page=hs_ScoutBookLabelPrint_settings.php';
		$my_settings = __('Settings', 'hs_ScoutBookLabelPrint');
		if (strpos( $file, 'hs_ScoutBookLabelPrint.php' ) != false ) {
			$new_links = array('<a href="'.$settings_link.'" target="_blank">'.$my_settings.'</a>');
			
			$links = array_merge ($links, $new_links);
		}
		return $links; 
	}

	// language 
	function hs_ScoutBookLabelPrint_load_textDomain() {
		load_plugin_textdomain( 'hs_ScoutBookLabelPrint', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	// Admin page definition.
	function hs_ScoutBookLabelPrint_admin() {
		$my_settingsPage = __('HoweScape ScoutBook Label Print Settings', 'hs_ScoutBookLabelPrint');
		$my_settingsMenu = __('HoweScape ScoutBook Label Print', 'hs_ScoutBookLabelPrint');
		add_options_Page($my_settingsPage, $my_settingsMenu, 'manage_options', 
							'hs_ScoutBookLabelPrint_settings', array($this, 'hs_ScoutBookLabelPrint_admin_options_page'));
	}

	function hs_ScoutBookLabelPrint_admin_options_page() {
		$option_checked_yes = '';
		$option_checked_no = '';
		
		$option_value_showBox = 0;
		//$option_MB_ImageLoc = HS_SETTING_SBLP_MBDIRPATH_INIT;
		//$option_PresentionCard = HS_SETTING_SBLP_PRESENTIONCARD_INIT;
		$option_Badge_Images = HS_SETTING_SBLP_MBRANKXML_INIT;
		$xmlValues = $this->hs_ScoutBookLabelPrint_loadXML();

		// Load list of abbr names for there display
		$meritBadgesAbbr = $this->hs_ScoutBookLabelPrint_loadMB($xmlValues);
		
		if (get_option( HS_SETTING_SBLP_SHOWBOX, '0' ) !== false) {
			$option_value_showBox = get_option(HS_SETTING_SBLP_SHOWBOX);

		} else {
			$deprecated = null;
			$autoload = 'no';
			add_option (HS_SETTING_SBLP_SHOWBOX, $option_value_showBox, $deprecated, $autoload);		
			$option_value_showBox = 1;

		}
		
		// Set Option to get list of MB and Ranks
		if (get_option(HS_SETTING_SBLP_MBRANKXML, HS_SETTING_SBLP_MBRANKXML_INIT) != false) {
			$option_Badge_Images = get_option(HS_SETTING_SBLP_MBRANKXML);
		} else {
			$deprecated = null;
			$autoload = 'no';
			add_option (HS_SETTING_SBLP_MBRANKXML, $option_Badge_Images, $deprecated, $autoload);
			$option_Badge_Images = HS_SETTING_SBLP_MBRANKXML_INIT;
			
		}

		// check for valid update and update option
		if (isset( $_POST['update-settings'])) {
			if (isset($_POST['HS_SETTINGS_NONCE']) && wp_verify_nonce($_POST['HS_SETTINGS_NONCE'], 'HS_SETTINGS_ADMIN') != false) {
				if (isset($_POST['drawBorder']) && !empty($_POST['drawBorder'])) {
					$option_value_showBox = sanitize_text_field($_POST['drawBorder']);
					update_option(HS_SETTING_SBLP_SHOWBOX, $option_value_showBox);

				} elseif (isset($_POST['drawBorder'])) {
					$option_value_showBox = 0;
					update_option(HS_SETTING_SBLP_SHOWBOX, $option_value_showBox);

				}
			} else {
				 die( 'Security check' ); 
			}
		} else {
			$option_value_showBox = get_option( HS_SETTING_SBLP_SHOWBOX, '0');
			update_option(HS_SETTING_SBLP_SHOWBOX, $option_value_showBox);

		}
		
		// Set Radio selection 
		if ($option_value_showBox == 1) {
			$option_checked_yes = 'Checked="checked"';
		} else {
			$option_checked_no = 'Checked="checked"';
		}

		$my_SettingTitle = __('HoweScape ScoutBook Label Print Settings', 'hs_ScoutBookLabelPrint');
		$my_updateSetting = __('Update Settings', 'hs_ScoutBookLabelPrint');
		$my_displayGraphics = __('Display Graphics', 'hs_ScoutBookLabelPrint');
		$my_drawBorderLabel = __('Draw Border', 'hs_ScoutBookLabelPrint');
//		$columnHeader_orignal = __('CSV Award Original title', 'hs_ScoutBookLabelPrint');
//		$columnHeader_abbr = __('CSV Award Abbr title', 'hs_ScoutBookLabelPrint');
//		$columnHeader_abbrML = __('CSV Award Abbr Multi Line', 'hs_ScoutBookLabelPrint');
		$my_MB_PresentationCard = __('Presentation Card', 'hs_ScoutBookLabelPrint');
		$my_MB_listLocation = __('Merit Badge / Rank Images List XML',  'hs_ScoutBookLabelPrint');
		$columnHeader_SKU	= __('SKU', 'hs_ScoutBookLabelPrint');
		$columnHeader_TYPE	= __('Type', 'hs_ScoutBookLabelPrint');
		$columnHeader_NAME	= __('Name', 'hs_ScoutBookLabelPrint');
		$columnHeader_GRAPHIC	= __('Graphic', 'hs_ScoutBookLabelPrint');
		//$columnHeader_GRAPHIC_SVG = __('SVG Available', 'hs_ScoutBookLabelPrint');
		$columnHeader_Width = __('Width', 'hs_ScoutBookLabelPrint');
		$columnHeader_Height = __('Height', 'hs_ScoutBookLabelPrint');
				
		// Form which allows draw / no draw of box
		echo '<h2>'.$my_SettingTitle.'</h2>';
		echo('<div class="divTable blueTable">');
			echo('<div class="divTableHeading">');
			echo('<div class="divTableRow">');
			echo('</div>');
			echo('</div>');

			echo '<form id="" method="POST">';
			wp_nonce_field('HS_SETTINGS_ADMIN', 'HS_SETTINGS_NONCE', true, true);
			echo ('<div class="divTableBody">');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell">'.$my_drawBorderLabel.'</div>');
				echo ('<div class="divTableCell"><input type="radio" name="drawBorder" value="0" '.$option_checked_no.'>No');
				echo ('<input type="radio" name="drawBorder" value="1" '.$option_checked_yes.'>Yes</div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo '<div class="divTableCell"><input type="submit" name="update-settings" value="'.$my_updateSetting.'" class="button button-primary"/></div>';
				echo '<div class="divTableCell"></div>';
			echo ('</div>');
			echo('</div>');
		echo '</form>';
		echo('</div>');
		echo('<br>');
	/*
		// abbr list
		echo('<div class="divTable blueTable">');
			echo('<div class="divTableHeading">');
			echo('<div class="divTableRow">');
				echo('<div class="divTableHead">'.$columnHeader_orignal.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_abbr.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_abbrML.'</div>');
			echo('</div>');
			echo('</div>');
			echo('<div class="divTableBody">');
			foreach ($meritBadgesAbbr as $singleAbbr) {
				//echo ($singleAbbr[HS_SBLP_NAME]);
				//$len = strpos($singleAbbr,';');
				//$fullName = substr($singleAbbr, 0, $len);
				//$abbrName = substr($singleAbbr, $len+1 );
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell">'.$singleAbbr[HS_SBLP_NAME].'</div>');
					echo ('<div class="divTableCell">'.$singleAbbr[HS_SBLP_ABBR].'</div>');
					echo ('<div class="divTableCell">'.$singleAbbr[HS_SBLP_MULTILINE].'</div>');
					//echo ('<div class="divTableCell">'.$fullName.'</div>');
					//echo ('<div class="divTableCell"><input type="text" size="50" value="'.$abbrName.'" /></div>');
					//echo ('<div class="divTableCell"><input type="text" size="50" value="'.$singleAbbr.'" /></div>');
					//echo ('<div class="divTableCell">'.$abbrName.'</div>');
				echo ('</div>');
			}
			echo('</div>');
		echo('</div>');		
			// abbr list end
	*/
		echo('<br>');

		// Display Graphic List
		echo '<form id="" method="POST" action="">';
		wp_nonce_field('HS_GRAPHICS_ADMIN', 'HS_GRAPHICS_NONCE', true, true);
		//echo('<input type="hidden" name="action" value="hs_ScoutBookLabelPrint_GraphicResult">');
		echo ('<div class="divTable blueTable">');
			echo('<div class="divTableHeading">');
				echo ('<div class="divTableRow">');
					echo '<div class="divTableHead">Cub Scouting</div>';
					echo '<div class="divTableHead">Scouts BSA</div>';
					//echo '<div class="divTableHead">Venturing</div>';
					//echo '<div class="divTableHead">Sea Scouting</div>';
					//echo '<div class="divTableHead">Exploring</div>';
					echo '<div class="divTableHead">App Settings</div>';
				echo ('</div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				// Set Checked flag
				$checked_academicSport = '';
				$checked_adventure = '';
				$checked_cubRank = '';
				$checked_merit = '';
				$checked_boyRank = '';
				$checked_webelos = '';
				$checked_boySegments = '';
				$checked_AppSettings = '';
				if (isset($_POST['AcademicsSportsBeltLoops'])) {
					$checked_academicSport = 'Checked';
				}
				if (isset($_POST['AdventureLoops'])) {
					$checked_adventure = 'Checked';
				}
				if (isset($_POST['WebelosActivityPins'])) {
					$checked_webelos = 'Checked';
				}
				if (isset($_POST['CubScoutRanks'])) {
					$checked_cubRank = 'Checked';
				}
				if (isset($_POST['MeritBadges'])) {
					$checked_merit = 'Checked';
				}
				if (isset($_POST['BoyScoutRanks'])) {
					$checked_boyRank = 'Checked';
				}
				if (isset($_POST['BoySegments'])) {
					$checked_boySegments = 'Checked';
				}
				if (isset($_POST['AppSettings'])) {
					$checked_AppSettings = 'Checked';
				}
				//Cub
				echo '<div class="divTableCell">';
				echo '<input type="checkbox" name="AcademicsSportsBeltLoops" value="Academics &#38; Sports Belt Loop" '.$checked_academicSport.'><label for="Academic & Sport BeltLoops">Academic & Sport Belt Loops</label><br>';
				echo '<input type="checkbox" name="AdventureLoops" value="Adventure" '.$checked_adventure.'><label for="AdventureLoops">Adventure Loops</label><br>';
				echo '<input type="checkbox" name="WebelosActivityPins" value="Webelos Activity Badge" '.$checked_webelos.'><label for="Webelos Activity Badge">Webelos Activity Badge</label><br>';
				echo '<input type="checkbox" name="CubScoutRanks" value="Rank" '.$checked_cubRank.'><label for="CubRanks">Cub Rank Bages</label><br>';
				echo '</div>';
				// Scout
				echo '<div class="divTableCell">';
					echo '<input type="checkbox" name="MeritBadges" value="Merit Badge" '.$checked_merit.'><label for="MeritBadges">Merit Bages</label><br>';
					echo '<input type="checkbox" name="BoyScoutRanks" value="Rank" '.$checked_boyRank.'><label for="BoyScoutRanks">Rank Bages</label><br>';
					echo '<input type="checkbox" name="BoySegments" value="Award" '.$checked_boySegments.'><label for="BoySegments">Boy Scout Segments</label><br>';
				echo '</div>';
				//Vent
				//echo '<div class="divTableCell"><br></div>';
				// Sea Scouting
				//echo '<div class="divTableCell"><br></div>';
				// Exploring
				//echo '<div class="divTableCell"><br></div>';
				// App Settings
				echo '<div class="divTableCell">';
					echo '<input type="checkbox" name="AppSettings" value="AppSettings" '.$checked_AppSettings.'><label for="AppSettings">App Settings</label>';
				echo '</div>';
			echo('</div>');
			echo ('<div class="divTableRow">');
				echo '<div class="divTableCell"></div>';
				echo '<div class="divTableSpanCell"><input type="submit" name="display-graphic" value="'.$my_displayGraphics.'" class="button button-primary"/></div>';
				echo '<div class="divTableCell"></div>';
			echo ('</div>');
			//echo('</div>');
		echo('</div>');
		echo '</form>';
		echo('<br>');

		$badgeWidth = 42;
		$badgeHeight = 42;
		$awardWidth = 84;
		$awardHeight = 60;
		// Define values set from POST
		$MB_Check = "";		// MeritBadge
		$BSR_Check = "";	// Boy Scout Rank
		$BS_Check = "";		// Boy Segments 
		$csr_Check = "";	// Cub Scout Rank
		$ASBL_Check = "";	// Academic Sports Belt Loop 
		$AL_Check = "";		// Adventure Loops
		$WAP_Check = "";	// Webelos Activity Pins
		$Setting_Check = "";	// Setting Check
		//
		if (isset($_POST['MeritBadges'])) {
			$MB_Check = $_POST['MeritBadges'];
		}
		if (isset($_POST['BoyScoutRanks'])) {
			$BSR_Check = $_POST['BoyScoutRanks'];
		}
		if (isset($_POST['BoySegments'])) {
			$BS_Check = $_POST['BoySegments'];
		}
		if (isset($_POST['CubScoutRanks'])) {
			$csr_Check = $_POST['CubScoutRanks'];
		}
		if (isset($_POST['AcademicsSportsBeltLoops'])) {
			$ASBL_Check = $_POST['AcademicsSportsBeltLoops'];
		}
		if (isset($_POST['AdventureLoops'])) {
			$AL_Check = $_POST['AdventureLoops'];
		}
		if (isset($_POST['WebelosActivityPins'])) {
			$WAP_Check = $_POST['WebelosActivityPins'];
		}
		if (isset($_POST['AppSettings'])) {
			$Setting_Check = $_POST['AppSettings'];
		}

		// display App settings 
		if (strcmp( 'AppSettings', $Setting_Check) == 0) {
			$this->HS_ScoutBookLabelPrint_Settings($meritBadgesAbbr);
		}
		//
		// Award Graphic list
		echo('<div class="divTable blueTable">');
			echo('<div class="divTableHeading">');
			echo('<div class="divTableRow">');
				echo('<div class="divTableHead">'.$columnHeader_SKU.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_TYPE.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_NAME.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_GRAPHIC.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_Width.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_Height.'</div>');
			echo('</div>');
			echo('</div>');

			echo('<div class="divTableBody">');
			// get data and build rows
			$graphicBadgeList = $this->HS_ScoutBookLabelPrint_loadBadgeGraphics();
			
			foreach ($graphicBadgeList as $singleRow) {
						$skuName = '';
						$typeName = '';
						$nameName = '';
						$graphicName = '';
						$width = $badgeWidth;
						$height = $badgeHeight;
						$BoySegmentsField = "";
						if (isset($_POST['BoySegments'])) {
							$BoySegmentsField = $_POST['BoySegments'];
						}
						if (strcmp($singleRow['Type'], $BoySegmentsField) == 0 && 
							strcmp($singleRow['Category'], HS_SBLP_UNIT_BOY) == 0) {
							$width = $awardWidth;
							$height = $awardHeight;
						}
				//echo ("<br>".$singleRow['Type'].":".$_POST['Award'].":".$singleRow['Category'].":");
		
				if ((strcmp($singleRow['Type'], $MB_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_BOY) == 0) ||
					(strcmp($singleRow['Type'], $BSR_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_BOY) == 0) ||
					(strcmp($singleRow['Type'], $BS_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_BOY) == 0) ||
					(strcmp($singleRow['Type'], $csr_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_CUB) == 0) ||
					(strcmp($singleRow['Type'], $ASBL_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_CUB) == 0) ||
					(strcmp($singleRow['Type'], $AL_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_CUB) == 0) ||
					(strcmp($singleRow['Type'], $WAP_Check) == 0 && strcmp($singleRow[HS_ARRAY_Graphic_Category], HS_SBLP_UNIT_CUB) == 0)
					) {

				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell">'.$singleRow[HS_ARRAY_Graphic_SKU].'</div>');
					echo ('<div class="divTableCell">'.$singleRow[HS_ARRAY_Graphic_Type].'</div>');
					echo ('<div class="divTableCell">'.$singleRow[HS_ARRAY_Graphic_Name].'</div>');
					$mbGraphic = plugin_dir_path( __FILE__ ).$singleRow[HS_ARRAY_Graphic_Img];
					if (file_exists($mbGraphic) == true) {
					} else {
						$mbGraphic = $singleRow[HS_ARRAY_Graphic_Name];
					}
					echo ('<div class="divTableCell"><img src="'.plugins_url( $singleRow[HS_ARRAY_Graphic_Img], __FILE__ ).'" alt="'.$singleRow[HS_ARRAY_Graphic_Name].'" height="'.$height.'" width="'.$width.'" /></div>');
					echo ('<div class="divTableCell">'.$singleRow[HS_ARRAY_Graphic_Width].'</div>');
					echo ('<div class="divTableCell">'.$singleRow[HS_ARRAY_Graphic_Height].'</div>');
				echo ('</div>');
				}
			} 
			echo('</div>');
		echo('</div>');		
			// Graphic list end
		echo('<br>');
	}

	//
	function HS_ScoutBookLabelPrint_Settings($meritBadgesAbbr) {
		$columnHeader_orignal = __('CSV Award Original title', 'hs_ScoutBookLabelPrint');
		$columnHeader_abbr = __('CSV Award Abbr title', 'hs_ScoutBookLabelPrint');
		$columnHeader_abbrML = __('CSV Award Abbr Multi Line', 'hs_ScoutBookLabelPrint');
		
		// abbr list
		echo('<div class="divTable blueTable">');
			echo('<div class="divTableHeading">');
			echo('<div class="divTableRow">');
				echo('<div class="divTableHead">'.$columnHeader_orignal.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_abbr.'</div>');
				echo('<div class="divTableHead">'.$columnHeader_abbrML.'</div>');
			echo('</div>');
			echo('</div>');
			echo('<div class="divTableBody">');
			foreach ($meritBadgesAbbr as $singleAbbr) {
				//echo ($singleAbbr[HS_SBLP_NAME]);
				//$len = strpos($singleAbbr,';');
				//$fullName = substr($singleAbbr, 0, $len);
				//$abbrName = substr($singleAbbr, $len+1 );
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell">'.$singleAbbr[HS_SBLP_NAME].'</div>');
					echo ('<div class="divTableCell">'.$singleAbbr[HS_SBLP_ABBR].'</div>');
					echo ('<div class="divTableCell">'.$singleAbbr[HS_SBLP_MULTILINE].'</div>');
					//echo ('<div class="divTableCell">'.$fullName.'</div>');
					//echo ('<div class="divTableCell"><input type="text" size="50" value="'.$abbrName.'" /></div>');
					//echo ('<div class="divTableCell"><input type="text" size="50" value="'.$singleAbbr.'" /></div>');
					//echo ('<div class="divTableCell">'.$abbrName.'</div>');
				echo ('</div>');
			}
			echo('</div>');
		echo('</div>');		
		// abbr list end
	}
	
	// 
	function HS_ScoutBookLabelPrint_loadBadgeGraphics() {
			$BadgeGraphicList = array();
			// get data and build rows
			$xmlFileName = plugin_dir_path( __FILE__ ).get_option(HS_SETTING_SBLP_MBRANKXML);
			if (file_exists($xmlFileName)) {
				//echo ("<br> file:".$xmlFileName);
				$xmlBadge = simplexml_load_file($xmlFileName);
			
				$firstTag = $xmlBadge->getName();
				if (strcmp($firstTag, 'ScoutBook') == 0) {
					//echo ("<br> tag2:".$firstTag.":");
					foreach ($xmlBadge->children() as $rootBadges) {
						//echo ("<br> ".$rootBadges->getName());
						foreach ($rootBadges->children() as $subRootBadges) {
							$category = '';
							$skuName = '';
							$typeName = '';
							$nameName = '';
							$graphicName = '';
							$ItemRank = '';
							$graphicWidth = 42;
							$graphicHeight = 42;

							//echo ("<br> ".$subRootBadges->getName());
							foreach ($subRootBadges->children() as $awardBadges) {
								//echo ("<br> :".$awardBadges->getName().":".$awardBadges);
								if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_Category) == 0) {
									$category = $awardBadges;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_SKU) == 0) {
									$skuName = $awardBadges;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_ItemType) == 0) {
									$typeName = $awardBadges;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_ItemName) == 0) {
									$nameName = $awardBadges;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_IMG) == 0) {
									$pathUpdated = str_replace('/',DIRECTORY_SEPARATOR,$awardBadges); 
									$graphicNameIMG = $pathUpdated;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_ItemRank) == 0) {
									$ItemRank = $awardBadges;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_Width) == 0) {
									$graphicWidth = $awardBadges;
								} else if (strcmp($awardBadges->getName(), HS_XML_GRAPHICS_Height) == 0) {
									$graphicHeight = $awardBadges;
								}
							}
							$BadgeGraphicList[] = array(HS_ARRAY_Graphic_Category => $category, HS_ARRAY_Graphic_SKU => $skuName, 
														HS_ARRAY_Graphic_Type => $typeName, HS_ARRAY_Graphic_Name => $nameName, 
														HS_ARRAY_Graphic_Img => $graphicNameIMG, HS_ARRAY_Graphic_Rank => $ItemRank,
														HS_ARRAY_Graphic_Width => $graphicWidth, HS_ARRAY_Graphic_Height => $graphicHeight);
						}
					}
				}
			}
		return $BadgeGraphicList;
	}

	// Load lists of MB from XML file
	function hs_ScoutBookLabelPrint_loadMB($xmlValues) {
		$mbList = array();
		$isMBListValue = FALSE;
		$subListCount = 0;
		//$selectedUnit = $_COOKIE[HS_COOKIE_SBLP_UnitType];
		foreach ($xmlValues as $singleRow) {
			//echo ("list-lmb: ".$singleRow[HS_SBLP_NAME]." count: ".$subListCount."<br>");
			if ($isMBListValue == TRUE && $singleRow != -1 && $subListCount >= 3 && $subListCount < 5) { 
				$mbList[] = $singleRow;
			} elseif ($isMBListValue == TRUE && $singleRow == -1 && $subListCount >= 4) {
				$subListCount = $subListCount + 1;
				$isMBListValue = FALSE;
			} elseif ($isMBListValue == FALSE && $singleRow == -1) {
				$subListCount = $subListCount + 1;
				if ($subListCount >=4 && $subListCount <= 4) {
					$isMBListValue = TRUE;
				}
			} elseif ($subListCount >= 4) {
				$isMBListValue = TRUE;
			}
		}
		return $mbList;
	}
	
	function hs_ScoutBookLabelPrint_loadCouncil($selectedCouncil) {
		$councils = array();
		$keys = array();
		$values = array();
		$councilXML = plugin_dir_path( __FILE__ )."include/CouncilList.xml";
		$xml = simplexml_load_file($councilXML);
		$firstTag = $xml->getName();
		if (strcmp($firstTag, HS_SBLP_ROOT) == 0) {
			foreach ($xml->children() as $rootChildren) {
				$councilName = $rootChildren->getName(); 
				if (strcmp($councilName, HS_SBLP_COUNCILLIST) == 0) {
					foreach ($rootChildren->children() as $councilEntry) {
						$councilName2 = $councilEntry->getName();
						//echo (" council2:".$councilName2."<br>");
						foreach ($councilEntry->children() as $councilFields) {
							if (strcmp($councilFields->getName(), HS_SBLP_NAME) == 0) {
								$councilName = $councilFields;
								//echo "<Option value='".$councilFields."'>".$councilFields."</Option>";
							} elseif (strcmp ($councilFields->getName(), HS_SBLP_NUMBER) == 0) {
								$councilNumber = $councilFields;
							}
						}
						$keys[] = $councilNumber;
						$values[] = $councilName;
					}
				}
			}
		}
		$councils = array_combine($keys, $values);

		$sortResult = asort($councils, SORT_STRING);

		$selectedCouncil = str_replace('+', ' ', $selectedCouncil);

		foreach ($councils as $a => $b) {
			if (strcmp($selectedCouncil, $b) == 0){
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			echo "<Option value='".$b."' ".$selected." >".$b." - ".$a."</Option>";
		}
	}

	function hs_ScoutBookLabelPrint_loadXML() {
		$dropdownValues = array();
		
		$generalXML = plugin_dir_path( __FILE__ )."include/ScoutBook_DropDown.xml";
		$xml = simplexml_load_file($generalXML);
		$firstTag = $xml->getName();
		//echo ("First: ".$firstTag."<br>");
		if (strcmp($firstTag, HS_SBLP_GENERAL) == 0) {
			foreach ($xml->children() as $rootChildren) {
				//echo ("Children: ".$rootChildren."<br>");
				if (strcmp($rootChildren->getName(), HS_SBLP_FONTS) == 0) {
					foreach ($rootChildren->children() as $fontsChildren) {
						//echo ("Font Children: ".$fontsChildren."<br>");
						//$dropdownValues[] = $fontsChildren;
						$dropdownValues[] = array (HS_SBLP_FONT => $fontsChildren);
					}
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_UNITS) == 0) {
					foreach ($rootChildren->children() as $unitsChildren) {
						//echo ("Unit Children: ".$unitsChildren."<br>");
						//$dropdownValues[] = "".$unitsChildren."";
						$dropdownValues[] = array (HS_SBLP_UNIT => $unitsChildren);
					}
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_RANKMSG) == 0) {
					//$dropdownValues[] = $rootChildren;
					$dropdownValues[] = array( HS_SBLP_RANKMSG => $rootChildren);
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_MBMSG) == 0) {
					//$dropdownValues[] = $rootChildren;
					$dropdownValues[] = array( HS_SBLP_MBMSG => $rootChildren);
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_ABBRLIST) == 0) {
					foreach ($rootChildren->children() as $meritBadgeChilren) {
						// mb abbr.
						$fullName = "";
						$abbrName = "";
						$multiLine = "";
						foreach ($meritBadgeChilren->children() as $mbc) {
							//echo "MB: ".$meritBadgeChilren."<br>";
							if (strcmp($mbc->getName(),HS_SBLP_NAME) == 0) {
								$fullName = $mbc;
							} elseif (strcmp($mbc->getName(), HS_SBLP_ABBR) == 0) {
								$abbrName = $mbc;
							} elseif (strcmp($mbc->getName(), HS_SBLP_MULTILINE) == 0) {
								//error_log(print_r("<br> multi:".$mbc->getName()." : ".$mbc." :"));
								//echo ("<br> value:".$mbc.":<br>");
								$multiLine = $mbc;
								//echo ("<br> value2:".$multiLine.":<br>");
							}
						}
						//echo " both: ".$fullName.";".$abbrName."<br>";
						//$dropdownValues[] = $fullName.";".$abbrName; 
						$dropdownValues[] = array(HS_SBLP_NAME => $fullName,
													HS_SBLP_ABBR => $abbrName,
													HS_SBLP_MULTILINE => $multiLine);
					}
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_AWARDS_BOY) == 0) {
					foreach ($rootChildren->children() as $AwardTypeChildren) {
						//error_log ($AwardTypeChildren);
						//foreach ($AwardTypeChildren->children() as $advName) {
							if (strcmp($AwardTypeChildren->getName(), HS_SBLP_AWARDS_BOY_ADV) == 0) {
								$dropdownValues[] = $AwardTypeChildren;
							}
						//}
					}
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_OUTPUTTYPES) == 0) {
					foreach ($rootChildren->children() as $outputTypeChildren) {
						$outputTID = $outputTypeChildren[HS_SBLP_OUTPUTTYPEID];

						$dropdownValues[] = array( HS_SBLP_OUTPUTTYPEID => $outputTypeChildren[HS_SBLP_OUTPUTTYPEID], 
													HS_SBLP_OUTPUTTYPE => $outputTypeChildren);
						
					}
				} elseif (strcmp($rootChildren->getName(), HS_SBLP_ADVANCEMENTTYP) == 0) {

					foreach ($rootChildren->children() as $outputTypeChildren) {
						$dropdownValues[] = array (HS_SBLP_ADVANCEMENTTYPID => $outputTypeChildren[HS_SBLP_ADVANCEMENTTYPID],
													HS_SBLP_ADVANCEMENTTYP => $outputTypeChildren);
					}
				} elseif (strcmp($rootChildren->getName(), 'AwardTitleLineBreaks') == 0) {
					foreach($rootChildren->children() as $outputTypeChildren) {
						//echo  " AwardLine:1:".$outputTypeChildren.' :: '.'<br>';
						$singleLine = array();
						$breakIndex = 0;
						foreach ($outputTypeChildren as $lineBreaks) {
							//echo  " AwardLine:2:".$lineBreaks.' :: '.$lineBreaks['index'].'<br>';
							$breakIndex++;
							$lineBreakLabel = 'lineBreakText'.$breakIndex;
							$lineBreakIndex = 'lineBreakIndex'.$breakIndex;
							$lineBreaksSpace = str_replace("+", " ", $lineBreaks);
							//$singleLine[] = array($lineBreakLabel => $lineBreaksSpace, $lineBreakIndex => $lineBreaks['index']);
							
							$singleLine[$lineBreakLabel] = $lineBreaksSpace;
							$singleLine[$lineBreakIndex] = $lineBreaks['index'];

						}
						
						$dropdownValues[] = $singleLine;
					}
				}
				$dropdownValues[] = -1;
			}
		}
		//

		return $dropdownValues;
	}

	function hs_ScountBookLabelPrint_labelCount ($inCol, $inRow, $inSelRow) {
		//echo "cookieValue: ".$inSelRow."<br>";
		for ($i = 1; $i < ($inCol * $inRow)+1; $i++) {
			if ($inSelRow == $i) {
				$selectedValue = 'selected="selected"';
			} else {
				$selectedValue = '';
			}
			echo ('<option value="'.$i.'" '.$selectedValue.' >'.$i.'</option>');
		}
	}


	function hs_ScoutBookLabelPrint_loadMsg( $xmlValues, $index, $inMsg, $arrayTag) {
		$isRankValue = FALSE;
		$isFlagCount = 0;
		$rowCount = 0;
		$loadMsgText = "";
		if (strlen($inMsg) == 0) {
			foreach ($xmlValues as $singleRow) {
				if ($singleRow == -1) { 
					$isFlagCount = $isFlagCount + 1;
				} elseif ($isFlagCount == $index) {
					if (array_key_exists($arrayTag, $singleRow)) {
						$loadMsgText = $singleRow[$arrayTag];

						$loadMsgText = str_replace('\n', '&percnt;n&percnt;', $loadMsgText);
						$loadMsgText = str_replace('\rank', '&percnt;rank&percnt;', $loadMsgText);
						$loadMsgText = str_replace('\mb_name', '&percnt;mb_name&percnt;', $loadMsgText);
						echo $loadMsgText;
						break;
					}
				} 
			}
		} else {
			echo $inMsg;
		}
	}

	function hs_ScoutBookLabelPrint_loadOutputType($xmlValues, $index, $inOutputType) {
		//global	$outputType;
		$section = 0;
		$selectedOutputType = $inOutputType;
		//echo " outputType: ".$inOutputType."<br>";
		foreach ($xmlValues as $singleRow) {
			if ($section == $index) {
				if ($singleRow != -1 ) {
					$singleRowIndex = $singleRow[HS_SBLP_OUTPUTTYPEID];
					$singleRowValue = $singleRow[HS_SBLP_OUTPUTTYPE];
					//echo ("Single:".$singleRow."<br>");
					if (strcmp($selectedOutputType, $singleRowIndex) == 0){
						$selected = 'selected="selected"';
					} else {
						$selected = '';
					}	
					echo "<Option value='".$singleRowIndex."' ".$selected." >".$singleRowValue."</Option>";
				} else {
					break;
				}
			} elseif ($singleRow == -1) {
				$section = $section + 1;
			}
		}
	}

	function hs_ScoutBookLabelPrint_loadStyle($inCookie) {
		$my_mediaType6570 = __('Avery 6570', 'hs_ScoutBookLabelPrint');
		$my_mediaType654936 = __('BSA Stock Form SKU 654936 (beta)', 'hs_ScoutBookLabelPrint');
		$my_mediaType33414 = __('BSA Stock Form SKU 33414 (beta)', 'hs_ScoutBookLabelPrint');
		
		// load label styles, $labelStyle = 6570;
		if (strcmp($inCookie, HS_LABELSTYLE_AVERY_6570) == 0) {
			echo ('<option value="'.HS_LABELSTYLE_AVERY_6570.'" selected="selected">'.$my_mediaType6570.'</option>');
		} else {
			echo ('<option value="'.HS_LABELSTYLE_AVERY_6570.'">'.$my_mediaType6570.'</option>');
		}
		// Beta Option to generate card
		//echo ('<option value="'.HS_LABELSTYLE_CARD.'">Pocket Certificate (beta)</option>');
		// Beta Option for created card
//		if (strcmp($inCookie, HS_LABELSTYLE_MeritBadge_SKU_654936) == 0) {
//			echo ('<option value="'.HS_LABELSTYLE_MeritBadge_SKU_654936.'" selected="selected">'.$my_mediaType654936.'</option>');
//		} else {
//			echo ('<option value="'.HS_LABELSTYLE_MeritBadge_SKU_654936.'">'.$my_mediaType654936.'</option>');
//		}
//		if (strcmp($inCookie, HS_LABELSTYLE_MeritBadge_SKU_33414) == 0) {
//			echo ('<option value="'.HS_LABELSTYLE_MeritBadge_SKU_33414.'" selected="selected">'.$my_mediaType33414.'</option>');
//		} else {
//			echo ('<option value="'.HS_LABELSTYLE_MeritBadge_SKU_33414.'">'.$my_mediaType33414.'</option>');
//		}
	}
	
	/* Function to load XML file which describes supported label format */
	function hs_ScoutBookLabelPrint_loadTemplate() {
		$generalXML = plugin_dir_path( __FILE__ )."include/Labels-templates.xml";
		$xml = simplexml_load_file($generalXML);
		$firstTag = $xml->getName();
	}

	function hs_ScoutBookLabelPrint_loadFonts($xmlValues, $inFontSize) {
		global	$fontSize;
		
		$selectedFont = $inFontSize;
		foreach ($xmlValues as $singleRow) {
			//echo ("Single:".$singleRow."<br>");
			$singleRowValue = $singleRow[HS_SBLP_FONT];
			//echo (" SF:".$selectedFont." RV:".$singleRowValue."<br>");
			if (strcmp($selectedFont, $singleRowValue) == 0){
				$selected = 'selected';
			} else {
				$selected = '';
			}	
			if ($singleRow != -1 ) {
				echo "<Option value='".$singleRowValue."' ".$selected." >".$singleRowValue."</Option>";
			} else {
				break;
			}
		}
	}

	// Function to get unit types and place in dropdown item.
	// Input param $xmlValues is multiple categories and selected based on order with
	// -1 as separator between groups
	function hs_ScoutBookLabelPrint_loadUnits ( $xmlValues, $unitType ) {
		$isUnitValue = FALSE;
		//$selectedUnit = $_COOKIE[HS_COOKIE_SBLP_UnitType];
		$selectedUnit = $unitType;
		foreach ($xmlValues as $singleRow) {
			//foreach($singleRow as $sIndex => $singleItem) {
			//	error_log("Index: ".$sIndex." Unit: ".$singleItem);
			//}
			if ($isUnitValue == TRUE && $singleRow != -1) { 
				//foreach($singleRow as $sIndex => $singleItem) {
				//	error_log("Index: ".$sIndex." Unit: ".$singleItem." unitType: ".$unitType);
				//}
				if (array_key_exists(HS_SBLP_UNIT, $singleRow)) {
					$singleRowValue = $singleRow[HS_SBLP_UNIT];
					if (strcmp($selectedUnit, $singleRowValue) == 0){
						$selected = 'selected';
					} else {
						$selected = '';
					}
					echo "<Option value='".$singleRowValue."' ".$selected." >".$singleRowValue."</Option>";
				}
			} elseif ($isUnitValue == TRUE && $singleRow == -1) {
				break;
			} elseif ($singleRow == -1) {
				$isUnitValue = TRUE;
			}
		}
	}
	
	function hs_ScoutBookLabelPrint_loadCookies() {

		//$cookieValues = array();

		$initialValue = HS_COOKIE_SBLP_INITIAL;
		$cookieValues = json_decode($initialValue, true);
		//var_dump($cookieValues);
		$timeExpires = time() + (60 * 60 * 24 * 365);
//		$cookieValue_JSON = json_encode($cookieValues);
		//echo "<br>".$cookieValue_JSON."<br>";
		//$cookieValue_Serial = JsonSerializable ($cookieValue_JSON);
		//echo "<br>".$cookieValue_Serial."<br>";
		if (isset($_COOKIE[HS_COOKIE_SB_JSON])) {
			unset($_COOKIE[HS_COOKIE_SB_JSON]);
		}
		//setcookie(HS_COOKIE_SB_JSON, $cookieValue_JSON, $timeExpires, '/');

		return $cookieValues;
	}

	function hs_ScoutBookLabel_getCookie () {
		global	$cookieValues;
		if (!session_id()) {
			session_start();
		}
		// Load default JSON parameters
		$initialValue = HS_COOKIE_SBLP_INITIAL;
		$cookieValues = json_decode($initialValue, true);
		// Check if Cookies are present 
		if (isset($_COOKIE[HS_COOKIE_SB_JSON]) && strlen($_COOKIE[HS_COOKIE_SB_JSON]) > 0) {
			// copy old cookies into array 
			$cookieValue_serial = $_COOKIE[HS_COOKIE_SB_JSON];
//			echo "CookieValue:".$cookieValue_serial.":end<br>";
			$cookieValue_strip = stripcslashes($cookieValue_serial);
			$browserValues = json_decode($cookieValue_strip, true);
			//error_log("Dump:".var_dump($browserValues));
			
//			echo "BrowserValue:".$browserValues.":end<br>";
			$IndexList = array_keys($browserValues);

			//3echo " getCookie:Browser:".$browserValues['SB_ADVANCEMENT_CARD']['DateFilter'].":<br>";
			foreach ($IndexList as $singleGroup) {
				foreach ($browserValues[$singleGroup] as $bvL => $bvV) {
					//error_log ("cookie00: ".$cookieValues[$singleGroup][$bvL].":+:".$bvL." => ".$bvV.": ");
					//error_log ("browse00: ".$browserValues[$singleGroup][$bvL].":+:".$bvL." => ".$bvV.":<br>");
					if ( strcmp($cookieValues[$singleGroup][$bvL], $browserValues[$singleGroup][$bvL]) != 0) {
						$cookieValues[$singleGroup][$bvL] = $browserValues[$singleGroup][$bvL];
					}
				}
			}
			
		//echo " flags-1:".$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'].":".$browserValues['SB_ADVANCEMENT_CARD']['DateFilter'].":<br>";
		//echo " flags-1:".$cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'].":".$browserValues['SB_ADVANCEMENT_CARD']['SortFilter'].":<br>";
		//echo " flags-1:".$cookieValues['SB_ADVANCEMENT_CARD']['TitleFilter'].":".$browserValues['SB_ADVANCEMENT_CARD']['TitleFilter'].":<br>";


			}
			if (isset($_COOKIE[HS_COOKIE_SB_JSON])) {
				// Save cookies into session variable
				$_SESSION[HS_COOKIE_SB_JSON] = $_COOKIE[HS_COOKIE_SB_JSON];
			}
//		}
		
		// Check for presents 
		if (isset($_COOKIE[HS_COOKIE_SBLP_LabelStyle]) && strlen($_COOKIE[HS_COOKIE_SBLP_LabelStyle]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['LabelStyle'] = $_COOKIE[HS_COOKIE_SBLP_LabelStyle];
			unset($_COOKIE[HS_COOKIE_SBLP_LabelStyle]);
			setcookie(HS_COOKIE_SBLP_LabelStyle, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_UnitType]) && strlen($_COOKIE[HS_COOKIE_SBLP_UnitType]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['UnitType'] = $_COOKIE[HS_COOKIE_SBLP_UnitType];
			unset($_COOKIE[HS_COOKIE_SBLP_UnitType]);
			setcookie(HS_COOKIE_SBLP_UnitType, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_UnitNumber]) && strlen($_COOKIE[HS_COOKIE_SBLP_UnitNumber]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['UnitNumber'] = $_COOKIE[HS_COOKIE_SBLP_UnitNumber];
			unset($_COOKIE[HS_COOKIE_SBLP_UnitNumber]);	
			setcookie(HS_COOKIE_SBLP_UnitNumber, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_FontSize]) && strlen($_COOKIE[HS_COOKIE_SBLP_FontSize]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['FontSize'] = $_COOKIE[HS_COOKIE_SBLP_FontSize];
			unset($_COOKIE[HS_COOKIE_SBLP_FontSize]);
			setcookie(HS_COOKIE_SBLP_FontSize, "", time() - 3600);
		}		
		if (isset($_COOKIE[HS_COOKIE_SBLP_LabelPosition]) && strlen($_COOKIE[HS_COOKIE_SBLP_LabelPosition]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['LabelPosition'] = $_COOKIE[HS_COOKIE_SBLP_LabelPosition];
			unset($_COOKIE[HS_COOKIE_SBLP_LabelPosition]);
			setcookie(HS_COOKIE_SBLP_LabelPosition, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_Council]) && strlen($_COOKIE[HS_COOKIE_SBLP_Council]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['Council'] = $_COOKIE[HS_COOKIE_SBLP_Council];
			unset($_COOKIE[HS_COOKIE_SBLP_Council]);
			setcookie(HS_COOKIE_SBLP_Council, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_RankMsg]) && strlen($_COOKIE[HS_COOKIE_SBLP_RankMsg]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['RankMessage'] = $_COOKIE[HS_COOKIE_SBLP_RankMsg];
			unset($_COOKIE[HS_COOKIE_SBLP_RankMsg]);
			setcookie(HS_COOKIE_SBLP_RankMsg, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_MBMsg]) && strlen($_COOKIE[HS_COOKIE_SBLP_MBMsg]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['MeritBadgeMessage'] = $_COOKIE[HS_COOKIE_SBLP_MBMsg];
			unset($_COOKIE[HS_COOKIE_SBLP_MBMsg]);
			setcookie(HS_COOKIE_SBLP_MBMsg, "", time() - 3600);
		}
		if (isset($_COOKIE[HS_COOKIE_SBLP_OutputType]) && strlen($_COOKIE[HS_COOKIE_SBLP_OutputType]) > 0) {
			$cookieValues['SB_LABEL_PRINT']['OutputContent'] = $_COOKIE[HS_COOKIE_SBLP_OutputType];
			unset($_COOKIE[HS_COOKIE_SBLP_OutputType]);
			setcookie(HS_COOKIE_SBLP_OutputType, "", time() - 3600);
		}
		// Save cookies into session variable
		$_SESSION[HS_COOKIE_SB_JSON] = $cookieValues;
		
		// Clear space holders
		$cookieValues['SB_LABEL_PRINT']['RankMessage'] = str_replace('+',' ',$cookieValues['SB_LABEL_PRINT']['RankMessage']);
		$cookieValues['SB_LABEL_PRINT']['MeritBadgeMessage'] = str_replace('+', ' ', $cookieValues['SB_LABEL_PRINT']['MeritBadgeMessage']);
		//3echo " getCookie:".$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'].":";
	}

	// Routine to accept parameters for label creations
	public function hs_ScoutBookLabelPrint_code ( $attr ) {
		global	$cookieValues;
		
		$this->hs_ScoutBookLabel_getCookie();
		
		//$cookieValues = array();
		$unitNumber = "";
		$meritBadgesAbbr = array();

		/*
		<Label-rectangle id="0" width="126pt" height="90pt" round="4.464pt" x_waste="6.768pt" y_waste="0pt">
		<Markup-margin size="9pt"/>
		<Layout nx="4" ny="8" x0="36pt" y0="36pt" dx="139.536pt" dy="90pt"/>
		</Label-rectangle>		
		36 + 126 + 6.768 + 126 + 6.768 + 126 + 6.768 + 126 + 36  = ? width
	
		36 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 36  = ? Height

		Name
		Message
		Merit Badge MB
		Councilname
		Troop 0008 Date mm/dd/yy
		*/

		$xmlValues = $this->hs_ScoutBookLabelPrint_loadXML();
		/* load XML data for supported labels, Currently not used */
		$xmlLabelTemplates = $this->hs_ScoutBookLabelPrint_loadTemplate();
		$meritBadgesAbbr = $this->hs_ScoutBookLabelPrint_loadMB($xmlValues);
				
		// Display list of available text 
		$my_listTitle = __('ScoutBook Label Print', 'hs_ScoutBookLabelPrint');
		$my_buttonValue = __('Load CSV', 'hs_ScoutBookLabelPrint');
		$my_scoutBookCsv = __('Scout Book CSV:', 'hs_ScoutBookLabelPrint');
		$my_labelStyle = __('Label Style:', 'hs_ScoutBookLabelPrint');
		$my_unitType = __('Unit Type:', 'hs_ScoutBookLabelPrint');
		$my_unitNum = __('Unit Number:', 'hs_ScoutBookLabelPrint');
		$my_fontSize = __('Font Size:', 'hs_ScoutBookLabelPrint');
		$my_labelPos = __('Label Position:', 'hs_ScoutBookLabelPrint');
		$my_council = __('Council:', 'hs_ScoutBookLabelPrint');
		$my_rankMsg = __('Rank Message', 'hs_ScoutBookLabelPrint');
		$my_mbMsg = __('MeritBadge Message', 'hs_ScoutBookLabelPrint');
		$my_LabelLayout = __('Label Layout', 'hs_ScoutBookLabelPrint');
		$my_outputType = __('Output Content', 'hs_ScoutBookLabelPrint');

		$label_Columns = 4;
		$label_Rows = 8;

		// Load standard value or cookie
		$unitNumber = $cookieValues['SB_LABEL_PRINT']['UnitNumber'];
		$unitType = $cookieValues['SB_LABEL_PRINT']['UnitType'];
		$labelPos = $cookieValues['SB_LABEL_PRINT']['LabelPosition'];
		$councilName = $cookieValues['SB_LABEL_PRINT']['Council'];
		$rankMsg = $cookieValues['SB_LABEL_PRINT']['RankMessage'];
		$mbMsg = $cookieValues['SB_LABEL_PRINT']['MeritBadgeMessage'];
		$outputType = $cookieValues['SB_LABEL_PRINT']['OutputContent'];

		echo ('<h2>'.$my_listTitle.'</h2>');
		echo('<form id="form" target="_Blank" method="POST" onsubmit="return openForm(this.id)" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">');
		wp_nonce_field('HS_SCOUTBOOK_FORM', 'HS_SETTINGS_NONCE', true, true);
		echo('<input type="hidden" name="action" value="hs_ScoutBookLabelPrint_Result">');
		echo('<input type="hidden" name="numCol" value="'.$label_Columns.'" />');
		echo('<input type="hidden" name="numRow" value="'.$label_Rows.'" />');
		// Build Table of controls
		echo('<div class="divTable grayTable">');
		echo('<div class="divTableBody">');
			echo('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_scoutBookCsv.'</div>');
				echo ('<div class="divTableCell"><input type="file" name="csv_file" accept=".csv" /></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_labelStyle.'</div>');
				echo ('<div class="divTableCell"><select name="LabelStyle">');
				$this->hs_ScoutBookLabelPrint_loadStyle($cookieValues['SB_LABEL_PRINT']['LabelStyle']);
				echo('</select></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_unitType.'</div>');
				echo ('<div class="divTableCell"><select name="UnitReportType">');
					$this->hs_ScoutBookLabelPrint_loadUnits($xmlValues, $unitType);
				echo ('</select></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_unitNum.'</div>');
				echo ('<div class="divTableCell"><Input type="text" name="UnitNumber" value="'.$unitNumber.'"></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_fontSize.'</div>');
				echo ('<div class="divTableCell"><select name="FontSize">');
				$fontValue = $cookieValues['SB_LABEL_PRINT']['FontSize'];
				//echo " before Font::".$fontValue."::<br>";
				$this->hs_ScoutBookLabelPrint_loadFonts($xmlValues, $cookieValues['SB_LABEL_PRINT']['FontSize']);
				echo ('</select></div>');
			echo ('</div>');
				echo('
				<div class="form-popup" id="myForm">
					<form action="" class="form-container">
					<h3>Label Layout</h3>
					<div class="divTable grayTable">
					<div class="divTableBody">');
					$position = 1;
					for ($i = 0; $i < $label_Rows; $i++) {
						echo ('<div class="divTableRow">');
						for ($j = 0; $j < $label_Columns; $j++) {
							echo ('<div class="divTableCell">'.$position++.'</div>');
						}
						echo ('</div>');
					}
					echo ('</div>
						   </div>
					
					<input type="hidden" name="numRow2" value="2" />
					<div class="divTableRow">
					<div class="divTableCell">
					<button type="button" class="btn cancel" onclick="closeForm()">Close</button>
					</div></div>
					</form>
				</div>');

			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_labelPos.'</div>');
				echo ('<div class="divTableCell"><select name="LabelPos">');
				$this->hs_ScountBookLabelPrint_labelCount($label_Columns, $label_Rows, $labelPos);
				echo ('</select><button class="open-button" onclick="return openForm(this.id)" id="LabelButton" value="LabelButton">'.$my_LabelLayout.'</button></div>');

			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_council.'</div>');
				echo ('<div class="divTableCell"><select name="CouncilName">');
				$this->hs_ScoutBookLabelPrint_loadCouncil($councilName);
				echo ('</select></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_rankMsg.'</div>');
				echo ('<div class="divTableCell"><Input type="text" name="rankMsg" value="');
				$this->hs_ScoutBookLabelPrint_loadMsg($xmlValues, 2, $rankMsg, HS_SBLP_RANKMSG);
				echo ('"></select></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_mbMsg.'</div>');
				echo ('<div class="divTableCell"><Input type="text" name="mbMsg" value="');
				$this->hs_ScoutBookLabelPrint_loadMsg($xmlValues, 3, $mbMsg, HS_SBLP_MBMSG);
				echo ('"></select></div>');
			echo ('</div>');
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell" align="right">'.$my_outputType.'</div>');
				echo ('<div class="divTableCell"><select name="OutputType">');
				$this->hs_ScoutBookLabelPrint_loadOutputType($xmlValues, 6, $outputType);
				echo ('</select></div>');
			echo ('</div>');			
			echo ('<div class="divTableRow">');
				echo ('<div class="divTableCell"></div>');
				echo ('<div class="divTableCell"><input type="submit" name="scoutbook-form-submit" id="submit" value="'.$my_buttonValue.'" class="button button-primary"/></div>');
			echo ('</div>');
		echo('</div>');
		echo('</div>');		
		echo('</form>');

	}

	function hs_ScoutBookLabelPrint_Result() {
		global	$cookieValues;
		
		global	$colNum;
		global	$fontSize;
		global	$labelRow;
		global	$numRow;
		global	$numCol;
		global	$lineLabels;
		global	$linesCards;
		global	$pdf;
		global	$lineIsInProgress;
		//global	$list_awardName;
		global	$csvFileName_sanitize;
		global	$pageSize;
		
		// Define string variable so it has a starting value
		$my_labelDate = __('Date',  'hs_ScoutBookLabelPrint');
		$my_InputFileMissing = __('<h2>Input file not provided to generate labels.</h2>', 'hs_ScoutBookLabelPrint');
		$my_SecurityCheck = __('<h2>Security Check</h2>',  'hs_ScoutBookLabelPrint');
		
		// Keywords 
		$keywords = "ScoutBook";
		if (isset($_POST['scoutbook-form-submit'])) {
			/*
			<Label-rectangle id="0" width="126pt" height="90pt" round="4.464pt" x_waste="6.768pt" y_waste="0pt">
		 	  <Markup-margin size="9pt"/>
			  <Layout nx="4" ny="8" x0="36pt" y0="36pt" dx="139.536pt" dy="90pt"/>
			</Label-rectangle>		
			36 + 126 + 6.768 + 126 + 6.768 + 126 + 6.768 + 126 + 36  = ? width
	
			36 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 0 + 90 + 36  = ? Height

			Name
			Message
			Merit Badge MB
			Councilname
			Troop 0000 Date mm/dd/yy
			*/			
			// Test if NONCE value is set and valid for form
	//		if (isset($_POST['HS_SETTINGS_NONCE']) && wp_verify_nonce($_POST['HS_SETTINGS_NONCE'], 'HS_SCOUTBOOK_FORM') != false) {

				$xmlValues = $this->hs_ScoutBookLabelPrint_loadXML();
				
				if (isset($_FILES['csv_file']) && strlen($_FILES['csv_file']["tmp_name"])>0) {
					//$santized_Filename = sanitize_file_name( $_FILES["csv_file"]["tmp_name"] );
					if (isset($_POST['CouncilName'])) {
						$councilName = sanitize_text_field($_POST['CouncilName']);
						$cookieValues ['SB_LABEL_PRINT']['Council'] = $councilName;
					} else {
						$councilName = "";
						$cookieValues ['SB_LABEL_PRINT']['Council'] = $councilName;
					}
					if (isset($_POST['UnitReportType'])) {
						$unitType = sanitize_text_field($_POST['UnitReportType']);
						$cookieValues ['SB_LABEL_PRINT']['UnitType'] = $unitType;
					} else {
						$unitType = "";
						$cookieValues ['SB_LABEL_PRINT']['UnitType'] = $unitType;
					}
					if (isset($_POST['UnitNumber']) && is_numeric($_POST['UnitNumber'])) {
						$unitNumber = sanitize_text_field($_POST['UnitNumber']);
						$cookieValues ['SB_LABEL_PRINT']['UnitNumber'] = $unitNumber;
					} else {
						$unitNumber = "";
						$cookieValues ['SB_LABEL_PRINT']['UnitNumber'] = $unitNumber;
					}
					
					if (isset($_POST['LabelStyle']) && is_numeric($_POST['LabelStyle'])) {
						$labelStyle = sanitize_text_field($_POST['LabelStyle']);
					} elseif (isset($_POST['LabelStyle']) && 
						(strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_654936) == 0 || 
						 strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_33414) == 0)) {
						$labelStyle = sanitize_text_field($_POST['LabelStyle']);
						$cookieValues ['SB_LABEL_PRINT']['LabelStyle'] = $labelStyle;
					} else {
						$labelStyle = HS_LABELSTYLE_AVERY_6570;
						$cookieValues ['SB_LABEL_PRINT']['LabelStyle'] = $labelStyle;
					}
					
					if (isset($_POST['LabelPos']) && is_numeric($_POST['LabelPos'])) {
						$labelPos = sanitize_text_field($_POST['LabelPos']);
						$cookieValues ['SB_LABEL_PRINT']['LabelPosition'] = $labelPos;
					} else {
						$labelPos = '1';
						$cookieValues ['SB_LABEL_PRINT']['LabelPosition'] = $labelPos;
					}
					if (isset($_POST['FontSize']) && is_numeric($_POST['FontSize'])) {
						$fontSize = sanitize_text_field($_POST['FontSize']);
						$cookieValues ['SB_LABEL_PRINT']['FontSize'] = $fontSize;
					} else {
						$fontSize = sanitize_text_field('9');
						$cookieValues ['SB_LABEL_PRINT']['FontSize'] = $fontSize;
					}
					if (isset($_POST['numRow']) && is_numeric($_POST['numRow'])) {
						// Specal case presentation card are 4 col by 2 row
						if ($labelStyle == HS_LABELSTYLE_CARD) {
							$numRow = 2;
						} else {
							$numRow = sanitize_text_field($_POST['numRow']);
						}
					} else {
						$numRow = '8';
					}
					if (isset($_POST['numCol']) && is_numeric($_POST['numCol'])) {
						// Specal case presentation card are 4 col by 2 row
						if ($labelStyle == HS_LABELSTYLE_CARD) {
							$numCol = 4;
						} else {
							$numCol = sanitize_text_field($_POST['numCol']);
						}
					} else {
						$numCol = '4';
					}
					if (isset($_POST['rankMsg']) && is_string($_POST['rankMsg'])) {
						$rankMsg = sanitize_text_field($_POST['rankMsg']);
						$cookieValues ['SB_LABEL_PRINT']['RankMessage'] = $rankMsg;
					} else {
						$rankMsg = "";
						$cookieValues ['SB_LABEL_PRINT']['RankMessage'] = $rankMsg;
					}
					if (isset($_POST['mbMsg']) && is_string($_POST['mbMsg'])) {
						$mbMsg = sanitize_text_field($_POST['mbMsg']);
						$cookieValues ['SB_LABEL_PRINT']['MeritBadgeMessage'] = $mbMsg;
					} else {
						$mbMsg = "";
						$cookieValues ['SB_LABEL_PRINT']['MeritBadgeMessage'] = $mbMsg;
					}
					if (isset($_POST['OutputType']) && is_numeric($_POST['OutputType'])) {
						$outputType = sanitize_text_field($_POST['OutputType']);
						$cookieValues ['SB_LABEL_PRINT']['OutputContent'] = $outputType;
					} else {
						$outputType = "";
						$cookieValues ['SB_LABEL_PRINT']['OutputContent'] = $outputType;
					}
					
					$row = 0;
					$colNum = 0;
					$labelRow = 0;
					$currentScount = "";
				
					// Remove old cookies
					setcookie(HS_COOKIE_SBLP_LabelStyle, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_UnitType, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_UnitNumber, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_FontSize, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_LabelPosition, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_Council, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_RankMsg, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_MBMsg, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_OutputType, "", time() - 3600);

					// Create Cookies for selections
					$cookieValues_string = json_encode($cookieValues);
					$timeExpires = time() + (60 * 60 * 24 * 365);
					setcookie(HS_COOKIE_SB_JSON, $cookieValues_string, $timeExpires, '/');
					
					$meritBadgesAbbr = $this->hs_ScoutBookLabelPrint_loadMB($xmlValues);
					$councilName = sanitize_text_field($_POST["CouncilName"]);

					// Determine page Orientation
					if (isset($_POST['LabelStyle']) && 
						strcmp($_POST['LabelStyle'],HS_LABELSTYLE_CARD) == 0 && 
						$outputType != HS_XML_LISTONLY) {
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_LANDSCAPE;
					} if (isset($_POST['LabelStyle']) && (
						strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_654936) == 0 ||
						strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_33414) == 0) &&
						($outputType == HS_XML_LABELLIST || $outputType == HS_XML_LABELONLY) ) {
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_LANDSCAPE;
					} else {
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
					}

					// Open PDF file for labels
					$pdf = new hs_ScoutBookLabelPrint_pdf($pageOrientation,TCPDF_PDF_UNIT_PT, TCPDF_PDF_LETTER);
					$pdf->SetTitle('HoweScape ScoutBook Label Print');
					$pdf->SetAuthor('http://HoweScape.com');
					$csvFileName_sanitize = sanitize_file_name($_FILES["csv_file"]["name"]);
					$pdf->SetTitleFileName($csvFileName_sanitize);
					$pdf->SetSubject($csvFileName_sanitize);
					$pdf->SetKeywords($keywords);
					$pdf->SetCreator("HoweScape ScoutBook LabelPrint");
					if (isset($_POST['LabelStyle']) &&
										strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_654936) == 0) {
						$selectedPageType = Extension_PageType_BSAStock;				
						$pdf->SetMargins(HS_PDF_SETTING_BSASTOCK_MARGIN_LEFT, 
										HS_PDF_SETTING_BSASTOCK_MARGIN_TOP, 
										HS_PDF_SETTING_BSASTOCK_MARGIN_RIGHT); // PT
					} else {
						$selectedPageType = Extension_PageType_PlainPaper;
						$pdf->SetMargins(HS_PDF_SETTING_MARGIN_LEFT, 
										HS_PDF_SETTING_MARGIN_TOP, 
										HS_PDF_SETTING_MARGIN_RIGHT); // PT
					}					
					$pdf->SetPageType($selectedPageType);

					$pdf->AddPage($pageOrientation,TCPDF_PDF_LETTER);
					$pdf->SetFont(HS_PDF_SETTING_FONT,HS_PDF_SETTING_BOLD,$fontSize);
				
					$pdf->SetAutoPageBreak(false);

					$lineLabels = array();
					$linesCards = array();
					$lineIsInProgress = false;
					
					// File name generated by code on server.
					$csvFileTmp_sanitize = $_FILES["csv_file"]["tmp_name"];
					if (($handle = fopen($csvFileTmp_sanitize,"r")) !== FALSE) {
						
						while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
							$num = count($data);
							//echo "<p> $num fields in line $row: <br /></p>\n";
							$row++;
						
							if ($row > 1) {
								// Store infor in array for list page
								$date = date_create($data[8]);		// I
								$formattedDate = date_format($date, "m/d/y");
								$itemNameAbbr = $this->hs_ScoutBookLabelPrintMBAbbr($data[7], $meritBadgesAbbr);
								$unitTypeNumDate = $unitType.' '.$unitNumber.' '.$my_labelDate.' '.$formattedDate;
								$itemType = $data[5];		// F
								$scoutName = $data[0].' '.$data[1];		// A and B								
								$labelMsgArray = $this->hs_ScoutBookLabelBuildMsg($itemType, $itemNameAbbr, $mbMsg, $rankMsg);
								$labelMsg = $labelMsgArray[HS_SBLP_ABBR];
								// Presentation Card msg
								$itemName = str_replace(HS_MSG_MB_EMBLEM, '', $itemNameAbbr);
								$itemName = trim($itemName[HS_SBLP_ABBR]);

								// Build array of info
								$list_PO_record[] = array(HS_ARRAY_FirstName => $data[0], 	// A
														  HS_ARRAY_LastName => $data[1],	// B
														  HS_ARRAY_ItemType => $itemType,	// F
														  HS_ARRAY_ItemName => $data[7],	// H
														  HS_ARRAY_ItemName_ABBR => $itemNameAbbr,
														  HS_ARRAY_DateEarned => $data[8],	// I
														  HS_ARRAY_DateFormated => $formattedDate,
														  HS_ARRAY_ScoutName => $scoutName,
														  HS_ARRAY_LabelLines => $scoutName.HS_MSG_NEWLINE.
																				$labelMsg.HS_MSG_NEWLINE.
																				$councilName.HS_MSG_NEWLINE.
																				$unitTypeNumDate,
														  HS_ARRAY_CardLines => HS_MSG_NEWLINE.HS_MSG_NEWLINE.
																				$scoutName.HS_MSG_NEWLINE.
																				$itemName.HS_MSG_NEWLINE.
																				$councilName.HS_MSG_NEWLINE.
																				$unitTypeNumDate);
							}
						} 
						if ($lineIsInProgress == true) {
							$this->hs_ScoutBookLabelPrint_placeRowLabels($outputType);
							// Add blank entry
							$list_PO_record[] = array(HS_ARRAY_LabelLines => '', HS_ARRAY_CardLines => '');
							//$lineLabels = array();
							//$linesCards = array();
							$col = 0;
							$pdf->Ln();
							$labelRow = $labelRow + 1;
						}

						fclose($handle);						
						// Done reading data print labels/cards
						// print labels or cards
						if ($outputType == HS_XML_LABELLIST || $outputType == HS_XML_LABELONLY) {
							// Choose what gets printed
							if (isset($_POST['LabelStyle']) && 
								strcmp($_POST['LabelStyle'],HS_LABELSTYLE_CARD) == 0) {
									$cardHeight = 262;	// 3.5 inch === 252 pt
									$cardWidth = 184; // 2.5 inch === 180 pt
									$mbWidth = 54;	// 1.5 inch === 108 pt
									$mbHeight = 54;
									$graphicBadgeList = $this->HS_ScoutBookLabelPrint_loadBadgeGraphics();

									foreach ($list_PO_record as $singleCardLines) {
										$this->hs_ScoutBookLabelPrint_singleCard($singleCardLines[HS_ARRAY_CardLines], $pdf, $pageOrientation, $graphicBadgeList);
										$colNum = $colNum + 1;	// Increment counter
										if ($colNum % 4 != 0) {	// Move to next position on line
											$x = $pdf->GetX();
											$y = $pdf->GetY();
											//$pdf->multiCell($w_waste, $h_waste, "  ", 0, "C");
											$pdf->SetXY($x+$cardWidth, $y);
											//$pdf->SetXY($x, $y);
										}
										if ($colNum % 4 == 0) {	// Move to next line
											$pdf->Ln();
											$labelRow = $labelRow + 1;
											$x = $pdf->GetX();
											$y = $pdf->GetY();
											//$pdf->multiCell($w_waste, $h_waste, "  ", 0, "C");
											$pdf->SetXY($x, $y+$cardHeight);
											if ($labelRow % 2 == 0) {
												$pdf->AddPage($pageOrientation,TCPDF_PDF_LETTER);
											}
										}
									}

							} else if (isset($_POST['LabelStyle']) && 
								strcmp($_POST['LabelStyle'],HS_LABELSTYLE_CARD_CREATED) == 0) {
								// Use created car
							} else if (isset($_POST['LabelStyle']) &&
										strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_33414) == 0) {
								// Create a PDF file with filled in fields to print over stock
								if (strcmp($outputType, HS_XML_LABELONLY) == 0 || strcmp($outputType, HS_XML_LABELLIST) == 0) {
									$colNum = 0;
									$cardRow = 2;
									$rowNum = 0;
									$cardHeight = 270;	// 3.75 inch === 270 pt
									$cardWidth = 180; // 2.5 inch === 180 pt
									$origin_x = $pdf->GetX();
									$origin_y = $pdf->GetY();
									$pageCol = HS_PDF_LETTER_LANDSCAPE_COLS;
									$pageRow = HS_PDF_LETTER_LANDSCAPE_ROWS;
									// Settings for page
									// set default form properties
									// IMPORTANT: disable font subsetting to allow users editing the document
									$pdf->setFontSubsetting(false);
									$pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 255), 'strokeColor'=>array(255, 255, 255)));
									$formCounter = 0;
									// create PDF file in Landscape
									// Name
									// Award
									// Date  Unit
									// Council
									// 4 columns by 2 Rows
									foreach ( $list_PO_record as $singleRecord) {
										//error_log("Type1 :=: ".print_r($singleRecord[HS_ARRAY_ItemType], true). " :: ");
										if (strcmp((string)$singleRecord[HS_ARRAY_ItemType], HS_CSV_MERIT_BADGES) == 0) {
											//error_log("Type2 :=: ".print_r($singleRecord[HS_ARRAY_ItemType], true). " :: ");
											// Call feature to insert single card
											$formCounter = $formCounter + 1;
											$this->hs_ScoutBook_CardForm33414($pdf, $singleRecord, $origin_x, $origin_y, $unitType, $unitNumber, $councilName, $formCounter);
											$colNum = $colNum + 1;
											if ($colNum % $pageCol != 0) {
												$origin_x1 = $origin_x + ($cardWidth * $colNum);
											} else {
												//echo("<br> colNum:".$colNum." : ".$rowNum." : ".$colNum % HS_PDF_POSTER_PORTRATE_COLS);
												$rowNum = $rowNum + 1;
												$origin_x = $origin_x - ($cardWidth * $pageCol);
												$origin_x1 = $origin_x + ($cardWidth * $colNum);
												$origin_y = $origin_y + $cardHeight;
											}
											if (($colNum % ($pageCol * $pageRow))  == 0) {
												$pdf->AddPage($pageOrientation,$pageSize);
												//$origin_y = $origin_y - ($cardHeight * $rowNum);
												$origin_x1 = $pdf->GetX();
												$origin_y = $pdf->GetY();
											}							
											//$origin_y1 = $origin_y1 + $cardHeight;
											$pdf->SetXY($origin_x1, $origin_y);
										}
									}

								}											
							} else if (isset($_POST['LabelStyle']) &&
										strcmp($_POST['LabelStyle'],HS_LABELSTYLE_MeritBadge_SKU_654936) == 0) {
									//error_log("Type0 :=: ". " :: ");
								// Create a PDF file with filled in fields to print over stock
								if (strcmp($outputType, HS_XML_LABELONLY) == 0 || strcmp($outputType, HS_XML_LABELLIST) == 0) {
									$colNum = 0;
									$cardRow = 2;
									$rowNum = 0;
									$cardHeight = 270;	// 3.75 inch === 270 pt
									$cardWidth = 180; // 2.5 inch === 180 pt
									$origin_x = $pdf->GetX();
									$origin_y = $pdf->GetY();
									$pageCol = HS_PDF_LETTER_LANDSCAPE_COLS;
									$pageRow = HS_PDF_LETTER_LANDSCAPE_ROWS;
									// Settings for page
									// set default form properties
									// IMPORTANT: disable font subsetting to allow users editing the document
									$pdf->setFontSubsetting(false);
									$pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
									$formCounter = 0;
									// create PDF file in Landscape
									// Name
									// Award
									// Date  Unit
									// Council
									// 4 columns by 2 Rows
									foreach ( $list_PO_record as $singleRecord) {
										//error_log("Type1 :=: ".print_r($singleRecord[HS_ARRAY_ItemType], true). " :: ");
										if (strcmp((string)$singleRecord[HS_ARRAY_ItemType], HS_CSV_MERIT_BADGES) == 0) {
											//error_log("Type2 :=: ".print_r($singleRecord[HS_ARRAY_ItemType], true). " :: ");
											// Call feature to insert single card
											$formCounter = $formCounter + 1;
											$this->hs_ScoutBook_CardForm654936($pdf, $singleRecord, $origin_x, $origin_y, $unitType, $unitNumber, $councilName, $formCounter);
											$colNum = $colNum + 1;
											if ($colNum % $pageCol != 0) {
												$origin_x1 = $origin_x + ($cardWidth * $colNum);
											} else {
												//echo("<br> colNum:".$colNum." : ".$rowNum." : ".$colNum % HS_PDF_POSTER_PORTRATE_COLS);
												$rowNum = $rowNum + 1;
												$origin_x = $origin_x - ($cardWidth * $pageCol);
												$origin_x1 = $origin_x + ($cardWidth * $colNum);
												$origin_y = $origin_y + $cardHeight;
											}
											if (($colNum % ($pageCol * $pageRow))  == 0) {
												$pdf->AddPage($pageOrientation,$pageSize);
												//$origin_y = $origin_y - ($cardHeight * $rowNum);
												$origin_x1 = $pdf->GetX();
												$origin_y = $pdf->GetY();
											}							
											//$origin_y1 = $origin_y1 + $cardHeight;
											$pdf->SetXY($origin_x1, $origin_y);
										}
									}

								}
							} else {
								// print blank labels
								// Check if labels should be sent to PDF 
								if ($outputType == HS_XML_LABELLIST || $outputType == HS_XML_LABELONLY) {
									$colNum = 0;
									// Add Blank blocks
									for ($i = 1; $i < $labelPos; $i++) {
										$blankLabel = array(HS_ARRAY_FirstName => HS_LABELSTYLE_FILLER, 	// A
														  HS_ARRAY_LastName => HS_LABELSTYLE_FILLER,	// B
														  HS_ARRAY_ItemType => HS_LABELSTYLE_FILLER,	// F
														  HS_ARRAY_ItemName => HS_LABELSTYLE_FILLER,	// H
														  HS_ARRAY_ItemName_ABBR => HS_LABELSTYLE_FILLER,
														  HS_ARRAY_DateEarned => HS_LABELSTYLE_FILLER,	// I
														  HS_ARRAY_DateFormated => HS_LABELSTYLE_FILLER,
														  HS_ARRAY_ScoutName => HS_LABELSTYLE_FILLER,
														  HS_ARRAY_LabelLines => " ".HS_MSG_NEWLINE.
																					" ".HS_MSG_NEWLINE.
																					" ".HS_MSG_NEWLINE.														
																					" ".HS_MSG_NEWLINE,
														  HS_ARRAY_CardLines => HS_LABELSTYLE_FILLER);

										array_unshift($list_PO_record, $blankLabel);
									}
								}							
								// Print labels
								$w = 128; //126; // pt
								$h = 64; //2; //17;
								$stickerHeight = 90.1; 87; //90; // PT
								// Labels
								foreach ($list_PO_record as $singleLabelLines) {

									$this->hs_ScoutBookLabelPrintSingle($singleLabelLines[HS_ARRAY_LabelLines], $pdf, $pageOrientation);
									$colNum = $colNum + 1;	// Increment counter
									if ($colNum % $numCol == 0) {	// Move to next line
										$pdf->Ln(2);
										$labelRow = $labelRow + 1;
										$x = $pdf->GetX();
										$y = $pdf->GetY();
										//$pdf->multiCell($w_waste, $h_waste, "  ", 0, "C");
										$pdf->SetXY($x, $y+$stickerHeight);
										if ($labelRow % $numRow == 0) {
											$pdf->AddPage($pageOrientation,TCPDF_PDF_LETTER);
										}
									}
								}
							}
						}
						// print lists if requested
						if ($outputType == HS_XML_LABELLIST || $outputType == HS_XML_LISTONLY) {

							$selectedPageType = Extension_PageType_PlainPaper;
							$pdf->SetMargins(HS_PDF_SETTING_MARGIN_LEFT, 
											HS_PDF_SETTING_MARGIN_TOP, 
											HS_PDF_SETTING_MARGIN_RIGHT); // PT
							
							// Add list at end of labels
							$currentScout = '';
							$lineHeight = 15;
							$lineCount = 0;

							if ($outputType !=  HS_XML_LISTONLY) { // Put list on a new page
								$pdf->AddPage(HS_PDF_SETTING_ORIENTATION_PORTRAIT, TCPDF_PDF_LETTER);
								$lineCount = 0;
							}
							$currentScout = '';
							$countArray = array();
							foreach ($list_PO_record as $singleKey => $singleItemList) {
								//error_log(" item:".$singleItemList[HS_ARRAY_ScoutName]." Key: ".$singleKey);
								// Test to skip filler entries added for empty label positions
								if (strcmp(HS_LABELSTYLE_FILLER, (string)$singleItemList[HS_ARRAY_FirstName]) != 0) {								
								
									$numLinesForScout = $this->hs_ScoutBookLabelPrint_listLineCount ($list_PO_record, $singleItemList[HS_ARRAY_ScoutName]);
									//error_log("   ListMath :=: ".print_r($numLinesForScout, true). " + ".print_r($lineCount, true).' = '.print_r($lineCount + $numLinesForScout, true));
									if ($lineCount + $numLinesForScout >= 46 && strcmp($currentScout, (string)$singleItemList[HS_ARRAY_ScoutName]) != 0) {
										$pdf->AddPage(HS_PDF_SETTING_ORIENTATION_PORTRAIT, TCPDF_PDF_LETTER);
										$lineCount = 0;
									}
									if (strcmp($currentScout, (string)$singleItemList[HS_ARRAY_ScoutName]) != 0) {
										$currentScout = (string)$singleItemList[HS_ARRAY_ScoutName];
										$this->hs_ScoutBookLabel_printListScout ($list_PO_record, $currentScout, $lineCount, $numLinesForScout, $lineHeight, $countArray);
										$lineCount = $lineCount + $numLinesForScout;
									}
								}
							}

							// Count of type of card label
							$countArray = array(HS_LABEL_COUNT_MERITBADGE => 0,
												HS_LABEL_COUNT_SCOUT => 0,
												HS_LABEL_COUNT_TENDERFOOT => 0,
												HS_LABEL_COUNT_SECONDCLASS => 0,
												HS_LABEL_COUNT_FIRSTCLASS => 0,
												HS_LABEL_COUNT_STAR => 0,
												HS_LABEL_COUNT_LIFE => 0,
												HS_LABEL_COUNT_EAGLE => 0,
												HS_LABEL_COUNT_OTHER => 0);
							$pdf->AddPage($pageOrientation,$pageSize);
							// Determine how many sheets 
							$this->hs_ScoutBookLabel_CardSheetCount ($countArray, $list_PO_record);
							$pdf->AddPage($pageOrientation,$pageSize);
							$this->hs_ScoutBookLabel_dialogSettings();

							
						}

						// End of lists
						$my_outputFileExt = __('PDF', 'hs_ScoutBookLabelPrint');
						$outputNameSan = sanitize_file_name($_FILES["csv_file"]["name"]);
						$outputName = pathinfo($outputNameSan,PATHINFO_BASENAME);
						$outputNameExt = pathinfo($outputName,PATHINFO_EXTENSION);
						$outputNameExtPos = strripos($outputName, $outputNameExt);
						$outputName = substr($outputName, 0, strlen($outputName) - strlen($outputNameExtPos) - 1);
						$outputName = $outputName.$my_outputFileExt;
						$pdf->Output($outputName, 'I');					
					//}
						
				} else {
					// No input file provided
					echo ($my_InputFileMissing);
				}
			} else {
				die( $my_SecurityCheck ); 
			}			
		}
	}

	/* Routine to replace long award name with shorter version for label */	
	function hs_ScoutBookLabelPrintMBAbbr($inItemName, $inXmlValues){
		$abbrName = $inItemName;
		$len = strlen($inItemName);
		$replyArray = array();
		foreach ($inXmlValues as $singleRow) {
			//echo ("list: ".$singleRow[HS_SBLP_NAME].":".$inItemName."<br>");
			//echo "1vs2: ".substr($singleRow[HS_SBLP_NAME], 0, $len).":".$inItemName."<br>";
			if ( strcmp(substr($singleRow[HS_SBLP_NAME], 0, $len),$inItemName) == 0 ) {
				$abbrName = substr($singleRow[HS_SBLP_ABBR], $len+1);
				//echo "2vs3: ".substr($singleRow[HS_SBLP_NAME], 0, $len).":".$inItemName."::".$abbrName."<br>";
				//echo " First:".$singleRow[HS_SBLP_NAME]." : ".$singleRow[HS_SBLP_ABBR]." : ".$singleRow[HS_SBLP_MULTILINE]." : <br>";
				//error_log(print_r("<br> Name:".$singleRow." : ".$inItemName."<br>"));
				//error_log(print_r($singleRow));
				$replyArray = array(HS_SBLP_NAME => $singleRow[HS_SBLP_NAME],
										HS_SBLP_ABBR => $singleRow[HS_SBLP_ABBR],
										HS_SBLP_MULTILINE => $singleRow[HS_SBLP_MULTILINE]);
			}
		}
		//echo "CountArray:".count($replyArray);
		if (count($replyArray) == 0) {
			//echo "<br> backup array";
			$replyArray = array(HS_SBLP_NAME => $abbrName,
										HS_SBLP_ABBR => $abbrName,
										HS_SBLP_MULTILINE => $abbrName);
			//echo "<br> default value: CountArray:".count($replyArray);
			//echo "<br> same:".$abbrName;
		} else {
			//echo "<br> backup array have value";
		}
		//echo ("<br> backup:".$abbrName);
		foreach ($replyArray as $a => $b) {
			//echo ("<br> MBAbbr:".$a." => ".$b);
		}
		//echo ("<br> print array:".$replyArray);
		return $replyArray;
	}

	function hs_ScoutBookLabelBuildMsg($itemType, $itemNameAbbr, $mbMsg, $rankMsg) {
		$labelMsgArray = Array();
		
		//foreach ($itemNameAbbr as $a => $b) {
		//foreach ($itemNameAbbr as $a) {
		//	echo "<br> hs_ScoutBookLabelBuildMsg:".$a." => ".$b;
		//}
		foreach ($itemNameAbbr as $msgType => $msgText) {
			if (strcmp ($itemType, HS_CSV_MERIT_BADGE) == 0 || 
				strcmp ($itemType, HS_CSV_MERIT_BADGES) == 0) {
				$labelMsgArray[$msgType] = str_replace(HS_MSG_MERITBADGE, $msgText, $mbMsg);
				$labelMsgArray[$msgType] = str_replace(HS_MSG_NL, HS_MSG_NEWLINE, $labelMsgArray[$msgType]);
			} elseif (strcmp($itemType, HS_CSV_BADGEFOFRANK) == 0) {
				$labelMsgArray[$msgType] = str_replace(HS_MSG_RANK, $msgText, $rankMsg);
				$labelMsgArray[$msgType] = str_replace(HS_MSG_NL, HS_MSG_NEWLINE, $labelMsgArray[$msgType]);
			} else {
				$labelMsgArray[$msgType] = trim($msgText);
				//$strLen = strlen($itemName);
				$needle = ' ';
				$spaceCount = substr_count($labelMsgArray[$msgType], $needle);
				if ($spaceCount == 2){
					$pos1 = strpos($labelMsgArray[$msgType], $needle);
					$labelMsg = substr($labelMsgArray[$msgType], 0, $pos1) . HS_MSG_NEWLINE . substr($labelMsgArray[$msgType], $pos1+1);
				} else if (($spaceCount == 3) || ($spaceCount == 4)) {
					$pos1 = strpos($labelMsgArray[$msgType], $needle);
					$pos2 = strpos($labelMsgArray[$msgType], $needle, $pos1 + strlen($needle));
					$labelMsgArray[$msgType] = substr($labelMsgArray[$msgType], 0, $pos2) . HS_MSG_NEWLINE . substr($labelMsgArray[$msgType], $pos2+1);
				} elseif ($spaceCount == 5) {
					$pos1 = strpos($labelMsgArray[$msgType], $needle);
					$pos2 = strpos($labelMsgArray[$msgType], $needle, $pos1 + strlen($needle));
					$pos3 = strpos($labelMsgArray[$msgType], $needle, $pos2 + strlen($needle));
					$labelMsgArray[$msgType] = substr($labelMsgArray[$msgType], 0, $pos3) . HS_MSG_NEWLINE . substr($labelMsgArray[$msgType], $pos3+1);
				} else {
					$labelMsgArray[$msgType] = $labelMsgArray[$msgType];
				}
				$labelMsgArray[$msgType] = trim($labelMsgArray[$msgType]);
				$labelMsgArray[HS_SBLP_MULTILINE] = $labelMsgArray[$msgType];
			}
			$labelMsgArray[$msgType] = str_replace(HS_MSG_EMBLEM, '', $labelMsgArray[$msgType]);
			$labelMsgArray[$msgType] = trim($labelMsgArray[$msgType]);
		}

		return $labelMsgArray;
	}

	function hs_ScoutBookLabelPrintSingle($singleLabelLines, $pdf, $pageOrientation) {
		global	$colNum;
		global	$fontSize;
		global	$labelRow;
		global	$numRow;
		global	$numCol;
		//global	$list_awardName;
		
		$drawBox_setting = '0';
		if (get_option( HS_SETTING_SBLP_SHOWBOX, '0' ) === false) {
			$drawBox_setting = get_option(HS_SETTING_SBLP_SHOWBOX);
		} else {
			$drawBox_setting = get_option(HS_SETTING_SBLP_SHOWBOX);
		}

		// NX =4, NY = 8
		// x0 = 36pt, y0=36pt
		// dx = 129.536pt
		// dy = 90pt
		//$stickerHeight = 31.75; // MM (1.25 inch)
		$stickerHeight = 90.1; 87; //90; // PT
		$count = 0;
		//$w = 44.45; //mm  (126pt)
		//$w = 46.0248000000003; // MM (126pt + 4.464pt)
		//$w = 47.5996000000003; // MM (126pt + 4.464pt + 4.464pt)
		//$w = 46.8227833333336; // mm (126pt + 4.464pt + 2.232pt)
		$w = 128; //126; // pt
		//$h = 6;
		$h = 2; //17;
		//$w_waste = 4.826; //4.38760; //mm (6.768pt)
		$w_waste = 11.4; //11.0; //6.768; // pt
		$h_waste = 0;
		$origin_x = $pdf->GetX();
		$origin_y = $pdf->GetY();
		//foreach ($lineLabels as $singleLabel) {
			$numLines = substr_count($singleLabelLines,"\n");
//			$h = ($stickerHeight/$numLines)-1;
			$h = $stickerHeight/($numLines+1);
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->multiCell($w, $h, $singleLabelLines, $drawBox_setting, "C");

			$pdf->SetXY($x+$w, $y);

//		$pdf->SetXY($origin_x, $origin_y+($h * 4));
			$pdf->SetXY($origin_x+$w+$w_waste, $origin_y+$h_waste);//$stickerHeight+
	}

	function hs_ScoutBookLabelPrint_listLineCount ($list_ScoutName, $scout){
		//error_log("Count ScoutName :=: ".print_r($scout, true). "::");
		//$listSize = sizeof($list_ScoutName);
		$nameCount = 3; // Start at 3 for Blank line, name line, space line
		$scout = trim($scout);
		foreach ($list_ScoutName as $singleScout) {
			$currentScout = trim((string)$singleScout[HS_ARRAY_ScoutName]);
			//error_log("compare :=: ".print_r($scout, true). "::".print_r($currentScout, true).':=:');
			if (strcmp($scout, $currentScout)==0) {
				$nameCount = $nameCount + 1;
			}
		}
		return $nameCount;
	}

	function hs_ScoutBookLabel_printListScout ($list_PO_record, $currentScout, $lineCount, $numLinesForScout, $lineHeight, $countArray) {
		global	$pdf;
			
		$localScout = '';
		foreach ($list_PO_record as $singleScout) {
			if (strcmp($currentScout, (string)$singleScout[HS_ARRAY_ScoutName]) == 0) {
				if (strcmp((string)$singleScout[HS_ARRAY_ScoutName], $localScout) != 0) {
					$localScout = (string)$singleScout[HS_ARRAY_ScoutName];
					
					$pdf->cell(100, $lineHeight, "  ", '');
					$pdf->cell(100, $lineHeight, "  ", '');;
					$pdf->cell(100, $lineHeight, "  ", '');
					$pdf->Ln();
					$lineCount = $lineCount + 1;
					$pdf->cell(100, $lineHeight, "  ", 'B');
					$pdf->cell(100, $lineHeight, "  ", 'B');;
					$pdf->cell(100, $lineHeight, "  ", 'B');
					$pdf->Ln();
					$lineCount = $lineCount + 1;
					$pdf->cell(100, $lineHeight, $singleScout[HS_ARRAY_ScoutName], 'T');
					$pdf->cell(100, $lineHeight, "  ", 'T');
					$pdf->cell(100, $lineHeight, "  ", 'T');
					$pdf->Ln();
					$lineCount = $lineCount + 1;
				}
				$pdf->cell(100, $lineHeight, "");
				$pdf->cell(100, $lineHeight, $singleScout[HS_ARRAY_ItemName]);
				$pdf->Ln();	
				$lineCount = $lineCount + 1;
				if ($lineCount >= 46 ) {
					$pdf->AddPage(HS_PDF_SETTING_ORIENTATION_PORTRAIT, TCPDF_PDF_LETTER);
					$lineCount = 0;
				}
			}
		}
	}

	function hs_ScoutBookLabel_dialogSettings() {
		global	$pdf;
		global	$cookieValues;
		global	$colNum;
		global	$fontSize;
		global	$labelRow;
		global	$numRow;
		global	$numCol;
		global	$csvFileName_sanitize;

		global	$awardLineBreak;
		$lineHeight = 15;

		$my_CardTitle = __('Settings Card', 'hs_ScoutBookLabelPrint');
		$my_CardSource = __('Source File: ', 'hs_ScoutBookLabelPrint');
		
		$my_CardLabelStyle = __('Label Style: ', 'hs_ScoutBookLabelPrint');
		$my_CardUnitType = __(' Unit Type: ', 'hs_ScoutBookLabelPrint');
		$my_CardUnitNum = __(' Unit Num: ', 'hs_ScoutBookLabelPrint');

		$my_CardFountSize = __('Font Size: ', 'hs_ScoutBookLabelPrint');
		$my_CardPosition = __('Label Postion: ', 'hs_ScoutBookLabelPrint');
		$my_CardCouncil = __('Council: ', 'hs_ScoutBookLabelPrint');
		$my_CardRankMsg = __('Rank Msg: ', 'hs_ScoutBookLabelPrint');
		$my_CardMBMsg = __('MB Msg: ', 'hs_ScoutBookLabelPrint');
		$my_CardOutput = __('Output: ', 'hs_ScoutBookLabelPrint');
		
		$count = 0;
		$cardHeight = 262;	// 3.5 inch === 252 pt
		$cardWidth = 184; // 2.5 inch === 180 pt
		$mbWidth = 54;	// 1.5 inch === 108 pt
		$mbHeight = 54;
		$badgeAdjX = 62;
		$badgeAdjY = 30;

		$border = 0;
		$cardCreated = false;
		$origin_x1 = 0;

		$origin_x = $pdf->GetX();
		$origin_y = $pdf->GetY();

		//$this->hs_ScoutBookLabel_getCookie();

		$pdf->SetXY($origin_x, $origin_y);
		
		$pdf->Ln();
		$pdf->Ln();
		$pdf->cell ( 50, $lineHeight, " ", 'B');

		$pdf->SetFontSize(12);
		$x1 = $origin_x + ($cardWidth / 4); 
		//$y1 = $origin_y + (($cardHeight / 8) *2.75);
		$y1 = $origin_y + (($cardHeight / 16) *2.75);
		$pdf->SetXY($origin_x, $y1);
		$pdf->Cell($cardWidth*2, 100, $my_CardTitle, $border, 1, "C", false); // Scout Name
		
		$pdf->Cell($cardWidth*2, 10, $my_CardSource.$csvFileName_sanitize, $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardLabelStyle.$cookieValues['SB_LABEL_PRINT']['LabelStyle'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardUnitType.$cookieValues['SB_LABEL_PRINT']['UnitType'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardUnitNum.$cookieValues['SB_LABEL_PRINT']['UnitNumber'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardFountSize.$cookieValues['SB_LABEL_PRINT']['FontSize'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardPosition.$cookieValues['SB_LABEL_PRINT']['LabelPosition'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardCouncil.$cookieValues['SB_LABEL_PRINT']['Council'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardRankMsg.$cookieValues['SB_LABEL_PRINT']['RankMessage'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardMBMsg.$cookieValues['SB_LABEL_PRINT']['MeritBadgeMessage'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth*2, 10, $my_CardOutput.$cookieValues['SB_LABEL_PRINT']['OutputContent'],	 $border, 1, "C", false);
		
	}
	
	function hs_ScoutBookLabel_CardSheetCount ($countArray, $list_PO_record) {
		global	$pdf;
		$lineCount = 0;
		$lineHeight = 15;
		$my_SheetsCards = __(' Sheets / Cards: ',  'hs_ScoutBookLabelPrint');
		$my_OnHand = __('On Hand',   'hs_ScoutBookLabelPrint');
		$my_ToBePurchased = __('To Be Purchased',   'hs_ScoutBookLabelPrint');
		$my_MeritBadges = __(' Merit Badges ',  'hs_ScoutBookLabelPrint');
		$my_ScoutRank = __(" Scout Rank ", 'hs_ScoutBookLabelPrint');
		$my_TenderFootRank = __(" Tenderfoot Rank ", 'hs_ScoutBookLabelPrint');
		$my_SecondClassRank = __(" Second Class Rank ", 'hs_ScoutBookLabelPrint');
		
		foreach ($list_PO_record as $singleScout) {
			$itemType = $singleScout[HS_ARRAY_ItemType];
			$itemName = $singleScout[HS_ARRAY_ItemName];
			switch ($itemType) {
				case HS_CSV_MERIT_BADGES :
					$countArray[HS_LABEL_COUNT_MERITBADGE]++;
					break;
				case HS_CSV_BADGEFOFRANK :
					//var_dump("Type:".$itemType.":"."itemName:".$itemName.":".HS_CSV_RANK_NAME_TENDERFOOT.":");
					if (strpos($itemName, HS_CSV_RANK_NAME_SCOUT) === 0) {
						$countArray[HS_LABEL_COUNT_SCOUT]++;
					} elseif (strpos($itemName, HS_CSV_RANK_NAME_TENDERFOOT) === 0) {
						$countArray[HS_LABEL_COUNT_TENDERFOOT]++;
						//var_dump( "itemName:".$itemName.":");
						//var_dump( "SCOUT:".HS_CSV_RANK_NAME_SCOUT.":");
						//var_dump( "itemName:".$itemName);
					} elseif (strpos($itemName, HS_CSV_RANK_NAME_SECOND) === 0) {
						$countArray[HS_LABEL_COUNT_SECONDCLASS]++;
					} elseif (strpos($itemName, HS_CSV_RANK_NAME_FIRST) === 0) {
						//var_dump("First Type:".$itemType.":"."itemName:".$itemName.":");
						$countArray[HS_LABEL_COUNT_FIRSTCLASS]++;
					} elseif (strpos($itemName, HS_CSV_RANK_NAME_STAR) === 0) {
						$countArray[HS_LABEL_COUNT_STAR]++;
					} elseif (strpos($itemName, HS_CSV_RANK_NAME_LIFE) === 0) {
						$countArray[HS_LABEL_COUNT_LIFE]++;
					} elseif (strpos($itemName, HS_CSV_RANK_NAME_EAGLE) === 0) {
						$countArray[HS_LABEL_COUNT_EAGLE]++;
					}
					break;
				case HS_CSV_MISC_AWARDS :
					$countArray[HS_LABEL_COUNT_OTHER]++;
					break;
			}
		}
		$pdf->Ln();
		$pdf->Ln();
		$pdf->cell ( 50, $lineHeight, " ", 'B');
		$pdf->cell (100, $lineHeight, " ", 'B');
		$pdf->cell (100, $lineHeight, " ", 'B');
		$pdf->cell (100, $lineHeight, $my_SheetsCards, 'B', 0, 'C');
		$pdf->cell (100, $lineHeight, $my_OnHand, 'B', 0, 'C');
		$pdf->cell (100, $lineHeight, $my_ToBePurchased, 'B', 0, 'C');
		$pdf->ln();
		$this->hs_ScoutBookLabelPrint_countLine($my_MeritBadges, $countArray[HS_LABEL_COUNT_MERITBADGE], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine($my_ScoutRank, $countArray[HS_LABEL_COUNT_SCOUT], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine(my_TenderFootRank, $countArray[HS_LABEL_COUNT_TENDERFOOT], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine($my_SecondClassRank, $countArray[HS_LABEL_COUNT_SECONDCLASS], 8, $lineHeight);
		$lineCount++;
		
		$this->hs_ScoutBookLabelPrint_countLine(" First Class Rank ", $countArray[HS_LABEL_COUNT_FIRSTCLASS], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine(" Star Rank ", $countArray[HS_LABEL_COUNT_STAR], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine(" Life Rank ", $countArray[HS_LABEL_COUNT_LIFE], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine(" Eagle Rank ", $countArray[HS_LABEL_COUNT_EAGLE], 8, $lineHeight);
		$lineCount++;

		$this->hs_ScoutBookLabelPrint_countLine(" Other Cards ", $countArray[HS_LABEL_COUNT_OTHER], 8, $lineHeight);
		$lineCount++;

		$total = $countArray[HS_LABEL_COUNT_MERITBADGE] + 
				 $countArray[HS_LABEL_COUNT_SCOUT] +
				 $countArray[HS_LABEL_COUNT_TENDERFOOT] +
				 $countArray[HS_LABEL_COUNT_SECONDCLASS] +
				 $countArray[HS_LABEL_COUNT_FIRSTCLASS] +
				 $countArray[HS_LABEL_COUNT_STAR] +
				 $countArray[HS_LABEL_COUNT_LIFE] +
				 $countArray[HS_LABEL_COUNT_EAGLE] +
				 $countArray[HS_LABEL_COUNT_OTHER];
		$pdf->cell ( 50, $lineHeight, " ", 'T');
		$pdf->cell (100, $lineHeight, " Total ", 'T');
		$pdf->cell (100, $lineHeight, $total, 'T');
		$pdf->cell (100, $lineHeight, " ", 'T');
		$pdf->cell (100, $lineHeight, " ", 'T');
		$pdf->cell (100, $lineHeight, " ", 'T');
		$lineCount++;
		$pdf->Ln();
	}
	
	/* Function to print one line of totals page */
	function hs_ScoutBookLabelPrint_countLine($lineLabel, $lineCount, $sheetCountMax, $lineHeight) {
		global	$pdf;

		$pdf->cell ( 50, $lineHeight, " ", ' ');
		$pdf->cell (100, $lineHeight, $lineLabel , ' ');
		$pdf->cell (100, $lineHeight, number_format($lineCount), ' ');

		$sheetCount = floor($lineCount/$sheetCountMax);
		$cardCount = $lineCount - floor($sheetCount * $sheetCountMax);

		$pdf->cell (100, $lineHeight, $sheetCount.'  /  '.$cardCount,' ', 0, 'C');
		if ($cardCount > 0 || $sheetCount > 0) {
			$pdf->cell (100, $lineHeight, "__", ' ', 0, 'C');
			$pdf->cell (100, $lineHeight, "__", ' ', 0, 'C');
		} else {
			$pdf->cell (100, $lineHeight, "0", ' ', 0, 'C');
			$pdf->cell (100, $lineHeight, "0", ' ', 0, 'C');
		}
		$pdf->Ln();
	}

	/*	Short code function to put link which displays the sample input file CSV */
	function hs_ScoutBookLabelPrint_SampleCSV ( $attr ) {

		$my_anchor_sampleCSV = __('Sample Input File CSV', 'hs_ScoutBookLabelPrint');
		$file='HS-TestMeritBadges.csv';
		$file1 = plugins_url('testFiles/'.$file, __FILE__);		
		echo ('<a href="'.$file1.'">'.$my_anchor_sampleCSV.'</a><br>');
	}
	
	/* Short Code function to put link which displays the sample output file PDF */
	function hs_ScoutBookLabelPrint_SamplePDF ( $attr ) {
		
		$my_anchor_samplePDF = __('Sample output File PDF', 'hs_ScoutBookLabelPrint');
		$file='HS-TestMeritBadges.PDF';
		$file1 = plugins_url('testFiles/'.$file, __FILE__);
		echo ('<a href="'.$file1.'">'.$my_anchor_samplePDF.'</a><br>');
	}

	// Advancement poster
	function hs_ScoutBookLabelPrint_Advancement_code ( $attr ) {
		global	$cookieValues;

		$this->hs_ScoutBookLabel_getCookie();

		//echo " flags-:".$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'].":<br>";
		//echo " flags-:".$cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'].":<br>";
		//echo " flags-:".$cookieValues['SB_ADVANCEMENT_CARD']['TitleFilter'].":<br>";
		
		// Display list of available text 
		$my_listTitle = __('HoweScape ScoutBook Label Print Advancement', 'hs_ScoutBookLabelPrint');
		$my_buttonValue = __('Load CSV', 'hs_ScoutBookLabelPrint');
		$my_buttonValueChart = __('Create Poster', 'hs_ScoutBookLabelPrint');
		//$my_scoutBookCsv = __('Scout Book Advancement CSV:', 'hs_ScoutBookLabelPrint');
		$my_scoutBookScoutsCSV = __('Scout Book Scouts CSV:', 'hs_ScoutBookLabelPrint');
		$my_scoutBookAdvancemntCSV = __('Scout Book Advancement CSV:', 'hs_ScoutBookLabelPrint');
		$my_fontSize = __('Font Size:', 'hs_ScoutBookLabelPrint');
		$my_advancementScouts = __('Select Scout: ', 'hs_ScoutBookLabelPrint');
		$my_advancementFile = __('Unit Information:', 'hs_ScoutBookLabelPrint');
		$my_council = __('Council:', 'hs_ScoutBookLabelPrint');
		$my_badscoutInput = __('Selected input file had the wrong number of columns was expecting 36 columns.<br>Use Back button to retry.', 'hs_ScoutBookLabelPrint');
		$my_DateRangeFrom = __('Filter From:', 'hs_ScoutBookLabelPrint');
		$my_DateRangeTo = __(' To:', 'hs_ScoutBookLabelPrint');
		$my_presentationOrder = __('Card Presentation', 'hs_ScoutBookLabelPrint');
		$my_paperSize = __('Output Size', 'hs_ScoutBookLabelPrint');
		$my_missingScoutInput = __('<h2>Input file of Scout Information is not selected.</h2>', 'hs_ScoutBookLabelPrint');
		$my_outputType = __('Output Format', 'hs_ScoutBookLabelPrint');
		$my_advancementType = __('Unit Type:', 'hs_ScoutBookLabelPrint');
		
		$my_adv_file = '';
		$scoutInfo = array();
		$linesCards = array();
		$row = 0;
		$outputSizeSelect = '';
		$cardOrderSelect = '';
		$startDate = date("Y").'-01-01'; //date("Y-m-d");
		$endDate = date("Y").'-12-31'; //date("Y-m-d");
		$statusAllScout = '';
		$statusDateFilter = '';
		$statusSortFilter = '';
		$reportTitleStatus = '';
		$reportType = '';
	
		$councilName = $cookieValues['SB_ADVANCEMENT_CARD']['Council'];
		$selectedUnitType = $cookieValues['SB_ADVANCEMENT_CARD']['UnitType'];
		
		$scoutSelection = $cookieValues['SB_ADVANCEMENT_CARD']['SelectedScout'];
		$startDate =  $cookieValues['SB_ADVANCEMENT_CARD']['StartDate'];
		$endDate  =  $cookieValues['SB_ADVANCEMENT_CARD']['EndDate'];
		$cardOrderSelect = $cookieValues['SB_ADVANCEMENT_CARD']['CardOrder'];
		$reportName = $cookieValues['SB_ADVANCEMENT_CARD']['OutputFormat'];
		$outputSizeSelect = $cookieValues['SB_ADVANCEMENT_CARD']['OutputSize'];
		//echo "<br> sort checked <br>:".$scoutSelection.":<br>";

		$xmlValues = $this->hs_ScoutBookLabelPrint_loadXML();
		
		//print_r($_COOKIE);
	
		$scoutColumn = HS_CSV_SCOUT_COLUMN; // 36
		$advancementColumn = HS_CSV_ADVANCMENT_COLUMN; // 15
		$num = $scoutColumn;
		if (isset( $_POST['scoutbook-form-submit'])) {
			if (isset($_FILES['csv_scout_file']) && strlen($_FILES['csv_scout_file']['tmp_name'])>0) {
				$my_adv_file = $_FILES["csv_scout_file"]['name'];
				$csvFileName_sanitize = sanitize_file_name($_FILES["csv_scout_file"]['tmp_name']);
				// File name generated by code on server.
				$csvFileTmp_sanitize = $_FILES["csv_scout_file"]['tmp_name'];
				if (($handle = fopen($csvFileTmp_sanitize,"r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						$num = count($data);
						// 36
						$row++;
						//echo " Column: ".$num.":".HS_CSV_SCOUT_COLUMN_2021_03."<br>";
						if ($row > 1 && $num == HS_CSV_SCOUT_COLUMN) {
							$scoutInfo[] = $this->hs_ScoutBookLabelPrint_load($data);
							$scoutColumn = HS_CSV_SCOUT_COLUMN; // 36
						} elseif ($row > 1 && $num == HS_CSV_SCOUT_COLUMN_2021_03) {
							$scoutInfo[] = $this->hs_ScoutBookLabelPrint_load_21_03($data);
							$scoutColumn = HS_CSV_SCOUT_COLUMN_2021_03; // 34
							//foreach ($scoutInfo[0] as $b => $a) {
							//	echo " ".$b." => ".$a." <br>";
							//}
						}
					}
					fclose($handle);
				}
			} else {
				// How to handle input file missing
				echo ($my_missingScoutInput);
				exit;
			}
			// Test number of columns to match expected input file of 36 columns
			//echo (" number of columns: ".$num);
			if ($num == $scoutColumn) {
				//
		//echo " flags0:".$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'].":<br>";
		//echo " flags0:".$cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'].":<br>";
		//echo " flags0:".$cookieValues['SB_ADVANCEMENT_CARD']['TitleFilter'].":<br>";

		if (strcmp($cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'],"true") == 0||
			strcmp($cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'], "1") == 0) {

			$statusDateFilter = 'Checked';
			$fromToDateStatus = '';
			//echo "<br> date checked <br>:".$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'].":<br>";
		} else {
			$statusDateFilter = '';
			$fromToDateStatus = 'Disabled';			
			//echo "<br> date not <br>";
		}
		if (strcmp($cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'], "true") == 0 ||
			strcmp($cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'], "1") == 0) {
			$statusSortFilter = 'Checked';
			$sortStatus = '';
			//echo "<br> sort checked <br>:".$cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'].":<br>";
		} else {
			$statusSortFilter = ''; 
			$sortStatus = 'Disabled';
			//echo "<br> not sort <br>";
		}
		if (strcmp($cookieValues['SB_ADVANCEMENT_CARD']['TitleFilter'], "true") == 0 ||
			strcmp($cookieValues['SB_ADVANCEMENT_CARD']['TitleFilter'], "1") == 0) {
			$statusReportTitle = 'Checked';
			$titleStatus = '';
		} else {
			$statusReportTitle = '';
			$titleStatus = 'Disabled';
		}
		$titleFromCookie = sanitize_text_field($cookieValues['SB_ADVANCEMENT_CARD']['TitleSample']);
		$titleFromCookie = str_replace('+', ' ', $titleFromCookie);
				
				$selectedCouncil = sanitize_text_field($_POST['CouncilName']);
				$selectedReport = sanitize_text_field($_POST['reportType']);
				$unitType = $this->hs_ScoutBookLabelPrint_getUnitType($scoutInfo);
				$unitNumber = $this->hs_ScoutBookLabelPrint_getUnitNumber($scoutInfo);
				$selectedReportType = $this->hs_ScoutBookLabelPrint_getReportType($selectedReport);
				
				//if (strLen($statusDateFilter) > 0) {
				//	echo " flags:".$statusDateFilter.":";
				//} else {
				//	echo " flags:"."-:";
				//}
				// Second form
				echo ('<h2>'.$my_listTitle.'</h2>');
				echo('<form id="form" target="_Blank" method="POST" onsubmit="return openForm(this.id)" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">');
				wp_nonce_field('HS_SCOUTBOOK_FORM_ADVANCEMENT', 'HS_SETTINGS_NONCE', true, true);
				echo('<input type="hidden" name="action" value="hs_ScoutBookLabelPrint_AdvancementResult">');
				echo('<input type="hidden" name="CouncilName" value="'.$selectedCouncil.'">');
				echo('<input type="hidden" name="unitType" value="'.$unitType.'">');
				echo('<input type="hidden" name="unitNumber" value="'.$unitNumber.'">');
				$countScout = count($scoutInfo);
				$countAttr = count($scoutInfo[0]);
				echo('<input type="hidden" name="scoutCount" value="'.$countScout.'">');
				echo('<input type="hidden" name="attributeCount" value="'.$countAttr.'">');
				//echo "reportType: ".$selectedReportType."<br>";
				//echo '<input type="hidden" name="reportType" value="'.$selectedReportType.'">';
				$sep = ':';
				foreach ($scoutInfo as $singleScout) {
					foreach ($singleScout as $singleName => $singleField) {
						echo('<input type="hidden" name="ScoutInfo[]" value="'.$singleName.$sep.$singleField.'">');
					}
				}

			//error_log(" beforeTable:".$statusDateFilter.":".$statusSortFilter.":".$statusReportTitle.":");
			
			echo('<div class="divTable grayTable">');
			echo('<div class="divTableBody">');	
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_council.'</div>');
					echo ('<div class="divTableCell">'.$selectedCouncil.'</div>');
				echo ('</div>');
//				echo ('<div class="divTableRow">');
//					echo ('<div class="divTableCell" align="right">'.$my_outputType.'</div>');
//					echo ('<div class="divTableCell">'.$selectedReportType.'</div>');
//				echo ('</div>');
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_advancementFile.'</div>');
					echo ('<div class="divTableCell">'.$unitType.' '.$unitNumber.'</div>');
				echo ('</div>');

				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_advancementType.'</div>');
//					//$this->hs_ScoutBook_unitTypeSelect();
					echo ('<div class="divTableCell">');
						$this->hs_ScoutBookLabelPrint_loadReportOnUnitType($xmlValues, $selectedUnitType);
					echo ('</div>');
				echo ('</div>');
				
				echo('<div class="divTableRow">');
					echo ('<div class="divTableCell"  align="right">'.$my_scoutBookAdvancemntCSV.'</div>');
					echo ('<div class="divTableCell"><input type="file" name="csv_file2" accept=".csv" /></div>');
				echo ('</div>');

				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">Options</div>');

					echo ('<div class="divTableCell">'.
								'Date Filter <input type="checkbox" id="DateFilter" name="DateFilter" value="DateFilter" '.$statusDateFilter.
																' onchange="document.getElementById(\'startDate\').disabled = !this.checked;'. 
																'document.getElementById(\'endDate\').disabled = !this.checked;">'.
																'<br>'.
								'Sort Filter <input type="checkbox" id="SortFilter" name="SortFilter" value="SortFilter" '.$statusSortFilter.
																' onchange="document.getElementById(\'CardSort\').disabled = !this.checked;">'.
																'<br>'.
								'Poster Title <input type="checkbox" id="TitleFilter" name="TitleFilter" value="TitleFilter" '.$statusReportTitle.
																' onchange="document.getElementById(\'TitlePresent\').disabled = !this.checked;">');
													echo ('</div>');

				echo ('</div>');

				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_advancementScouts.'</div>');
					echo ('<div class="divTableCell"><select name="SelectedScout" id="SelectedScout" >');
					$this->hs_ScoutBookLabelPrint_ScoutOptions($scoutInfo, $scoutSelection);
					echo ('</select></div>');
				echo ('</div>');
				
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_DateRangeFrom.'</div>');
					echo ('<div class="divTableCell"><input type="date" id="startDate" name="startDate" min="1910-01-01" value="'.$startDate.'" '.$fromToDateStatus.' /> '
														.$my_DateRangeTo.'<input type="date" id="endDate" name="endDate"  value="'.$endDate.'" '.$fromToDateStatus.' /></div>');
				echo ('</div>');
								
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_presentationOrder.'</div>');

					echo ('<div class="divTableCell"><select name="CardSort" id="CardSort" '.$sortStatus.' >');
						if ($cardOrderSelect == HS_PDF_SORT_FileOrder) {
							$selected = 'selected="selected"';
						} else {
							$selected = '';
						}
						echo ('<option value="'.HS_PDF_SORT_FileOrder.'" '.$selected.'>'.HS_PDF_SORT_FileOrder.'</option>');
						if ($cardOrderSelect == HS_PDF_SORT_DateOrderAsc) {
							$selected = 'selected="selected"';
						} else {
							$selected = '';
						}
						echo ('<option value="'.HS_PDF_SORT_DateOrderAsc.'" '.$selected.'>'.HS_PDF_SORT_DateOrderAsc.'</option>');
						if ($cardOrderSelect == HS_PDF_SORT_DateOrderDesc) {
							$selected = 'selected="selected"';
						} else {
							$selected = '';
						}
						echo ('<option value="'.HS_PDF_SORT_DateOrderDesc.'" '.$selected.'>'.HS_PDF_SORT_DateOrderDesc.'</option>');
					echo ('</select></div>');
				echo ('</div>');

				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.'Report Title: '.'</div>');
					echo ('<div class="divTableCell"><input type="text" id="TitlePresent" name="TitlePresent" value="'.$titleFromCookie.'" '.$titleStatus.' /></div>');
				echo ('</div>');
				
				echo ('<div class="divTableRow">');
					echo '<div class="divTableCell" align="right">'.$my_outputType.'</div>';
					echo '<div class="divTableCell"><select name="reportType">';
					$this->hs_ScoutBookLabelPrint_loadReportType($reportName);
					echo '</select></div>';
				echo ('</div>');
				
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_paperSize.'</div>');
					echo ('<div class="divTableCell"><select name="PaperSize">');
						$this->hs_ScoutBookLablePrint_PaperSizeOptions($outputSizeSelect, $reportType);
					echo ('</select></div>');
				echo ('</div>');

				
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell"></div>');
					echo ('<div class="divTableCell"><input type="submit" name="scoutbook-form-poster" id="submit" value="'.$my_buttonValueChart.'" class="button button-primary"/></div>');
				echo ('</div>');			
				echo ('</div>');			
				echo ('</div>');			
			echo('</form>');
			} else { // post error message wrong input file
				echo ('<h2>'.$my_listTitle.'</h2>');
				echo('<div class="divTable grayTable">');
					echo('<div class="divTableBody">');	
						echo ('<div class="divTableRow">');
						echo ('<div class="divTableCell">'.$my_badscoutInput.'</div>');
						echo ('</div>');
					echo('</div>');
				echo ('</div>');
			}
			//
		} elseif (isset( $_POST['scoutbook-form-poster'])) {
			if (isset($_FILES['csv_advancement_file']) && strlen($_FILES['csv_advancement_file']['tmp_name'])>0) {
				$memberID = sanitize_text_field($_POST['SelectedScout']);
				$my_adv_file = $_FILES["csv_advancement_file"]['name'];
				echo ("<br> memberID:".$memberID);
				echo ("<br> adv File:".$my_adv_file);
				echo ("<br> other:".$_FILES["csv_advancement_file"]['tmp_name']);
				// File name generated by code on server.
				$csvFileName_sanitize = sanitize_file_name($_FILES["csv_advancement_file"]['tmp_name']);
				if (($handle = fopen($csvFileTmp_sanitize,"r")) !== FALSE) {
					while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
						$num = count($data);
						$row++;
						if ($row > 1) {	// Skip title record
							$memberNum = $data[0];	// A
							$firstName = $data[1];	// B
							$middleName = $data[2];	// C
							$lastName = $data[3];	// D
							$advType = $data[4];	// E
							$Advancement = $data[5];	// F
							$advDate = $data[7];	// H
							$scoutName = $firstName." ".$middleName." ".$lastName;
							$scoutInfo[] = $memberNum.":".$scoutName;
							// Get Rank and MB records
							if (strcasecmp($memberNum, $memberID) == 0 && strcasecmp($advType, HS_CSV_RANK) == 0) {
								// Need values
								$councilName = "unknown";
								$unitTypeNum = "Unknown";
								$multiCardLines = HS_MSG_NEWLINE.HS_MSG_NEWLINE.
												  $scoutName.HS_MSG_NEWLINE.
												  $Advancement.HS_MSG_NEWLINE.
												  $councilName.HS_MSG_NEWLINE.
												  $unitTypeNum.$advDate;
								$linesCards[] = $multiCardLines;
							
							} else if (strcasecmp($memberNum, $memberID) == 0 && strcasecmp($advType, 'Merit Badge') == 0) {
						
							}
						}
					}
					fclose($handle);
				}
			}
		} else {
			$councilName = $cookieValues['SB_ADVANCEMENT_CARD']['Council'];
			//echo " CN:".$councilName."<br";
			echo ('<h2>'.$my_listTitle.'</h2>');
			echo('<form id="form" method="POST" onsubmit="return openForm(this.id)" enctype="multipart/form-data" >');		
			wp_nonce_field('HS_SCOUTBOOK_FORM_ADVANCEMENT', 'HS_SETTINGS_NONCE', true, true);
			echo('<input type="hidden" name="action" value="hs_ScoutBookLabelPrint_ResultAdvancement">');
			echo('<input type="hidden" name="reportType" value="">');
			// Load Scouts CSV to get scout name, member id, unit number, unit type
			// load XML file for council info
			// Build Table of controls
			echo('<div class="divTable grayTable">');
			echo('<div class="divTableBody">');
				echo('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_scoutBookScoutsCSV.'</div>');
					echo ('<div class="divTableCell"><input type="file" name="csv_scout_file" accept=".csv" /></div>');
				echo ('</div>');

				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell" align="right">'.$my_council.'</div>');
					echo ('<div class="divTableCell"><select name="CouncilName">');
					$this->hs_ScoutBookLabelPrint_loadCouncil($councilName);
					echo ('</select></div>');
				echo ('</div>');				
				echo ('<div class="divTableRow">');
					echo ('<div class="divTableCell"></div>');
					echo ('<div class="divTableCell"><input type="submit" name="scoutbook-form-submit" id="submit" value="'.$my_buttonValue.'" class="button button-primary"/></div>');
				echo ('</div>');			
			echo('</div>');	// TableBody
			echo('</div>');	// Gray Table
			echo('</form>');			
		}
	}

	function hs_ScoutBookLabelPrint_load($data) {
		$memberNum = sanitize_text_field($data[0]);	// A
		$firstName = sanitize_text_field($data[1]);	// B
		$middleName = sanitize_text_field($data[2]);	// C
		$lastName = sanitize_text_field($data[3]);	// D
		$lastNameSuffix = sanitize_text_field($data[4]);	// E
		$dateOfBirth = sanitize_text_field($data[13]); // N
		$unitNumber = sanitize_text_field($data[19]);	// T
		$unitType = sanitize_text_field($data[20]);	// U
		$dateJoined = sanitize_text_field($data[21]);  // V
		$denType = sanitize_text_field($data[22]); 	// W
		$denNum = sanitize_text_field($data[23]);	// X
							
		$patrolDate = sanitize_text_field($data[26]);	// AA
		$scoutName = $firstName." ".$middleName." ".$lastName." ".$lastNameSuffix;
		return array(HS_ARRAY_Scout_MemberNum => $memberNum, 
					HS_ARRAY_Scout_MemberName => $scoutName, 
					HS_ARRAY_Scout_DOB => $dateOfBirth,
					HS_ARRAY_Scout_UnitNum => $unitNumber, 
					HS_ARRAY_Scout_UnitType =>$unitType,
					HS_ARRAY_Scout_DateJoined => $dateJoined,
					HS_ARRAY_Scout_DenType => $denType, 
					HS_ARRAY_Scout_DenNum => $denNum, 
					HS_ARRAY_Scout_PatrolDate =>$patrolDate);		
	}
	
	function hs_ScoutBookLabelPrint_load_21_03($data) {
		$userID = $data[0];		// A
		$memberNum = $data[1];	// B
		$firstName = $data[2];	// C
		$middleName = "";		// removed 2021-03
		$lastName = $data[3];	// D
		$lastNameSuffix = $data[4];	// E
		$dateOfBirth = "99/99/9999";// removed 2021-03
		$unitNumber = $data[17];	// R
		$unitType = $data[18];		// S
		$dateJoined = $data[19];  	// T
		$denType = $data[20]; 	// U
		$denNum = $data[21];	// V
							
		$patrolDate = $data[24];	// Y
		$scoutName = $firstName." ".$lastName." ".$lastNameSuffix;
		return array(HS_ARRAY_Scout_MemberNum => $memberNum, 
					HS_ARRAY_Scout_MemberName => $scoutName, 
					HS_ARRAY_Scout_DOB => $dateOfBirth,
					HS_ARRAY_Scout_UnitNum => $unitNumber, 
					HS_ARRAY_Scout_UnitType =>$unitType,
					HS_ARRAY_Scout_DateJoined => $dateJoined,
					HS_ARRAY_Scout_DenType => $denType, 
					HS_ARRAY_Scout_DenNum => $denNum, 
					HS_ARRAY_Scout_PatrolDate =>$patrolDate);
	}

	function hs_ScoutBookLabelPrint_ScoutOptions($scoutInfo, $inScoutSelection) {
		$scoutName = "zczc";
		$AllScoutsSelection = "All Scouts";
		if (strcmp($inScoutSelection, -1) == 0) {
			$selectionFlag = 'selected="selected"';
		} else {
			$selectionFlag = '';
		}
		$optionScoutID = -1;
		echo '<option value="'.$optionScoutID.'" '.$selectionFlag.'>'.$AllScoutsSelection.'</option>';
		foreach ($scoutInfo as $singleScout) {
			if (strpos($singleScout['MemberName'], $scoutName) === false) {

				//error_log(print_r("<br> sc:".$inScoutSelection." : ".$singleScout[HS_ARRAY_Scout_MemberNum]."<br>"));
				echo "<br>:".$inScoutSelection.":".$singleScout[HS_ARRAY_Scout_MemberNum].":<br>";
				if (strcmp($inScoutSelection, $singleScout[HS_ARRAY_Scout_MemberNum]) == 0) {
					$selectionFlag = 'selected="selected"';
				} else {
					$selectionFlag = '';
				}
				$optionScoutID = $singleScout[HS_ARRAY_Scout_MemberNum];
				echo ('<option value="'.$optionScoutID.'" '.$selectionFlag.'>'.$singleScout[HS_ARRAY_Scout_MemberName].'</option>');
			}
		}
	}

	function hs_ScoutBookLablePrint_PaperSizeOptions($inOutputSizeSelect, $inReportType) {
		$paperOptions = array(  TCPDF_PDF_LETTER 			=> TCPDF_PDF_LETTER,
								TCPDF_PDF_EN_COPY_DRAUGHT 	=> TCPDF_PDF_EN_COPY_DRAUGHT,
								TCPDF_PDF_ARCH_C 			=> TCPDF_PDF_ARCH_C_DISPLAY,
								TCPDF_PDF_POSTER_LARGE 		=> TCPDF_PDF_POSTER_DISPLAY,
								TCPDF_PDF_ARCH_D_LAND 		=> TCPDF_PDF_ARCH_D_LAND_DISPLAY,
								TCPDF_PDF_ARCH_E 			=> TCPDF_PDF_ARCH_E_DISPLAY);

		foreach ($paperOptions as $paperValue => $paperDisplay) {
			if ($inOutputSizeSelect == $paperValue) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			echo ('<option value="'.$paperValue.'" '.$selected.'>'.$paperDisplay.'</option>');
		}	
	}

	function hs_ScoutBookLabelPrint_getUnitType($scoutInfo) {
		$returnValue = "unknown";
		foreach ($scoutInfo as $singleScout) {
				$returnValue = $singleScout['unitType'];
				break;
		}
		return $returnValue;
	}
	
	function hs_ScoutBookLabelPrint_getUnitNumber($scoutInfo) {
		$returnValue = "unknown";
		foreach ($scoutInfo as $singleScout) {
				//$sepIndex  = strpos ($singleScout, ':');
				//$sepUnitNo = strpos ($singleScout, ':', $sepIndex+1);
				//$sepUnitTyp= strpos ($singleScout, ':', $sepUnitNo+1);
				//$returnValue = substr($singleScout, $sepUnitNo+1, $sepUnitTyp-$sepUnitNo-1);
				$returnValue = $singleScout[HS_ARRAY_Scout_UnitNum];
				break;
		}
		return $returnValue;
	}

	function hs_ScoutBookLabelPrint_getReportType($selectedReport) {
		$supportedReports = array(TCPDF_REPORT_TYPE_REPORT => TCPDF_REPORT_TYPE_REPORT, TCPDF_REPORT_TYPE_POSTER => TCPDF_REPORT_TYPE_POSTER);
		$selectedName = "";
		foreach ($supportedReports as $a => $b) {
			if (strcmp ($selectedReport, $a) == 0) {
				$selectedName = $b;
			}
		}
		return $selectedName;
	}

	function hs_ScoutBookLabelPrint_loadReportOnUnitType($xmlValues, $cookieUnit) {
		$groupCount = 0;
		$isUnitValue = false;
		$selectedUnit = $cookieUnit;
		//echo " OnUnitTYpeCalled<br>";
		echo '<select name="UnitReportType" id="UnitReportType">';
		foreach ($xmlValues as $xmlSample) {
			if ($xmlSample == -1 ) {
				$groupCount++;
			}
			if ($groupCount >= 7 && $xmlSample != -1 && $groupCount < 8)  {
				$singleRowValue = $xmlSample["ID"];
				$singleRowLabel = $xmlSample["AdvancementType"];
					if (strcasecmp($selectedUnit, $singleRowValue) == 0 && $isUnitValue == false){
						$selected = 'selected="selected"';
						$isUnitValue = true;
					} else {
						$selected = '';
					}	
				echo "<Option value='".$singleRowValue."' ".$selected." >".$singleRowLabel."</Option>";
			}
		}
		echo '</select>';
		return;
	}

	function hs_ScoutBookLabelPrint_loadReportType($inReportName) {
		//$supportedReports = array(TCPDF_REPORT_TYPE_REPORT => TCPDF_REPORT_TYPE_REPORT, TCPDF_REPORT_TYPE_POSTER => TCPDF_REPORT_TYPE_POSTER);
		$supportedReports = array(TCPDF_REPORT_TYPE_POSTER => TCPDF_REPORT_TYPE_POSTER);
		foreach ($supportedReports as $a => $b) {
			if (strcmp($inReportName, $a) == 0){
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			echo "<Option value='".$a."' ".$selected." >".$b."</Option>";
		}

	}

	function hs_ScoutBookLabelPrint_AdvancementResult () {
		global	$cookieValues;

		global	$colNum;
		global	$fontSize;
		global	$labelRow;
		global	$numRow;
		global	$numCol;
		global	$awardLineBreak;
		global	$pageSize;
		//global	$list_awardName;
		
		$pageSize = TCPDF_PDF_POSTER_LARGE;
		$row = 0;		// Set variable initial value;
		
		$my_missingAdvancementInput = __('<h1>Input file of Advancement Information is not selected.</h1>', 'hs_ScoutBookLabelPrint');
		//$list_awardName = array();
		$border = 0;
		$GroupRow = array();
		$ScoutRank = array();
		$linesCards = array();
		$scoutInfo = array();
		$newScoutInfo = array();
		$scoutArray = array();
		$groupScout = array();
		$selectedScout = 'unknown';
		$awardLineBreak = array();
		
		$advanement_haystack = array();
		
		$l_checkedTitle = "";

		$xmlValues = $this->hs_ScoutBookLabelPrint_loadXML();

		$advanement_haystack = $this->hs_ScoutBookLabelPrint_loadAdvHaystack($xmlValues, 5);
									//error_log (" After load");
									//foreach ($advanement_haystack as $singleItem) {
									//	error_log ($singleItem);
									//}

		$awardLineBreak = $this->hs_ScoutBookLabelPrint_loadAwardLineBreak($xmlValues, 8);
		
		// Load list of abbr names for there display
		$meritBadgesAbbr = $this->hs_ScoutBookLabelPrint_loadMB($xmlValues);
		
		//send_to_console('entry');
		
		//echo ("<br> option:");
 		
		if (isset($_POST['scoutbook-form-poster'])) {
			if (isset($_FILES['csv_file2']) && strlen($_FILES['csv_file2']['tmp_name'])>0) {
				if (isset($_POST['HS_SETTINGS_NONCE']) && 
					wp_verify_nonce($_POST['HS_SETTINGS_NONCE'], 'HS_SCOUTBOOK_FORM_ADVANCEMENT') != false) {
					$list_Awards = array();
					$councilName = "unknown";
					$councilName = sanitize_text_field($_POST['CouncilName']);
					$unitType = sanitize_text_field($_POST['unitType']);
					$unitNumber = sanitize_text_field($_POST['unitNumber']);
					$scoutInfo = $_POST['ScoutInfo'];
					$attributeCount = sanitize_text_field($_POST['attributeCount']);
					$reportType = sanitize_text_field($_POST['reportType']);
					$reportUnitType = sanitize_text_field($_POST['UnitReportType']);
					//echo "enter: ".$reportUnitType."<br>";
					// did scout info make the form change?
					//echo '<br> scoutInfo: '.count($scoutInfo).'<br>';
					//echo '<br> scoutInfo: '.$scoutInfo.'<br>';
					$nameList = array();
					$valueList = array();
					$sepIndex = 0;
					$index = 0;
					$pairList = array();
					$pair = array();

					foreach ($scoutInfo as $singleScout) {

						$sepIndex = strrpos($singleScout, ':');
						$nameList[] = substr($singleScout, 0, $sepIndex);
						$valueList[] = substr($singleScout, $sepIndex+1); 

						$index = $index + 1;
						if (count($nameList) % $attributeCount == 0) {
							//$pair = array();
							$scoutArray[] = array_combine($nameList, $valueList);

						}

					}

					if (isset($_POST['SelectedScout'])) {
						$memberID = sanitize_text_field($_POST['SelectedScout']);
					} else {
						$memberID = sanitize_text_field($scoutArray[0][HS_ARRAY_Scout_MemberNum]);
					}

					$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
					//$pageOrientation = HS_PDF_SETTING_ORIENTATION_LANDSCAPE;
					$keywords = "ScoutBook";
					$fontSize = 9;

					// Set/reset Cookie values
//					if (isset($_COOKIE[HS_COOKIE_SBLP_SelectScout]) && !isset($_POST['AllScouts'])) {
					if (isset($_COOKIE[HS_COOKIE_SBLP_SelectScout])) {
						unset($_COOKIE[HS_COOKIE_SBLP_SelectScout]);
					}
					if (isset($_COOKIE[HS_COOKIE_SBLP_CardOrder])) {
						unset($_COOKIE[HS_COOKIE_SBLP_CardOrder]);
					}
					if (isset($_COOKIE[HS_COOKIE_SBLP_OutputSize])) {
						unset($_COOKIE[HS_COOKIE_SBLP_OutputSize]);
					}
					if (isset($_COOKIE[HS_COOKIE_SBLP_OutputFormat])) {
						unset($_COOKIE[HS_COOKIE_SBLP_OutputFormat]);
					}

					if (isset($_POST['DateFilter'])) {
						if (isset($_COOKIE[HS_COOKIE_SBLP_StartDate])) {
							unset($_COOKIE[HS_COOKIE_SBLP_StartDate]);
						}
						if (isset($_COOKIE[HS_COOKIE_SBLP_EndDate])) {
							unset($_COOKIE[HS_COOKIE_SBLP_EndDate]);
						}
					}
//					if (isset($_COOKIE[HS_COOKIE_SBLP_AllScoutsChecked])) {
//						unset($_COOKIE[HS_COOKIE_SBLP_AllScoutsChecked]);
//					}
					if (isset($_COOKIE[HS_COOKIE_SBLP_DateFilterChecked])) {
						unset($_COOKIE[HS_COOKIE_SBLP_DateFilterChecked]);
					}
					if (isset($_COOKIE[HS_COOKIE_SBLP_SortFilterChecked])) {
						unset($_COOKIE[HS_COOKIE_SBLP_SortFilterChecked]);
					}

					if (isset($_POST['SelectedScout'])) {
						$l_SelectedScout = sanitize_text_field($_POST['SelectedScout']);
						$cookieValues["SB_ADVANCEMENT_CARD"]["SelectedScout"] = sanitize_text_field($_POST['SelectedScout']);
					} else {
						//var_dump($scoutInfo);
						//var_dump($scoutArray);
						//var_dump($_COOKIE[HS_COOKIE_SBLP_SelectScout]);
						//error_log(print_r("<br> cookie:".$_COOKIE[HS_COOKIE_SBLP_SelectScout]."<br>"));
						//if (isset($_COOKIE[HS_COOKIE_SBLP_SelectScout]) && strlen($_COOKIE[HS_COOKIE_SBLP_SelectScout]) > 0) {
						if (isset($cookieValues["SB_ADVANCEMENT_CARD"]["SelectedScout"]) && strlen ($cookieValues["SB_ADVANCEMENT_CARD"]["SelectedScout"]) > 0) {

							$l_SelectedScout = $cookieValues["SB_ADVANCEMENT_CARD"]["SelectedScout"];
						} else {

							$l_SelectedScout = $scoutArray[0][HS_ARRAY_Scout_MemberNum];
							$cookieValues["SB_ADVANCEMENT_CARD"]["SelectedScout"] = $scoutArray[0][HS_ARRAY_Scout_MemberNum];

						}
					}
					if (isset($_POST['startDate'])) {
						$l_startDate = sanitize_text_field($_POST['startDate']);
						$cookieValues["SB_ADVANCEMENT_CARD"]["StartDate"] = $l_startDate;
					} else  {
						//$l_startDate = $_COOKIE[HS_COOKIE_SBLP_StartDate];
						$l_startDate = $cookieValues["SB_ADVANCEMENT_CARD"]["StartDate"];
					} 
					if (isset($_POST['endDate'])) {
						$l_endDate = sanitize_text_field($_POST['endDate']);
						$cookieValues["SB_ADVANCEMENT_CARD"]["EndDate"] = $l_endDate;
					} else {
						//$l_endDate = $_COOKIE[HS_COOKIE_SBLP_EndDate];
						$l_endDate = $cookieValues["SB_ADVANCEMENT_CARD"]["EndDate"];
					} 
					if (isset($_POST['CardSort'])) {
						$l_cardSort = sanitize_text_field($_POST['CardSort']);
						$cookieValues["SB_ADVANCEMENT_CARD"]["CardOrder"] = $l_cardSort;
					} else {
						//$l_cardSort = HS_PDF_SORT_FileOrder;
						$l_cardSort = $cookieValues["SB_ADVANCEMENT_CARD"]["CardOrder"];
					}
					$l_paperSize = sanitize_text_field($_POST['PaperSize']);
//					if (isset($_POST['AllScouts'])) {
//						$l_checkedScout = $_POST['AllScouts'];
//					} else {
//						$l_checkedScout = '';
//					}
					if (isset($_POST['DateFilter'])) {
						$l_checkedDate = true;
						$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'] = true;
					} else {
						//$l_checkedDate = false;
						$cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'] = false;
						$l_checkedDate = $cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'];
					}
					if (isset($_POST['SortFilter'])) {
						$l_checkedSort = true;
						$cookieValues["SB_ADVANCEMENT_CARD"]["SortFilter"] = true;
					} else {
						//$l_checkedSort = '';
						$cookieValues["SB_ADVANCEMENT_CARD"]["SortFilter"] = false;
						$l_checkedSort = $cookieValues["SB_ADVANCEMENT_CARD"]["SortFilter"];
					}
					if (isset($_POST['TitleFilter'])) {
						$l_checkedTitle = true;
						$l_titleValue = sanitize_text_field($_POST['TitlePresent']);
						$cookieValues["SB_ADVANCEMENT_CARD"]["TitleFilter"] = true;
						$cookieValues["SB_ADVANCEMENT_CARD"]["TitleSample"] = $_POST['TitlePresent'];
					} else {
						//$l_checkedTitle = '';
						//$l_titleValue = '';
						$cookieValues["SB_ADVANCEMENT_CARD"]["TitleFilter"] = false;
						//$l_checkedTitle = $cookieValues["SB_ADVANCEMENT_CARD"]["TitleFilter"];
						$l_titleValue = $cookieValues["SB_ADVANCEMENT_CARD"]["TitleSample"];
					}

					$cookieValues_string = json_encode($cookieValues);
					$timeExpires = time() + (60 * 60 * 24 * 365);
					setcookie(HS_COOKIE_SB_JSON, $cookieValues_string, $timeExpires, '/');
					
					// Remove old cookies
					setcookie(HS_COOKIE_SBLP_SelectScout, 		"", time() - 3600);
					setcookie(HS_COOKIE_SBLP_CardOrder, 		"", time() - 3600);
					setcookie(HS_COOKIE_SBLP_OutputSize, 		"", time() - 3600);
					setcookie(HS_COOKIE_SBLP_StartDate, 		"", time() - 3600);
					setcookie(HS_COOKIE_SBLP_EndDate, 			"", time() - 3600);
					setcookie(HS_COOKIE_SBLP_AllScoutsChecked, 	"", time() - 3600);
					setcookie(HS_COOKIE_SBLP_DateFilterChecked, "", time() - 3600);
					setcookie(HS_COOKIE_SBLP_SortFilterChecked, "", time() - 3600);

					// Load CSV file from local copy
					// File name generated by code on server.
					$csvFileTmp_sanitize = $_FILES["csv_file2"]["tmp_name"];
					if (($handle = fopen($csvFileTmp_sanitize,"r")) !== FALSE) {
						
						while (($data = fgetcsv($handle, 1000, ",", "\"")) !== FALSE) {
							$num = count($data);
							//echo "<p> $num fields in line $row: <br /></p>\n";
							$row++;
							$requirementFound = false;
							if ($row > 1) {	// Skip title record
								$memberNum = $data[0];	// A
								$firstName = $data[1];	// B 
								$middleName = $data[2];	// C
								$lastName = $data[3];	// D 
								$advType = $data[4];	// E
								$Advancement = $data[5]; // F
								$advDate = $data[7];	// H
								$approved = $data[8];	// I
								$awarded = $data[9];	// J
								$markedCompleteBy = $data[10];	// K
								$scoutName = $firstName." ".$middleName." ".$lastName;
								$category = 'unknown';
								$scoutInfo[] = $memberNum.":".$scoutName;
								$cardType = HS_SBLP_UNIT_BOY;
								//var_dump("advType:".$advType);
								//$category = $this->hs_ScoutBookCheckCategory($Advancement);

								$AdvancementAbbr = $this->hs_ScoutBookLabelPrintMBAbbr($Advancement, $meritBadgesAbbr);								
								//error_log(print_r("cardInfo:".$AdvancementAbbr['Name']));
								if (strcasecmp($advType, HS_CSV_RANK) == 0) {
//PTH									$list_awardName[] = $Advancement.HS_MSG_EMBLEM;
									$category = $this->hs_ScoutBookCheckCategory($Advancement);
									//error_log("cat :=: ".print_r($inGraphicBadgeList['Category'], true));
									//error_log("cat :=: ".print_r($category, true));
									$ScoutRank = array(HS_ARRAY_Scout_MemberNum=>$memberNum, 'ScoutName' => $scoutName,'Advancement' => $Advancement);
								} else if (strcasecmp($advType, HS_CSV_MERIT_BADGE) == 0) {
//PTH									$list_awardName[] = $Advancement." ".HS_MSG_MB_EMBLEM;
									$category = HS_SBLP_UNIT_BOY;
								} elseif (strcasecmp($advType, HS_CSV_RANK_NAME_SCOUT_REQ) == 0 || 
											strcasecmp($advType, HS_CSV_RANK_NAME_TENDERFOOT_REQ) == 0 ) {
								} elseif (strcasecmp($advType, HS_CSV_MERIT_BADGE_REQ) == 0) {
//									$list_meritBadgeReqs[] = 
								} elseif (strcasecmp($advType, HS_CSV_RANK_BS_AWARD) == 0) {
									// determine type of card
									$cardType = HS_SBLP_UNIT_CUB;
									$category = HS_SBLP_UNIT_CUB;
									if (strcmp($Advancement, "Kayaking BSA") == 0) {
									//error_log ("Adv: ".$Advancement." group: ".$advanement_haystack);
									//foreach ($advanement_haystack as $singleItem) {
									//	error_log ($singleItem);
									//}
									}
									//error_log(" Award:".print_r($Advancement, true));
									//error_log(" Award:".print_r($advanement_haystack, true));
									$found = false;
									foreach ($advanement_haystack as $singleItem) {
										if (strcmp($singleItem, $Advancement) == 0) {
										//error_log(" Single:".print_r($singleItem,true)."<br>");
										$found = true;
										}
									}
									//if (in_array($Advancement, $advanement_haystack) == true) {
									if ($found == true) {										
										$cardType = HS_SBLP_UNIT_BOY;
										$category = HS_SBLP_UNIT_BOY;
									}
								} elseif ($reportType == TCPDF_REPORT_TYPE_REPORT && 
										  strpos($advType, HS_CSV_ADVANCEMENT_REQ) !== false &&
										  strcasecmp($awarded, "1") != 0) {
										$requirementFound = true;
								} elseif (strcasecmp($advType, HS_CSV_RANK_CS_ADVENTURE) == 0) {
									$cardType = HS_SBLP_UNIT_CUB;
									$category = HS_SBLP_UNIT_CUB;
								} elseif (strcasecmp($advType, HS_CSV_RANK_CS_LOOP) == 0) {
									$cardType = HS_SBLP_UNIT_CUB;
									$category = HS_SBLP_UNIT_CUB;
								} elseif (strcasecmp($advType, HS_CSV_RANK_CS_WEBELOS) == 0) {
									$cardType = HS_SBLP_UNIT_CUB;
									$category = HS_SBLP_UNIT_CUB;
								}
								
//								if (strcmp($category, "unknown") == 0) {
//									echo "<br> award: ".$advType." = ".$category;
//								}
								$unitTypeNum = "Unit: ".$unitType." ".$unitNumber;
								// Get Rank and MB records
								$memberNumberOK = true;
//								if (isset($_POST['AllScouts']) && $_POST['AllScouts'] == true) {
//									$memberNumberOK = true;
//								} else {
//									$memberNumberOK = (strcasecmp($memberNum, $memberID) == 0);
//								}
//error_log(" memNum: ".$memberNum." memID: ".$memberID);
								if (strcmp($memberID,"-1") == 0) {
									$memberNumberOK = true;
								} else {
									$memberNumberOK = (strcasecmp($memberNum, $memberID) == 0);
								}
								$dateRangeOK = true;
								$fromDate = $l_startDate;
								$toDate = $l_endDate;
								$stdAdvDate = strtotime($advDate);
								if ($l_checkedDate != true) {
									$dateRangeOK = true;
								} else {
									//$stdAdvDate = strtotime($advDate);
									$stdFromDate = strtotime($fromDate);
									$stdToDate = strtotime($toDate);
									//error_log("<br> Date Check From:".print_r($stdFromDate, true));
									//error_log("<br> Date Check To:".print_r($stdToDate, true));
									//error_log("<br> Date Check:".print_r($stdAdvDate, true));
									$dateRangeOK = (($stdFromDate <= $stdAdvDate) && ($stdAdvDate <= $stdToDate));
								}
								// Set Flag for Report type
								//$awardInReportUnit = $this->hs_ScoutBook_TypeInReport ($reportUnitType, $category);
								//echo " RUT: ".$reportUnitType." CT: ".$cardType." Result: ".$awardInReportUnit."<br>";
								//$awardInReportUnit = true;
								//$awardInReportUnit = false;
								//var_dump(" mem:".$memberNumberOK."<br>Date:".$dateRangeOK."<br>advType".$advType."<BR>InReport:".$awardInReportUnit);
								if ($memberNumberOK && 
									$dateRangeOK && 
									//$awardInReportUnit && 
									(strcasecmp($advType, HS_CSV_RANK_BS_RANK) == 0 || 
									strcasecmp($advType, HS_CSV_MERIT_BADGE) == 0 ||
									strcasecmp($advType, HS_CSV_RANK_BS_AWARD) == 0 ||
									strcasecmp($advType, HS_CSV_RANK_CS_LOOP) == 0 ||
									strcasecmp($advType, HS_CSV_RANK_CS_ADVENTURE) == 0 ||
									strcasecmp($advType, HS_CSV_RANK_CS_WEBELOS) == 0 ||
									$requirementFound == true)) {
								// Selected scout
								$selectedScout = $scoutName;
								// Need values
								$ScoutRow = array(HS_ARRAY_Scout_MemberNum=>$memberNum, HS_ARRAY_ADV_ScoutName => $scoutName,
													HS_ARRAY_Scout_CardType => $cardType,
													HS_ARRAY_ADV_Type => $advType, HS_ARRAY_ADV_Advancement => $Advancement, 
													HS_ARRAY_Scout_Advancement => $AdvancementAbbr,
													HS_ARRAY_ADV_Council => $councilName, HS_ARRAY_ADV_UnitType => $unitType, 
													HS_ARRAY_Scout_UnitNum => $unitNumber, HS_ARRAY_ADV_Date => $advDate, 
													HS_ARRAY_ADV_Category => $category, HS_ARRAY_ADV_StandardDate => $stdAdvDate,
													'Awarded' => $awarded);
								//error_log("cat :=: ".print_r($ScoutRow['Category'], true));
								//var_dump("ScoutRow:".$ScoutRow);
								//foreach ($GroupRow as $debugRow) {
								//	var_dump($debugRow);
								//}
								foreach ($ScoutRow as $a => $b) {
								//	echo "<br> A: ".$a." => ".$b;
//P-44									error_log(print_r($a, true)." :=: ".print_r($b, true));
								}
								//echo "<br>";
								$GroupRow[] = $ScoutRow;
								//var_dump ($GroupRow);
							} 
						}	// end not header row
					}
					fclose($handle);
				}
//				// Remove requirements for awarded ranks
//				$GroupRow = $this->hs_ScoutBookRemoveAwardedRanks($GroupRow, $ScoutRank);
				// Sort records
				$SortDirection = '';		
				$sortFilter = $l_checkedSort;
				//error_log('sortFilter:'.print_r($sortFilter, true).': sortDir:'.print_r($sortFilter, true).':');
				if (strlen($sortFilter) > 0 ) {
					//error_log(print_r($_POST['CardSort'], true));
					switch($l_cardSort){
						case HS_PDF_SORT_FileOrder :
							$SortDirection = "";
							break;
						case HS_PDF_SORT_DateOrderAsc :
							$SortDirection = SORT_ASC;
							break;
						case HS_PDF_SORT_DateOrderDesc :
							$SortDirection = SORT_DESC;
							break;
						default:
							$SortDirection = "";					
							break;
					}
					if (strlen($SortDirection) > 0) {
						$StandardDateCol = array_column($GroupRow, 'StandardDate');
						if (array_multisort ($StandardDateCol, $SortDirection, $GroupRow) === true) {
						}
					}
				}
				$paperSelection = sanitize_text_field($_POST['PaperSize']);
				// Select Paper size and column/rows
				$pageSize = TCPDF_PDF_POSTER_LARGE;
				$pageCol = HS_PDF_POSTER_PORTRATE_COLS;
				$pageRow = HS_PDF_POSTER_PORTRATE_ROWS;
				$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
				switch ($paperSelection) {
					case TCPDF_PDF_ARCH_E :
						$pageSize = TCPDF_PDF_ARCH_E;
						$pageCol = HS_PDF_ARCH_E_PORTRATE_COLS;
						$pageRow = HS_PDF_ARCH_E_PORTRATE_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
						break;
					case TCPDF_PDF_ARCH_D_LAND :
						$pageSize = TCPDF_PDF_POSTER_LARGE;
						$pageCol = HS_PDF_ARCH_D_LAND_COLS;
						$pageRow = HS_PDF_ARCH_D_LAND_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_LANDSCAPE;
						break;
					case TCPDF_PDF_POSTER_LARGE :
						$pageSize = TCPDF_PDF_POSTER_LARGE;
						$pageCol = HS_PDF_POSTER_PORTRATE_COLS;
						$pageRow = HS_PDF_POSTER_PORTRATE_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
						break;
					case TCPDF_PDF_ARCH_C :
						$pageSize = TCPDF_PDF_ARCH_C;
						$pageCol = HS_PDF_ARCH_C_PORTRATE_COLS;
						$pageRow = HS_PDF_ARCH_C_PORTRATE_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
						break;
					case TCPDF_PDF_EN_COPY_DRAUGHT :
						$pageSize = TCPDF_PDF_EN_COPY_DRAUGHT;
						$pageCol = HS_PDF_LETTER_LANDSCAPE_COLS;
						$pageRow = HS_PDF_LETTER_LANDSCAPE_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
						break;
					case TCPDF_PDF_LETTER :
						$pageSize = TCPDF_PDF_LETTER;
						$pageCol = HS_PDF_LETTER_LANDSCAPE_COLS;
						$pageRow = HS_PDF_LETTER_LANDSCAPE_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_LANDSCAPE;
						break;
					default:
						$pageSize = TCPDF_PDF_POSTER_LARGE;
						$pageCol = HS_PDF_POSTER_PORTRATE_COLS;
						$pageRow = HS_PDF_POSTER_PORTRATE_ROWS;
						$pageOrientation = HS_PDF_SETTING_ORIENTATION_PORTRAIT;
					
				}
				//echo ("<br> start:");
				//echo "pagerow: ".$pageRow."<br>";
				// Get Graphic list
				$graphicBadgeList = $this->HS_ScoutBookLabelPrint_loadBadgeGraphics();
				//echo '<br> graphics loaded:'.count($graphicBadgeList).'<br>';
				//echo (" parm:".$pageOrientation.' : '.TCPDF_PDF_UNIT_PT.' : '.$pageSize);
				//echo "<br>".$reportType."<br>";
				if ($reportType == TCPDF_REPORT_TYPE_REPORT) {
					// Remove requirements for awarded ranks
					$GroupRow = $this->hs_ScoutBookRemoveAwardedRanks($GroupRow, $ScoutRank);
					// Generate report
					$GroupRow = $this->hs_ScoutBook_Report($GroupRow);
				}
				//var_dump("GroupRow:".$GroupRow);

				// Check for parameter and set default values
				// Create Cookies for selected values
				// Open PDF file 
					$pdf = new hs_ScoutBookLabelPrint_pdf($pageOrientation,TCPDF_PDF_UNIT_PT, $pageSize);
					$pdf->SetTitle('HoweScape ScoutBook Label Print');
					$pdf->SetAuthor('http://HoweScape.com');
					$csvFileName_sanitize = sanitize_file_name($_FILES["csv_file2"]["name"]);
					$pdf->SetTitleFileName($csvFileName_sanitize);
					$pdf->SetSubject($csvFileName_sanitize);
					$pdf->SetKeywords($keywords);
					$pdf->SetCreator("HoweScape ScoutBook LabelPrint");

					$pdf->SetMargins(HS_PDF_SETTING_MARGIN_LEFT, 
								  	 HS_PDF_SETTING_MARGIN_TOP, 
									 HS_PDF_SETTING_MARGIN_RIGHT); // PT
					$pdf->AddPage($pageOrientation,$pageSize);
					$pdf->SetFont(HS_PDF_SETTING_FONT,HS_PDF_SETTING_BOLD,$fontSize);
				
					$pdf->SetAutoPageBreak(false);
									

					$cardHeight = 262;	// 3.5 inch === 252 pt
					$cardWidth = 184; // 2.5 inch === 180 pt
					$mbWidth = 54;	// 1.5 inch === 108 pt
					$mbHeight = 54;
					$colNum = 0;
					$cardRow = 2;
					$rowNum = 0;
					$origin_x = $pdf->GetX();
					$origin_y = $pdf->GetY();
					$x1 = $origin_x;
					$y1 = $origin_y;
					$currentScoutName = "";
					//$pageRow = 10;
					//error_log(print_r('before loop: '.count($GroupRow), true));

					// if title bar active add centered title
					if (strlen($l_checkedTitle) > 0) {
						$originalFontSize = $pdf->getFontSizePt();
						$pdf->setFontSize(100);
						$pdf->SetXY($origin_x, $y1);
//						$chartWidthTitle = $cardWidth * $pageCol;
						$chartHeightTitle = $cardHeight * 1;
						$pdf->Cell(0, $chartHeightTitle,  $l_titleValue, $border, 1, 'C', 0, '', 0);
						$rowNum = $rowNum + 1;
						$colNum = $colNum + $pageCol;
						$origin_x = $origin_x - ($cardWidth * $pageCol);
						$origin_x1 = $origin_x + ($cardWidth * $colNum);
						$origin_y = $origin_y + $cardHeight;
					}

					
					foreach ($GroupRow as $singleCardGroup) {

						$x1 = $origin_x + 20;
						//var_dump($reportType);

						if ($reportType == TCPDF_REPORT_TYPE_REPORT) {

							//echo ("<br> single card dump report <Br>");
							//var_dump($singleCardGroup);
							$cardWidth = 184; // 2.5 inch === 180 pt
							$border = 0;
							if (strcmp($singleCardGroup['ScoutName'], $currentScoutName) != 0) {
								$currentScoutName = $singleCardGroup['ScoutName'];
								$pdf->SetXY($origin_x, $y1);
								$pdf->Cell($cardWidth, 10, $singleCardGroup['ScoutName'], $border, 1, "C", false); // Scout Name
								$rowNum = $rowNum + 1;

							}

							$x1 = $x1 + (10);
							$y1 = $origin_y + ($rowNum * 5);
							$pdf->SetXY($x1, $y1);
							$pdf->Cell($cardWidth+40, 20, $singleCardGroup['Type'], $border, 1, "R", false); // Scout Name
							$x1 = $x1 + (250);
							//$y1 = $y1 - (25);
							$pdf->SetXY($x1, $y1);
							$pdf->Cell($cardWidth+40, 20, $singleCardGroup['Advancement'], $border, 1, "L", false); // Scout Name

							$rowNum = $rowNum + 2;
							//$colNum = $colNum + 1;	// Increment counter
							if ($rowNum > 80) {
								$pdf->AddPage($pageOrientation,$pageSize);
								//$origin_y = $origin_y - ($cardHeight * $rowNum);
								$origin_x = $pdf->GetX();
								$origin_y = $pdf->GetY();
								$rowNum = 1;
							}							
							//$origin_y1 = $origin_y1 + $cardHeight;
							$pdf->SetXY($x1, $y1);
							
						} else {
						
							$cardCreated = false;
							$cardCreated = $this->hs_ScoutBookLabelPrint_advancementSingleCard($singleCardGroup, $pdf, $pageOrientation, $graphicBadgeList, $scoutArray, $reportUnitType, $colNum, $pageCol, $pageRow, $pageOrientation, $pageSize);
						

						if ($cardCreated == true) {
				
							$colNum = $colNum + 1;	// Increment counter
							//echo("<br> colNum:".$colNum." : ".$rowNum." : ".$colNum % HS_PDF_POSTER_PORTRATE_COLS);

							if ($colNum % $pageCol != 0) {
								$origin_x1 = $origin_x + ($cardWidth * $colNum);
							} else {
								//echo("<br> colNum:".$colNum." : ".$rowNum." : ".$colNum % HS_PDF_POSTER_PORTRATE_COLS);
								$rowNum = $rowNum + 1;
								$origin_x = $origin_x - ($cardWidth * $pageCol);
								$origin_x1 = $origin_x + ($cardWidth * $colNum);
								$origin_y = $origin_y + $cardHeight;
							}

							if ($colNum > 0) {
								//if (($colNum % (4 * 2))  == 0) {
								if (($colNum % ($pageCol * $pageRow))  == 0) {
									//error_log(print_r("<br>"."pageBreak: "."<br>"));
									$pdf->AddPage($pageOrientation,$pageSize);
									//$origin_y = $origin_y - ($cardHeight * $rowNum);
									$origin_x1 = $pdf->GetX();
									$origin_y = $pdf->GetY();
								}							
							}

							//$origin_y1 = $origin_y1 + $cardHeight;
							$pdf->SetXY($origin_x1, $origin_y);

							}
						}
					}
					$pdf->AddPage($pageOrientation,$pageSize);
					// Add HoweScape Settings info as a card
					$this->hs_ScoutBookLabelPrint_advancementSettingsCard($pdf, $pageOrientation, $scoutArray, $reportUnitType, $colNum, $pageCol, $pageRow, $pageOrientation,$pageSize);
						// What info do I want to store, Number of cards, Number of pages, 
						// Input file card, Scouts, Date range, Sort flag,

					// HoweScapeAdvCard_name_fromDate_toDate.pdf
					$pdfName = 'HoweScapeAdvCard_';
					if (strcmp($memberID,"-1") == 0) {
						$pdfName = $pdfName . 'AllScouts';
					} else {
						$selectedScout = $this->hs_ScoutBookGetScoutName ( $scoutArray, $l_SelectedScout);
						$pdfName = $pdfName . $selectedScout;
						//$pdfName = $pdfName . $scoutName;
					}
//				error_log(print_r("<br> scout:".$scoutName."<br>root:".$pdfName."<br>"));
					if ($l_checkedDate == 'DateFilter') {
						$pdfName = $pdfName . $l_startDate . '-' . $l_endDate;
					}
					$pdfName = $pdfName . '.pdf';
					$pdfName = str_replace(' ', '_', $pdfName);
					$pdf->Output($pdfName);	 
			} else {
				die ('Security check');
			}
			
			} else {
				// How to handle input file missing
				echo $my_missingAdvancementInput;
				exit;		
			}
		}
	}

	function hs_ScoutBookLabelPrint_loadAdvHaystack($xmlValues, $index) {
		$haystack_list = array();
		$selection = 0;
		//error_log (" load haystack".$index);
		foreach ($xmlValues as $singleRow) {
			if ($selection == $index && $singleRow != -1) {
				//error_log (" row: ".$selection);
				//error_log ($singleRow);
				$haystack_list[] = $singleRow;
			} elseif ($singleRow == -1) {
				$selection = $selection + 1;
			}
		}
		return $haystack_list;
	}

	function hs_ScoutBookLabelPrint_loadAwardLineBreak($xmlValues, $index) {
		$awardLineBreak_lists = array();
		$selection = 0;
		foreach ($xmlValues as $singleRow => $singleValue) {
			if ($selection == $index && $singleValue != -1) {
				//echo $selection." :: ".$singleRow." => ".$singleValue." - <br>";
				foreach ($singleValue as $a => $b) {
//					foreach ($b as $c => $d) {
//					//	echo " lev: ".$c." => ".$d."<br>";
//					}
				}
				//echo $singleRow."<br>";
				$awardLineBreak_lists[] = $singleValue;
			} elseif ($singleValue == -1) {
				$selection = $selection + 1;
			}
		}
		return $awardLineBreak_lists;
	}

	function hs_ScoutBookCheckCategory($inAdvancement) {
		$category = '';
		if ((strcmp($inAdvancement, HS_CSV_RANK_NAME_LION) == 0) ||
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_BOBCAT) == 0) || 
			    (strcmp($inAdvancement, HS_CSV_RANK_NAME_TIGER) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_WOLF) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_BEAR) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_WEBELOS) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_ARROW) == 0)) {
			$category = HS_SBLP_UNIT_CUB;
		} else if ((strcmp($inAdvancement, HS_CSV_RANK_NAME_SCOUT) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_TENDERFOOT) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_SECOND) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_FIRST) == 0) || 
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_STAR) == 0) ||
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_LIFE) == 0) ||
				(strcmp($inAdvancement, HS_CSV_RANK_NAME_EAGLE) == 0)) {
			$category = HS_SBLP_UNIT_BOY;
		}
		
		return $category;
	}
	
	function hs_ScoutBookLabelPrint_getRankAssociated($advType, $advName, $inGraphicBadgeList) {
		$returnRank = "";
		$lc_advName = mb_strtolower($advName);
		//error_log("Cat :-: ".(string)print_r($category, true).":-:".print_r($advType, true).":-:".print_r($advName, true).":=" );
		foreach ($inGraphicBadgeList as $singleBadge) {
			$lc_listName = (string)mb_strtolower($singleBadge[HS_ARRAY_Graphic_Name]);
			$lc_rank = (string)$singleBadge[HS_ARRAY_Graphic_Rank];
			$lc_advType = (string)$singleBadge[HS_ARRAY_Graphic_Type];
			$lc_category = (string)$singleBadge[HS_ARRAY_Graphic_Category];
			//error_log("in AdvType:".$advType.":lc:".$lc_advType." AdvName: ".$advName." lc:".$lc_listName);
			if (strcmp($lc_advType, $advType) == 0 && (strpos($lc_listName, $lc_advName) !== false && strlen($lc_rank) > 0)) {
				//error_log("type:-: ".print_r($lc_category, true).":-:".print_r($lc_advType, true).":-:".print_r($lc_listName, true));
				$returnRank = $lc_rank;
				break;
			} else {
				$returnRank = "";
			}
		}
		//error_log("RETURN :: ".print_r($returnRank, true));
		return $returnRank;
	}	

	// Function to create card which contains settings information
	function hs_ScoutBookLabelPrint_advancementSettingsCard($pdf, $inPageOrientation, $inScoutArray, $reportUnitType, $colNum, $pageCol, $pageRow, $pageOrientation,$pageSize) {
		global	$cookieValues;
		global	$colNum;
		global	$fontSize;
		global	$labelRow;
		global	$numRow;
		global	$numCol;

		global	$awardLineBreak;

		$count = 0;
		$cardHeight = 262;	// 3.5 inch === 252 pt
		$cardWidth = 184; // 2.5 inch === 180 pt
		$mbWidth = 54;	// 1.5 inch === 108 pt
		$mbHeight = 54;
		$badgeAdjX = 62;
		$badgeAdjY = 30;

		$border = 0;
		$cardCreated = false;
		$origin_x1 = 0;

		$origin_x = $pdf->GetX();
		$origin_y = $pdf->GetY();

		//$this->hs_ScoutBookLabel_getCookie();

		$pdf->SetXY($origin_x, $origin_y);
		
		// outline
		$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");

		$pdf->SetFontSize(12);
		$x1 = $origin_x + ($cardWidth / 4); 
		//$y1 = $origin_y + (($cardHeight / 8) *2.75);
		$y1 = $origin_y + (($cardHeight / 16) *2.75);
		$pdf->SetXY($origin_x, $y1);
		$pdf->Cell($cardWidth, 10, "Settings Card", $border, 1, "C", false); // Scout Name
		
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['LabelStyle'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['UnitType'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['UnitNumber'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['FontSize'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['LabelPosition'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['Council'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['RankMessage'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['MeritBadgeMessage'],	 $border, 1, "C", false);
		//$pdf->Cell($cardWidth, 10, $cookieValues['SB_LABEL_PRINT']['OutputContent'],	 $border, 1, "C", false);
		
		$pdf->Cell($cardWidth, 10, $cookieValues['SB_ADVANCEMENT_CARD']['Council'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, $cookieValues['SB_ADVANCEMENT_CARD']['UnitType'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Selected Scout:".$cookieValues['SB_ADVANCEMENT_CARD']['SelectedScout'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Start Date:".$cookieValues['SB_ADVANCEMENT_CARD']['StartDate'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "End Date:".$cookieValues['SB_ADVANCEMENT_CARD']['EndDate'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Card Order:".$cookieValues['SB_ADVANCEMENT_CARD']['CardOrder'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Output Format:".$cookieValues['SB_ADVANCEMENT_CARD']['OutputFormat'],	 $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Output Size:".$cookieValues['SB_ADVANCEMENT_CARD']['OutputSize'],	 $border, 1, "C", false);
		$DateFlag = "False";
		if ( $cookieValues['SB_ADVANCEMENT_CARD']['DateFilter'] == 1) $DateFlag = "True";
		$SortFlag = "False";
		if ( $cookieValues['SB_ADVANCEMENT_CARD']['SortFilter'] == 1) $SortFlag = "True";
		$TitleFlag = "False";
		if ( $cookieValues['SB_ADVANCEMENT_CARD']['TitleFilter'] == 1) $TitleFlag = "True";
		$pdf->Cell($cardWidth, 10, "Date Filter:".$DateFlag, $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Sort Filter:".$SortFlag, $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Title Filter:".$TitleFlag, $border, 1, "C", false);
		$pdf->Cell($cardWidth, 10, "Title:".$cookieValues['SB_ADVANCEMENT_CARD']['TitleSample'], $border, 1, "C", false);
	}
	
	function hs_ScoutBookLabelPrint_advancementSingleCard($singleCardLines, $pdf, $inPageOrientation, $inGraphicBadgeList, $inScoutArray, $reportUnitType, $colNum, $pageCol, $pageRow, $pageOrientation,$pageSize) {
		global	$colNum;
		global	$fontSize;
		global	$labelRow;
		global	$numRow;
		global	$numCol;
		//global	$list_awardName;
		global	$awardLineBreak;

		$count = 0;
		$cardHeight = 262;	// 3.5 inch === 252 pt
		$cardWidth = 184; // 2.5 inch === 180 pt
		$mbWidth = 54;	// 1.5 inch === 108 pt
		$mbHeight = 54;
		$badgeAdjX = 62;
		$badgeAdjY = 30;
		//$imageSuffix = "_sm.jpg";
		$border = 0;
		$cardCreated = false;
		$origin_x1 = 0;

		//$lines = explode ( HS_MSG_NEWLINE, $singleCardLines);
		$origin_x = $pdf->GetX();
		$origin_y = $pdf->GetY();
		//error_log("singleCard".print_r($singleCardLines['Type'],true));
		//error_log("<br>singleCard".print_r($singleCardLines));
		// Check for type of card?
//echo "<br> colNum:".$colNum." RowNum: ".$rowNum." PageRow: ".$pageRow."<br>";
			if (strcmp($singleCardLines['Advancement'],"Mile Swim BSA") == 0) {
				//send_to_console ($singleCardLines['Type']);
//			var_dump('Type: '.$singleCardLines['Type']);
//			var_dump('Title: '.$singleCardLines['Advancement']);
			}
		$awardInReportUnit = true;
//		echo "<br> UnitType: ".$reportUnitType." <=> ".$singleCardLines[HS_ARRAY_ADV_Category]." <=> ".$singleCardLines[HS_ARRAY_ADV_Advancement];
		$awardInReportUnit = $this->hs_ScoutBook_TypeInReport ($reportUnitType, $singleCardLines[HS_ARRAY_ADV_Category]);
		//echo "SingleCard: ".$singleCardLines['Type']."<br>";
//		echo "<br> awardInReport:".$awardInReportUnit.":  cat:".$singleCardLines[HS_ARRAY_ADV_Category].":  Adv:".$singleCardLines['Advancement'].":<br>";
		//$awardInReportUnit = true;

//		if ($awardInReportUnit) {
//			echo "<br> card1: ".$singleCardLines['Advancement'];	
//		} else {
//			echo "<br> card2: ".$singleCardLines['Advancement'];
//		}
		
		if ($awardInReportUnit) {
		//foreach ($singleCardLines as $a => $b) {
		//	echo " A: ".$a." => ".$b."<br>";
		//}
		
		//echo "<BR>Type:".$singleCardLines['Type'].":<br>";
		
		if (strcasecmp($singleCardLines['Type'], HS_CSV_RANK_CS_LOOP) == 0) {
			$cardCreated = true;
			$pdf->SetXY($origin_x, $origin_y);
			// Info on card
			$advType = (string)$singleCardLines['Type'];
//			if (strcmp($singleCardLines['Advancement'],"Kayaking BSA") == 0) {
//				send_to_console ($advType);
//			}
			$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic(HS_SBLP_UNIT_CUB, $advType, $singleCardLines, $inGraphicBadgeList);
			//if (strcmp($singleCardLines['Advancement'], "Kayaking BSA") == 0) {
			//	send_to_console($ImageInfo);
			//}
			$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];
			//error_log(print_r($image2, true));
			if (file_exists($image2) == true) {
				if (strpos($image2, '.svg') === false) {
					$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
				} else {
					$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
				}
			}
			// outline
			$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");

			$pdf->SetFontSize(12);
			$x1 = $origin_x + ($cardWidth / 4); 
			$y1 = $origin_y + (($cardHeight / 8) *2.75);
			$pdf->SetXY($origin_x, $y1);
			$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name

			$x1 = $origin_x;
			$y1 = $origin_y + (($cardHeight / 8) *4);
			$pdf->SetXY($x1, $y1);
			$pdf->SetFontSize(12);
			$pdf->Cell($cardWidth, 10, $singleCardLines['Advancement'], $border, 1, "C", false);  // Rank
			
			// den pack date
			$x1 = $origin_x;
			$y1 = $origin_y + (($cardHeight / 8) *5);
			$pdf->SetXY($x1, $y1);
			$pdf->SetFontSize(10);
			$pdf->Cell($cardWidth, 10, "Den "." Pack "." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);  // Rank
			// Den Leader
			$x1 = $origin_x;
			$y1 = $origin_y + (($cardHeight / 8) *6);
			$pdf->SetXY($x1, $y1);
			$pdf->SetFontSize(10);
			$pdf->Cell($cardWidth, 10, "Den Leader ", $border, 1, "C", false);  // Rank
			
			$pdf->SetFontSize(5);
			$pdf->SetXY($x1, $y1);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
				
			// Cubmaster
			$x1 = $origin_x;
			$y1 = $origin_y + (($cardHeight / 8) *7);
			$pdf->SetXY($x1, $y1);
			$pdf->SetFontSize(10);
			$pdf->Cell($cardWidth, 10, "Cubmaster ", $border, 1, "C", false);  // Rank

			$pdf->SetFontSize(5);
			$pdf->SetXY($x1, $y1);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
			
		} elseif (strcasecmp($singleCardLines['Type'], HS_CSV_RANK_BS_AWARD) == 0) {
			$cardCreated = true;

			// Check UnitType to determine if Pack or Troop
		
			$pdf->SetXY($origin_x, $origin_y);
			// Info on card
				$advType = (string)$singleCardLines['Type'];
				$scoutType = $this->hs_ScoutBookLabelPrint_getScoutTypeBadge($singleCardLines, $inGraphicBadgeList);
				//$scoutType = $this->hs_ScoutBookLabelPrint_getScoutType ($singleCardLines, $inScoutArray);
				//$scoutType = $this->hs_ScoutBookLabelPrint_getScoutCategoryFromGraphic($singleCardLines, $inGraphicBadgeList);
				//error_log("Award:".$scoutType." ".$singleCardLines);
				//error_log("CardLines:".print_r($singleCardLines));
				//error_log(print_r($scoutType, true));
				$denNum = $this->hs_ScoutBookLabelPrint_getDenNum ($singleCardLines, $inScoutArray);
				$packNum = $this->hs_ScoutBookLabelPrint_getPackNum ($singleCardLines, $inScoutArray);
				$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic($scoutType, $advType, $singleCardLines, $inGraphicBadgeList);
//			if (strcmp($singleCardLines['Advancement'],"Kayaking BSA") == 0) {
//				$this->send_to_console ($ImageInfo);
//			}
//var_dump('lines: '.$singleCardLines);
//var_dump('ScoutType: '.$scoutType);
//var_dump($ImageInfo[HS_GRAPHICS_IMG_FULLPATH]);	
				$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];		
				if ($ImageInfo[HS_XML_GRAPHICS_Width]>0) {
					$mbWidth = $ImageInfo[HS_XML_GRAPHICS_Width];
					$badgeAdjX = $badgeAdjX - ($mbWidth/3.5);
				} else {
				}
				if ($ImageInfo[HS_XML_GRAPHICS_Height]>0) {
					$mbHeight = $ImageInfo[HS_XML_GRAPHICS_Height];
				} else {
				}
				//error_log(print_r($image2, true));
				if (file_exists($image2) == true) {
					if (strpos($image2, '.svg') === false) {
						//$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
						$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY - 10, $mbWidth, $mbHeight);
					} else {
						$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY - 10, $mbWidth, $mbHeight);
					}
				}

			// outline
			$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");
			$pdf->SetFontSize(12);
			$x1 = $origin_x + ($cardWidth / 4); 
			$y1 = $origin_y + (($cardHeight / 8) *2.75);
			$pdf->SetXY($origin_x, $y1);

			$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name

			// Choice Boy or CubRank
			if ($scoutType == HS_SBLP_UNIT_CUB) {
				$advText = $singleCardLines['Advancement'];
				$advText = str_replace('+', ' ', $advText);
				$this->hs_ScoutBookAdvancmentWithLineBreak($advText, $origin_x, $origin_y, $pdf, $awardLineBreak);

				// den pack date
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den ".$denNum." Pack "." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);  // Rank
				// Den Leader
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *6);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den Leader ", $border, 1, "C", false);  // Rank
				
				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
				
				// Cubmaster
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *7);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Cubmaster ", $border, 1, "C", false);  // Rank

				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
			} else if ($scoutType == HS_SBLP_UNIT_BOY) {
				//
				$pdf->SetFontSize(10);
		
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *3.30);
				$pdf->SetXY($origin_x, $y1);
				$pdf->Cell($cardWidth, 10, "Has met the requirements for the", $border, 1, "C", false);
				//$pdf->Text($x1, $y1, "Has met the requirements for the");		
				$pdf->SetFontSize(10);
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *4);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(11);
				
				$y1 = $origin_y + ($cardHeight/12);
				$advText = $singleCardLines['Advancement'];
				$this->hs_ScoutBookAdvancmentWithLineBreak($advText, $x1, $y1, $pdf, $awardLineBreak);
				
				$pdf->SetFontSize(9);
				// Council
				$y1 = $origin_y + (($cardHeight / 8) * 5.5);
				$pdf->SetXY($origin_x, $y1);
				$pdf->Cell($cardWidth, 10, "Council ".$singleCardLines['Council'], $border, 1, "C", false); // Council
				// Unit ------ Date --
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *6);
				$pdf->SetXY($origin_x, $y1);
				//$pdf->Text($x1, $y1, "Unit"."Date");		
				$pdf->Cell($cardWidth, 10, "Unit ".$singleCardLines['UnitType']." ".$singleCardLines[HS_ARRAY_Scout_UnitNum]." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);	// unit date
				// Unit leader Signiture
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *7);
				$pdf->SetFontSize(5);
				$pdf->SetXY($origin_x, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($origin_x+($cardWidth/4), $y1, $origin_x + $cardWidth - ($cardWidth/4), $y1);
				$pdf->Cell($cardWidth, 10, "Unit Leader Signature", $border, 1, "C", false);
				//$pdf->Text($x1, $y1, "Unit Leader Signature");		
				$pdf->SetFontSize(10);
				// Boy Scouts of America
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *7.5);
				$pdf->SetXY($origin_x, $y1);
				//$imageLogo = plugin_dir_path(__FILE__)."images/BoyScoutOfAmericaLogo.jpg";
				$imageLogo = plugin_dir_path(__FILE__)."images/BoyScoutsLogoTransparentBackground.png";
				//echo ($imageLogo."<br>");
				//$imageLogo = " ";
				$pdf->Cell($cardWidth, 10, "Boy Scouts     of America", $border, 1, "C", false);
				$pdf->Cell($cardWidth, 10, $pdf->Image($imageLogo, $origin_x+($cardWidth/2)-7, $y1-5, 13, 13), $border, 1, "C", false);
				
			}
			
			//Start routine
			/*
			$advLineBr = strpos($advText, ' - ');
			$advGradeBr = strPos($advText, '(Grade');
			if ($advLineBr > 0) {
				$advText1 = substr($advText,0, $advLineBr);
				$advText2 = substr($advText, $advLineBr+3); // Add length of needle
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *3.5);
				$pdf->SetXY($x1, $y1);
				$pdf->Cell($cardWidth, 10, $advText1, $border, 1, "C", false);  // award name line 1
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *4.25);
				$pdf->SetXY($x1, $y1);
				$pdf->Cell($cardWidth, 10, $advText2, $border, 1, "C", false);  // award name line 2
			} else if ($advGradeBr > 0) { // Break at '(Grade'
				$advText1 = substr($advText,0, $advGradeBr);
				$advText2 = substr($advText, $advGradeBr); //
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *3.5);
				$pdf->SetXY($x1, $y1);
				$pdf->Cell($cardWidth, 10, $advText1, $border, 1, "C", false);  // award name line 1
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *4.25);
				$pdf->SetXY($x1, $y1);
				$pdf->Cell($cardWidth, 10, $advText2, $border, 1, "C", false);  // award name line 2
			} else {
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *4);
				$pdf->SetXY($x1, $y1);
				$pdf->Cell($cardWidth, 10, $advText, $border, 1, "C", false);  // Rank
			}
			*/
			// End routine
			
		} elseif (strcasecmp($singleCardLines['Type'], HS_CSV_RANK_BS_RANK) == 0) {
			$cardCreated = true;

			// Need to be able to seperate Cub Scout Rank and Boy Scout Rank
			if ((strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_LION) == 0) ||
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_BOBCAT) == 0) || 
			    (strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_TIGER) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_WOLF) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_BEAR) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_WEBELOS) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_ARROW) == 0)) {
				$this->hs_ScoutBookLabelPrint_cubScoutCard ($singleCardLines, $pdf, $origin_x, $origin_y, $inGraphicBadgeList, $inScoutArray);
/*				$denNumber = "   ";
				if (strlen() > 0) {
					$packNumber = $singleCardLines['UnitNum']
				} else {
					$packNumber = "   ";
				}
				if (strlen($singleCardLines[HS_ARRAY_ADV_Date]) > 0) {
					$awardDate = $singleCardLines[HS_ARRAY_ADV_Date]
				} else {
					$awardDate = "        ";
				}
				// Cub Scout Rank Cards
				// Cub Scouts
				// Image
				// Scout
				// BOBCAT BADGE
				// Den ____ Pack _____ Date ____
				// Den Leader ________
				// Cubmaster ________
				$pdf->SetXY($origin_x, $origin_y);
				// Info on card
				// outline
				$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");

				// bluebox
				$pdf->SetDrawColor(0, 102, 255); // Cub Scout 
				$pdf->SetFillColor(0, 102, 255);
				$pdf->Rect($origin_x, $origin_y,$cardWidth, $cardHeight/3, "F");
				$pdf->SetDrawColor(0, 0, 0); // Restore Black default
				$pdf->SetFillColor(0, 0, 0);		
				// gold box
				$this->hs_ScoutBookLabelPrint_upperBox($pdf, $origin_x, $origin_y, $cardWidth, $cardHeight, $lineWidth);
				$pdf->SetFontSize(12);
	//			$pdf->TextWithRotation($origin_x+10, $origin_y+($cardHeight/4),'Cub Scout',90,0);
				$pdf->TextWithRotation($origin_x+40, $origin_y,'Cub Scout',90,0);
				// Cub Advancement Logo
				$image2 = $this->hs_ScoutBookLabelPrint_selectGraphic($singleCardLines['Advancement'], $inGraphicBadgeList);
				//error_log(print_r($image2, true));
				if (file_exists($image2) == true) {
					if (strpos($image2, '.svg') === false) {
						$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					} else {
						$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					}
				}

				$pdf->SetFontSize(12);
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *2.75);
				$pdf->SetXY($origin_x, $y1);
				$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name
				// under line
				// earned the
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *3.40);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "earned the", $border, 1, "C", false);  // Rank				
				// rank name
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *4);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(12);
				$pdf->Cell($cardWidth, 10, $singleCardLines['Advancement']." BADGE", $border, 1, "C", false);  // Rank
				// den pack date
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den ".$denNumber." Pack ".$singleCardLines['UnitNum']." Date ".$awardDate, $border, 1, "C", false);  // Rank
				// Den Leader
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *6);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den Leader ", $border, 1, "C", false);  // Rank
				
				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
				
				// Cubmaster
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *7);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Cubmaster ", $border, 1, "C", false);  // Rank

				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
*/
			} else if ((strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_SCOUT) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_TENDERFOOT) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_SECOND) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_FIRST) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_STAR) == 0) ||
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_LIFE) == 0) ||
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_EAGLE) == 0)) {
				$pdf->SetXY($origin_x, $origin_y);
				// Info on card
				//error_log("MemberNum".print_r($singleCardLines['MemberNum'], true));
				//error_log("ScoutName".print_r($singleCardLines['ScoutName'], true));
				//error_log("AdvType".print_r($singleCardLines['AdvType'], true));
				//error_log("Advancement".print_r($singleCardLines['Advancement'], true));

				//error_log("Council".print_r($singleCardLines['Council'], true));
				//error_log("UnitType".print_r($singleCardLines['UnitType'], true));
				//error_log("UnitNum".print_r($singleCardLines['UnitNum'], true));
				//error_log("Date".print_r($singleCardLines[HS_ARRAY_ADV_Date], true));
				$advType = (string)$singleCardLines['Type'];
				$scoutType = $this->hs_ScoutBookLabelPrint_getScoutTypeBadge($singleCardLines, $inGraphicBadgeList);
				//$scoutType = $this->hs_ScoutBookLabelPrint_getScoutType ($singleCardLines, $inScoutArray);
				$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic($scoutType, $advType, $singleCardLines, $inGraphicBadgeList);
				$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];
				//error_log(print_r($image2));
				if (file_exists($image2) == true) {
					if (strpos($image2, '.svg') === false) {
						$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					} else {
						$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					}
				}
				// outline
				$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");

				$pdf->SetFontSize(12);
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *2.75);
				$pdf->SetXY($origin_x, $y1);
				$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name

				$pdf->SetFontSize(10);
		
				$x1 = $origin_x;// + ($cardWidth / 24); 
				$y1 = $origin_y + (($cardHeight / 8) *3.60);
				$pdf->SetXY($x1, $y1);
				$pdf->Cell($cardWidth, 10, "IS HEARBY CERTIFIED AS A", $border, 1, "C", false);		// message
				//$pdf->Text($x1, $y1, "Has met the requirements for the");		
				$pdf->SetFontSize(10);
				//$x1 = $x;

				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *4.5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(18);
				$pdf->Cell($cardWidth, 10, $singleCardLines['Advancement'], $border, 1, "C", false);  // Rank
			
				// Unit ------ Date --
				$pdf->SetFontSize(9);
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *5.5);
				$pdf->SetXY($origin_x, $y1);
				//$pdf->Text($x1, $y1, "Unit"."Date");		
				$pdf->Cell($cardWidth, 10, $singleCardLines['UnitType']." ".$singleCardLines[HS_ARRAY_Scout_UnitNum]." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);	// unit date
				// Council
				$y1 = $origin_y + (($cardHeight / 8) * 6);
				$pdf->SetXY($origin_x, $y1);
				$pdf->Cell($cardWidth, 10, "Council ".$singleCardLines['Council'], $border, 1, "C", false); // Council
				// Unit leader Signiture
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *7);
				$pdf->SetFontSize(5);
				$pdf->SetXY($origin_x, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($origin_x+($cardWidth/4), $y1, $origin_x + $cardWidth - ($cardWidth/4), $y1);
				$pdf->Cell($cardWidth, 10, "Unit Leader Signature", $border, 1, "C", false);
				//$pdf->Text($x1, $y1, "Unit Leader Signature");		
				$pdf->SetFontSize(10);
				// Boy Scouts of America
				$x1 = $origin_x + ($cardWidth / 4); 
				$y1 = $origin_y + (($cardHeight / 8) *7.5);
				$pdf->SetXY($origin_x, $y1);
				//$imageLogo = plugin_dir_path(__FILE__)."images/BoyScoutOfAmericaLogo.jpg";
				$imageLogo = plugin_dir_path(__FILE__)."images/BoyScoutsLogoTransparentBackground.png";
				//echo ($imageLogo."<br>");
				//$imageLogo = " ";
				$pdf->Cell($cardWidth, 10, "Boy Scouts     of America", $border, 1, "C", false);
				$pdf->Cell($cardWidth, 10, $pdf->Image($imageLogo, $x1+($cardWidth/2)-7, $y1-5, 13, 13), $border, 1, "C", false);
			
			} else { // Not CS rank
				// outline
				$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");
			}
			$lineWidth = 1;
		} else if (strcasecmp($singleCardLines['Type'], HS_CSV_MERIT_BADGE) == 0) {
			$cardCreated = true;
			
			// Add background for page.
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->SetXY($origin_x, $origin_y);
			// Info on card
			// MB image
			// Has met the requirements for the
			// - merit badge name
			// MERIT BADGE
			// Council --------------
			// Unit ------ Date -------------
			// Unit Leader signiture
			// Boy Scouts of America
		
			// outline
			$pdf->Rect($x, $y, $cardWidth, $cardHeight, "D");
			$lineWidth = 1;
			$pdf->SetLineWidth($lineWidth);
			// MB Image
			$advType = (string)$singleCardLines['Type'];
				$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic(HS_SBLP_UNIT_BOY, $advType, $singleCardLines, $inGraphicBadgeList);
				$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];
				//error_log(print_r($image2, true));
				if (file_exists($image2) == true) {
					if (strpos($image2, '.svg') === false) {
						$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					} else {
						$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					}
				}
			//$mbSize = 50;
			//$mbOffset = 20;
			// build mb image
			//$x1 = $origin_x + ($cardWidth/2) - ($mbSize/2);
			//$y1 = $origin_y + $mbOffset;
			
			//$badgeWidth = $mbWidth;
			//$badgeHeight = $mbHeight;
			//$mbName = trim($singleCardLines['Advancement']);
			//$mbName = strtolower($mbName);
			//$rankMBImage = str_replace(" ", "_", strtolower($mbName));
			//$image2 = plugin_dir_path(__FILE__)."images/".$rankMBImage.$imageSuffix;
			//$image2 = "http://clipart.usscouts.org/library/BSA_Boy_Scout_MeritBadges/mb015c.gif";
			//$image2 = "https://retailobjects.scoutshop.org/media/catalog/product/cache/7fddc48eca108f91c58a013a7169a988/6/3/637667.jpg";
			//$image2 = "https://i9peu1ikn3a16vg4e45rqi17-wpengine.netdna-ssl.com/wp-content/uploads/2019/09/merit-badge-AmericanBusiness.svg";
			//echo ($image2."<br>");
			//if (file_exists($image2) == true) {
//				$pdf->Cell( $x1, $y1, $pdf->Image($image2, $x1+$badgeAdjX, $y1+$badgeAdjY, $badgeWidth, $badgeHeight), 0, 0, 'L', false );
			//	$pdf->Cell( $x1, $y1, $pdf->Image($image2, $x1, $y1, $badgeWidth, $badgeHeight), 0, 0, 'L', false );
			//}
			$this->hs_ScoutBookLabelPrint_upperTrangle($pdf, $x, $y, $cardWidth, $cardHeight, $lineWidth);
			$this->hs_ScoutBookLabelPrint_lowerTrangle($pdf, $x, $y, $cardWidth, $cardHeight, $lineWidth);
		
			
//			$x1 = $x + ($cardWidth/2) - ($mbSize/2);
//			$y2 = $y + $mbOffset;
//			$pdf->Rect($x1, $y2, 54, 54, "F");
			// name
			//$pdf->Cell(0, 10, $lines[0], $border, 1, "C", false);
			$pdf->SetFontSize(12);
			$x1 = $x + ($cardWidth / 4); 
			$y1 = $y + (($cardHeight / 8) *2.75);
			$pdf->SetXY($x, $y1);
			$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name

			//		$pdf->Text($x1, $y1, "Scout Name");		
			$pdf->SetFontSize(10);
		
			$x1 = $x + ($cardWidth / 4); 
			$y1 = $y + (($cardHeight / 8) *3.30);
			$pdf->SetXY($x, $y1);
			$pdf->Cell($cardWidth, 10, "Has met the requirements for the", $border, 1, "C", false);
			//$pdf->Text($x1, $y1, "Has met the requirements for the");		
			$pdf->SetFontSize(10);
			$x1 = $x;
			$y1 = $y + (($cardHeight / 8) *4);
			$pdf->SetXY($x1, $y1);
			$pdf->SetFontSize(12);
			$pdf->Cell($cardWidth, 10, $singleCardLines['Advancement'], $border, 1, "C", false);  // MB name

			// Merit Badge text
			$pdf->SetFontSize(12);
			$x1 = $x + ($cardWidth / 4); 
			$y1 = $y + (($cardHeight / 8) *4.75);
			//$pdf->Text($x1, $y1, HS_CSV_MERIT_BADGE);		
			$pdf->SetXY($x, $y1);
			$pdf->Cell($cardWidth, 10, HS_CSV_MERIT_BADGE, $border, 1, "C", false); // MB Text
			$pdf->SetFontSize(10);
			// Council
			$y1 = $y + (($cardHeight / 8) * 5.5);
			$pdf->SetXY($x, $y1);
			$pdf->Cell($cardWidth, 10, "Council ".$singleCardLines['Council'], $border, 1, "C", false); // Council
			// Unit ------ Date --
			$x1 = $x + ($cardWidth / 4); 
			$y1 = $y + (($cardHeight / 8) *6);
			$pdf->SetXY($x, $y1);
			//$pdf->Text($x1, $y1, "Unit"."Date");		
			$pdf->Cell($cardWidth, 10, "Unit ".$singleCardLines['UnitType']." ".$singleCardLines[HS_ARRAY_Scout_UnitNum]." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);	// unit date
			// Unit leader Signiture
			$x1 = $x + ($cardWidth / 4); 
			$y1 = $y + (($cardHeight / 8) *7);
			$pdf->SetFontSize(5);
			$pdf->SetXY($x, $y1);
			$pdf->SetDrawColor(0, 0, 0);
			$pdf->Line($x+($cardWidth/4), $y1, $x + $cardWidth - ($cardWidth/4), $y1);
			$pdf->Cell($cardWidth, 10, "Unit Leader Signature", $border, 1, "C", false);
			//$pdf->Text($x1, $y1, "Unit Leader Signature");		
			$pdf->SetFontSize(10);
			// Boy Scouts of America
			$x1 = $x + ($cardWidth / 4); 
			$y1 = $y + (($cardHeight / 8) *7.5);
			$pdf->SetXY($x, $y1);
			//$imageLogo = plugin_dir_path(__FILE__)."images/BoyScoutOfAmericaLogo.jpg";
			$imageLogo = plugin_dir_path(__FILE__)."images/BoyScoutsLogoTransparentBackground.png";
			//echo ($imageLogo."<br>");
			//$imageLogo = " ";
			$pdf->Cell($cardWidth, 10, "Boy Scouts     of America", $border, 1, "C", false);
			$pdf->Cell($cardWidth, 10, $pdf->Image($imageLogo, $x+($cardWidth/2)-7, $y1-5, 13, 13), $border, 1, "C", false);
			//$pdf->Text($x1, $y1, "Boy Scouts of America");
//		$pdf->SetDrawColor(207, 181, 59); 
//		//$pdf->Line($x1, $y1, $x2, $y2); // draw diag
//		$x2 = $x + $cardWidth - ($lineWidth/2);
//		$y2 = $y + ($lineWidth / 2);
//		$pdf->Line($x1, $y1, $x2, $y2); // draw top
//		for ($i = 0; $x1 < $x2; $i++) {
//			$x1 = $x + ($cardWidth / 2) + $lineWidth - 1 + $i;
//			$y1 = $y + ($lineWidth / 2) + $lineWidth - 1 + $i;
//			$y2 = $y1;//$y + ($cardWidth / 2) + $lineWidth - 1 + $i;
//			$pdf->Line($x1, $y1, $x2, $y2); // draw top 2			
//		}
//		// Second line top
//		$x1 = $x1 + $lineWidth - 1;
//		$y1 = $y1 + $lineWidth - 1;
//		$y2 = $y2 + $lineWidth - 1;
//		//$pdf->Line($x1, $y1, $x2, $y2); // draw top 2
//		// 3 line top
//		$x1 = $x1 + $lineWidth - 1;
//		$y1 = $y1 + $lineWidth - 1;
//		$y2 = $y2 + $lineWidth - 1;
//		//$pdf->Line($x1, $y1, $x2, $y2); // draw top 3
//
//		//$x2 = $x2 - ($lineWidth / 2);
//		$x1 = $x + $cardWidth - ($lineWidth / 2);
//		$y1 = $y + ($cardWidth / 2);
//		//$pdf->Line($x2, $y2, $x1, $y1); // draw side
//		//$pdf->Rect($x, $y, 54, 54, "F");
		//$pdf->output();
		} else if (strcasecmp($singleCardLines['Type'], HS_CSV_RANK_CS_ADVENTURE) == 0) {
			$cardCreated = true;
			
			// Add background for page.
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->SetXY($origin_x, $origin_y);
		
			// outline
			$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");
			// Get Rank associated and draw card background
			$rankCardType = $this->hs_ScoutBookLabelPrint_getRankAssociated((string)$singleCardLines['Type'], $singleCardLines['Advancement'], $inGraphicBadgeList);
			// Select Card/Color
			switch ($rankCardType) {
				case 'Lion' :
					$topColor = array(0, 102, 255);
					break;
				case 'Tiger' :
					$topColor = array(247, 91, 0);
					break;
				case 'Wolf' :
					$topColor = array(237, 28, 35);
					break;
				case 'Bear' :
					$topColor = array(0, 255, 255);
					break;
				case 'Webelos' :
					$topColor = array(0, 0, 255);
					break;
				case 'Arrow of Light' :
					$topColor = array(102, 255, 255);
					break;
				default :
					$topColor = array(255, 102, 0);
					break;
			}
			$style6 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $topColor);
			$pdf->Polygon(array($origin_x+$cardWidth,$origin_y, $origin_x+$cardWidth,$origin_y+$cardHeight,$origin_x,$origin_y+$cardHeight), 
							'DF',  array('all' => $style6), $topColor);				
			// Image
			$advType = (string)$singleCardLines['Type'];
			$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic(HS_SBLP_UNIT_CUB, $advType, $singleCardLines, $inGraphicBadgeList);
			$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];
			//error_log(print_r($image2, true));
			if (file_exists($image2) == true) {
				if (strpos($image2, '.svg') === false) {
					$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
				} else {
					$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
				}
			}


			$pdf->SetFontSize(12);
			$x1 = $origin_x + ($cardWidth / 4); 
			$y1 = $origin_y + (($cardHeight / 8) *2.75);
			$pdf->SetXY($origin_x, $y1);
			$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name

			$this->hs_ScoutBookAdvancmentWithLineBreak($singleCardLines['Advancement'], $origin_x, $origin_y, $pdf, $awardLineBreak);
			//$x1 = $origin_x;
			//$y1 = $origin_y + (($cardHeight / 8) *4);
			//$pdf->SetXY($x1, $y1);
			//$pdf->SetFontSize(12);
			//$pdf->Cell($cardWidth, 10, $singleCardLines['Advancement'], $border, 1, "C", false);  // Rank
				// den pack date
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den "." Pack "." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);  // Rank
				// Den Leader
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *6);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den Leader ", $border, 1, "C", false);  // Rank
				
				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
				
				// Cubmaster
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *7);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Cubmaster ", $border, 1, "C", false);  // Rank

				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);

		} else if (strcasecmp($singleCardLines['Type'], HS_CSV_RANK_CS_WEBELOS) == 0) {
			$cardCreated = true;
			
			// Add background for page.
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->SetXY($origin_x, $origin_y);
			// Info on card
		
			// outline
			$pdf->Rect($origin_x, $origin_y, $cardWidth, $cardHeight, "D");

			// Image
			$advType = (string)$singleCardLines['Type'];
				$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic(HS_SBLP_UNIT_CUB, $advType, $singleCardLines, $inGraphicBadgeList);
				$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];
				//error_log(print_r($image2, true));
				if (file_exists($image2) == true) {
					if (strpos($image2, '.svg') === false) {
						$pdf->Image($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					} else {
						$pdf->ImageSVG($image2, $origin_x+$badgeAdjX, $origin_y+$badgeAdjY, $mbWidth, $mbHeight);
					}
				}
			$pdf->SetFontSize(12);
			$x1 = $origin_x + ($cardWidth / 4); 
			$y1 = $origin_y + (($cardHeight / 8) *2.75);
			$pdf->SetXY($origin_x, $y1);
			$pdf->Cell($cardWidth, 10, $singleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name

			$x1 = $origin_x;
			$y1 = $origin_y + (($cardHeight / 8) *4);
			$pdf->SetXY($x1, $y1);
			$pdf->SetFontSize(12);
			$pdf->Cell($cardWidth, 10, $singleCardLines['Advancement'], $border, 1, "C", false);  // Rank
				// den pack date
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den "." Pack "." Date ".$singleCardLines[HS_ARRAY_ADV_Date], $border, 1, "C", false);  // Rank
				// Den Leader
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *6);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Den Leader ", $border, 1, "C", false);  // Rank
				
				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
				
				// Cubmaster
				$x1 = $origin_x;
				$y1 = $origin_y + (($cardHeight / 8) *7);
				$pdf->SetXY($x1, $y1);
				$pdf->SetFontSize(10);
				$pdf->Cell($cardWidth, 10, "Cubmaster ", $border, 1, "C", false);  // Rank

				$pdf->SetFontSize(5);
				$pdf->SetXY($x1, $y1);
				$pdf->SetDrawColor(0, 0, 0);
				$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);

		}
		//$pdf->SetXY($origin_x, $origin_y);
//	echo("<br> colNum:".$colNum." RowNum: ".$rowNum." : PageCol: ".$pageCol." COlNum 5 porate_cols ".$colNum % HS_PDF_POSTER_PORTRATE_COLS);		
/*
		$colNum = $colNum + 1;	// Increment counter

		//echo "colNUm: ".$colNum." pageCol: ".$pageCol."<br>";
		if ($colNum % $pageCol != 0) {
			//$origin_x1 = $origin_x + ($cardWidth * $colNum);
			$origin_x1 = $origin_x + $cardWidth ;
		} else {
//			echo("<br> colNum:".$colNum." RowNum: ".$rowNum." : PageCol: ".$pageCol." COlNum 5 porate_cols ".$colNum % HS_PDF_POSTER_PORTRATE_COLS);
			$rowNum = $rowNum + 1;
			//$colNum = 0;
			$pdf->SetXY($origin_x, $origin_y);
			$origin_x = $origin_x - ($cardWidth * $pageCol);
			$origin_x1 = $origin_x + ($cardWidth * $colNum);
			$origin_y = $origin_y + $cardHeight;
		}

//		if ($colNum > 0) {
//			//if (($colNum % (4 * 2))  == 0) {
			if (($colNum % ($pageCol * $pageRow))  == 0) {
//				//error_log(print_r("<br>"."pageBreak: "."<br>"));
//				$pdf->AddPage($pageOrientation,$pageSize);
//				//$origin_y = $origin_y - ($cardHeight * $rowNum);
//				$origin_x1 = $pdf->GetX();
//				$origin_y = $pdf->GetY();
			}							
//		}
*/
		//$origin_y1 = $origin_y1 + $cardHeight;
		//$pdf->SetXY($origin_x, $origin_y);
		$pdf->SetXY($origin_x1, $origin_y);		
		}
		return $cardCreated;
	}

	function hs_ScoutBook_TypeInReport ($reportUnitType, $cardType) {
		$inReport = true;
		$upperUnit = strtoupper($reportUnitType);
		$upperCard = strtoupper($cardType);
		//echo "<br> UnitType: ".$upperUnit." <=> ".$upperCard;
		$strcomp = (strpos ($upperUnit, $upperCard) === false);
		if ($strcomp) {
//			echo " rut: ".$upperUnit." CT: ".$upperCard." Comp: ".$strcomp."<Br>";
			$inReport = false;
		}		
		return $inReport;
	}

	function hs_ScoutBookLabelPrint_cubScoutCard($inSingleCardLines, $pdf, $inOrigin_x, $inOrigin_y, $inGraphicBadgeList, $inScoutArray) {
		$count = 0;
		$cardHeight = 262;	// 3.5 inch === 252 pt
		$cardWidth = 184; // 2.5 inch === 180 pt
		$mbWidth = 54;	// 1.5 inch === 108 pt
		$mbHeight = 54;
		$badgeAdjX = 62;
		$badgeAdjY = 30;
		$border = 0;
		$lineWidth = 1; // This variable can be removed
		
		$denNumber = "   ";
		$denNumber = $this->hs_ScoutBookLabelPrint_getDenNum ($inSingleCardLines, $inScoutArray);
		//if (strlen($inSingleCardLines['UnitNum']) > 0) {
		//	$packNumber = $inSingleCardLines['UnitNum'];
		//} else {
			$packNumber = "   ";
		//}
		if (strlen($inSingleCardLines[HS_ARRAY_ADV_Date]) > 0) {
			$awardDate = $inSingleCardLines[HS_ARRAY_ADV_Date];
		} else {
			$awardDate = "        ";
		}
		// Select Color
		switch ($inSingleCardLines['Advancement']) {
			case 'Bobcat' :
				$topColor = array(0, 102, 255);
				break;
			case 'Tiger' :
				$topColor = array(255, 102, 0);
				break;
			case 'Wolf' :
				$topColor = array(255, 51, 0);
				break;
			case 'Bear' :
				$topColor = array(0, 255, 255);
				break;
			case 'Webelos' :
				$topColor = array(102, 255, 255);
				break;
			case 'Arrow of Light' :
				$topColor = array(102, 255, 255);
				break;
			default :
				$topColor = array(255, 102, 0);
				break;
		}
		// Cub Scout Rank Cards
		// Cub Scouts
		// Image
		// Scout
		// BOBCAT BADGE
		// Den ____ Pack _____ Date ____
		// Den Leader ________
		// Cubmaster ________
		$pdf->SetXY($inOrigin_x, $inOrigin_y);
		// Info on card
		// outline
		$pdf->Rect($inOrigin_x, $inOrigin_y, $cardWidth, $cardHeight, "D");

		// advanced background box
		//$style6 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,10', 'color' => array(0, 128, 0));
		//$pdf->Curve($inOrigin_x, $inOrigin_y+($cardHeight/3)+20, $inOrigin_x+40, $inOrigin_y+($cardHeight/3)-15, $inOrigin_x+$cardwidth-15, $inOrigin_y+($cardHeight/3)-30, $inOrigin_x+$cardWidth, $inOrigin_y+$cardHeight/3, 'F', $style6);

		$style6 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $topColor);
		$pdf->Polygon(array($inOrigin_x,$inOrigin_y,$inOrigin_x+$cardWidth,$inOrigin_y,
							$inOrigin_x+$cardWidth,$inOrigin_y+($cardHeight/3),$inOrigin_x,$inOrigin_y+$cardHeight/3+30), 'DF',  array('all' => $style6), $topColor);				

		// bluebox
		//$pdf->SetDrawColorArray($topColor); // Cub Scout 
		//$pdf->SetFillColorArray($topColor);
		//$pdf->Rect($inOrigin_x, $inOrigin_y,$cardWidth, $cardHeight/3, "F");
		//$pdf->SetDrawColor(0, 0, 0); // Restore Black default
		//$pdf->SetFillColor(0, 0, 0);		
		// gold box
		$this->hs_ScoutBookLabelPrint_upperBox($pdf, $inOrigin_x, $inOrigin_y, $cardWidth, $cardHeight, $lineWidth);
		$pdf->SetFontSize(12);
	//	$pdf->SetXY($inOrigin_x, $inOrigin_y);
	//	$pdf->TextWithRotation($origin_x+10, $origin_y+($cardHeight/4),'Cub Scout',90,0);
	//	$pdf->TextWithRotation($inOrigin_x+40, $inOrigin_y,'Cub Scout',90,0);
	//	$pdf->TextWithRotation($inOrigin_x, $inOrigin_y,'Cub Scout',90,0);
		$cublogo = plugin_dir_path(__FILE__).'/images/Cubscouts/logo-cub-scout-title-90.png';
		//error_log ("<br> cubLogo:".$cublogo);
		//$pdf->Image($cublogo, $inOrigin_x, $inOrigin_y+10, $mbWidth-25, $mbHeight+15,'PNG','','',true);
		$pdf->Image($cublogo, $inOrigin_x, $inOrigin_y+10, $mbWidth-25, $mbHeight+15);
		// Cub Advancement Logo
		$advType = (string)$inSingleCardLines['Type'];
		//error_log("Type :=: CUB :=:".print_r($advType, true).":=:".print_r($inSingleCardLines['Advancement'], true));
		$ImageInfo = $this->hs_ScoutBookLabelPrint_selectGraphic(HS_SBLP_UNIT_CUB, $advType, $inSingleCardLines, $inGraphicBadgeList);
		$image2 = $ImageInfo[HS_GRAPHICS_IMG_FULLPATH];
		
		if ($ImageInfo[HS_XML_GRAPHICS_Width]>0) {
			$mbWidth = $ImageInfo[HS_XML_GRAPHICS_Width];
			$badgeAdjX = $badgeAdjX - ($mbWidth/7);
		} else {
		}
		if ($ImageInfo[HS_XML_GRAPHICS_Height]>0) {
			$mbHeight = $ImageInfo[HS_XML_GRAPHICS_Height];
			$badgeAdjY = $badgeAdjY - ($mbHeight/3.5);
		} else {
		}
		
		//error_log(print_r($image2, true));
		if (file_exists($image2) == true) {
			if (strpos($image2, '.svg') === false) {
				//error_log ("ImageParm:".$image2);
				//$xvalue = $inOrigin_x+$badgeAdjX;
				//$yvalue = $inOrigin_x+$badgeAdjX;
				//error_log ("ImageParm:".', '.$xvalue);
				//error_log ("ImageParm:".', '.$yvalue);
				//error_log ("ImageParm:".', '.$mbWidth.', '.$mbHeight);
				$pdf->Image($image2, $inOrigin_x+$badgeAdjX, $inOrigin_y+$badgeAdjY, $mbWidth, $mbHeight);
			} else {
				$pdf->ImageSVG($image2, $inOrigin_x+$badgeAdjX, $inOrigin_y+$badgeAdjY, $mbWidth, $mbHeight);
			}
		}

		$pdf->SetFontSize(12);
		$x1 = $inOrigin_x + ($cardWidth / 4); 
		$y1 = $inOrigin_y + (($cardHeight / 8) *2.75);
		$pdf->SetXY($inOrigin_x, $y1);
		$pdf->Cell($cardWidth, 10, $inSingleCardLines['ScoutName'], $border, 1, "C", false); // Scout Name
		// under line
		// earned the
		$x1 = $inOrigin_x;
		$y1 = $inOrigin_y + (($cardHeight / 8) *3.40);
		$pdf->SetXY($x1, $y1);
		$pdf->SetFontSize(10);
		$pdf->Cell($cardWidth, 10, "earned the", $border, 1, "C", false);  // Rank				
		// rank name
		$x1 = $inOrigin_x;
		$y1 = $inOrigin_y + (($cardHeight / 8) *4);
		$pdf->SetXY($x1, $y1);
		$pdf->SetFontSize(12);
		$pdf->Cell($cardWidth, 10, $inSingleCardLines['Advancement']." BADGE", $border, 1, "C", false);  // Rank
		// den pack date
		$x1 = $inOrigin_x;
		$y1 = $inOrigin_y + (($cardHeight / 8) *5);
		$pdf->SetXY($x1, $y1);
		$pdf->SetFontSize(10);
		$pdf->Cell($cardWidth, 10, "Den ".$denNumber." Pack ".$packNumber." Date ".$awardDate, $border, 1, "C", false);  // Rank
		// Den Leader
		$x1 = $inOrigin_x;
		$y1 = $inOrigin_y + (($cardHeight / 8) *6);
		$pdf->SetXY($x1, $y1);
		$pdf->SetFontSize(10);
		$pdf->Cell($cardWidth, 10, "Den Leader ", $border, 1, "C", false);  // Rank
				
		$pdf->SetFontSize(5);
		$pdf->SetXY($x1, $y1);
		$pdf->SetDrawColor(0, 0, 0);
		$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);
				
		// Cubmaster
		$x1 = $inOrigin_x;
		$y1 = $inOrigin_y + (($cardHeight / 8) *7);
		$pdf->SetXY($x1, $y1);
		$pdf->SetFontSize(10);
		$pdf->Cell($cardWidth, 10, "Cubmaster ", $border, 1, "C", false);  // Rank

		$pdf->SetFontSize(5);
		$pdf->SetXY($x1, $y1);
		$pdf->SetDrawColor(0, 0, 0);
		$pdf->Line($x1+($cardWidth/4), $y1, $x1 + $cardWidth - ($cardWidth/4), $y1);		
	}

	function hs_ScoutBookLabelPrint_getDenNum ($singleCardLines, $inScoutArray) {
		$denNum = '';
		$scoutNum = $singleCardLines[HS_ARRAY_Scout_MemberNum];
		foreach ($inScoutArray as $singleScout) {
			if ($scoutNum == $singleScout[HS_ARRAY_Scout_MemberNum]) {
				$denNum = $singleScout[HS_ARRAY_Scout_DenNum];
			}
		}
		return 	$denNum;
	}

	function hs_ScoutBookLabelPrint_upperBox($pdf, $x, $y, $cardWidth, $cardHeight, $lineWidth) {
		$pdf->SetDrawColor(0, 63, 135); // Cub Scout Blue
		$x1 = $x;
		$y1 = $y;
		$x2 = $x + $cardWidth;
		$y2 = $y;
		$stop = $y1 + ($cardHeight/4);
//		for ($i = 0; $y1 < $stop; $i++) {
		//for ($i = 0; $i < 10; $i++) {			
		//	$pdf->Line($x1, $y1, $x2, $y2);
		//	$y1 = $y1 + $lineWidth;
		//	$y2 = $y1;
		//}
		$width = $cardWidth/6;
		$height = $cardHeight/3;
		$pdf->SetDrawColor(252, 209, 22); // Cub Scout Yellow FCD116
		$pdf->SetFillColor(252, 209, 22);
		//$pdf->Rect($x, $y, $width, $height, "DF");
		$style6 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => HS_CARD_CUB_SCOUT_BLUE_COLOR);
		$pdf->Polygon(array($x1,$y1,
							$x1+$width,$y1,
							$x1+$width,$y1+$height+25,
							$x1,$y1+$height+30), 'DF', array('all' => $style6), HS_CARD_CUB_SCOUT_BLUE_COLOR);
//		// need new line TBD
//		$pdf->Polygon(array($x1, $y1, $x1+$width,$y1,$x1+$width,$y1+$height+25,$x1,$y1+$height+30),
//						'DF', array('all' => $style6), HS_CARD_CUB_SCOUT_BLUE_COLOR);
		$pdf->SetDrawColor(0, 0, 0); // Restore Black default
		$pdf->SetFillColor(0, 0, 0);		
	}

	function hs_ScoutBookLabelPrint_selectGraphic($category, $advType, $inSingleCardLines, $inGraphicBadgeList) {
		//error_log("Adv:".print_r($inSingleCardLines['Advancement'])."\n]r");
		//error_log("\nAdv:".print_r($inSingleCardLines['AdvancementAbbr']['Name'])."\n");
		//error_log("\nAdv:".print_r($inSingleCardLines['AdvancementAbbr']['Abbr'])."\n");
		//error_log("\nAdv:".print_r($inSingleCardLines['AdvancementAbbr']['MultiLine'])."\n");
		//error_log("Adv:".print_r($inSingleCardLines['Advancement'])."\n]r");
		$mbName = $inSingleCardLines['Advancement'];
		$mbNameAbbr = $inSingleCardLines['AdvancementAbbr']['Abbr'];
		$mbNameMultiLine = $inSingleCardLines['AdvancementAbbr']['MultiLine'];
		
		$lc_mbName = mb_strtolower($mbName);
		$lc_mbNameAbbre = mb_strtolower($mbNameAbbr);
		$lc_mbNameMultiLine = mb_strtolower($mbNameMultiLine);
		
		$lc_nmNameLength = strlen($lc_mbName);
		$lc_mbNameAbbrLength = strlen($lc_mbNameAbbre);
		$lc_mbNameMultiLineLength = strlen($lc_mbNameMultiLine);
		
		$returnGraphic = "";
		$lc_width = 0;
		$lc_height = 0;
		
		foreach ($inGraphicBadgeList as $singleBadge) {
			$lc_listName = (string)mb_strtolower($singleBadge[HS_ARRAY_Graphic_Name]);
			$lc_listNameLength = strlen($lc_listName);
			$lc_img = (string)$singleBadge[HS_ARRAY_Graphic_Img];
			$lc_advType = (string)$singleBadge[HS_ARRAY_Graphic_Type];
			$lc_category = (string)$singleBadge[HS_ARRAY_Graphic_Category];
//			$lc_width = $singleBadge[HS_XML_GRAPHICS_Width];
//			$lc_height = $singleBadge[HS_XML_GRAPHICS_Height];

			
			if ( strcmp($category, $lc_category) == 0 && strcmp($lc_advType, $advType) == 0 && $lc_nmNameLength > 0 && 
				$lc_listNameLength > 0 && strcmp($lc_listName, $lc_mbName) == 0) {
				$returnGraphic = $lc_img;
				$lc_width = $singleBadge[HS_XML_GRAPHICS_Width];
				$lc_height = $singleBadge[HS_XML_GRAPHICS_Height];
			} elseif (strcmp($category, $lc_category) == 0 && strcmp($lc_advType, $advType) == 0 &&$lc_mbNameAbbrLength > 0 && 
				$lc_listNameLength > 0 && strcmp($lc_listName, $lc_mbNameAbbre) == 0) {
				$returnGraphic = $lc_img;
				$lc_width = $singleBadge[HS_XML_GRAPHICS_Width];
				$lc_height = $singleBadge[HS_XML_GRAPHICS_Height];
			} elseif ( strcmp($category, $lc_category) == 0 && strcmp($lc_advType, $advType) == 0 && $lc_mbNameMultiLineLength > 0 && 
				$lc_listNameLength > 0 && strcmp($lc_listName, $lc_mbNameMultiLine) == 0) {
				$returnGraphic = $lc_img;
				$lc_width = $singleBadge[HS_XML_GRAPHICS_Width];
				$lc_height = $singleBadge[HS_XML_GRAPHICS_Height];
			} //else {
			//	error_log(" Img:".$returnGraphic." cat:".$advType." Type:".$category."    ");
			//	error_log("Source:".$lc_listName." name:".$lc_mbName." Abbr:".$lc_mbNameAbbre." Muli:".$lc_mbNameMultiLine."    ");
			//}
		}
//		error_log(" Img:".$returnGraphic." cat:".$advType." Type:".$category."    ");
//		error_log("Source:".$lc_listName." name:".$lc_mbName." Abbr:".$lc_mbNameAbbre." Muli:".$lc_mbNameMultiLine."    ");
/*		
		$lc_mbName = mb_strtolower($mbName);
		$lc_mnNameLength = strlen($lc_mbName);
		//echo ("<br> Looking for: ".$lc_mbName);
		//error_log("Cat :-: ".(string)print_r($category, true).":-:".print_r($advType, true).":-:".print_r($mbName, true).":=" );
		foreach ($inGraphicBadgeList as $singleBadge) {
			$lc_listName = (string)mb_strtolower($singleBadge[HS_ARRAY_Graphic_Name]);
			$lc_listNameLength = strlen($lc_listName);
			// Get short version of names
			$lc_mbNameShort = substr($lc_mbName, 0, $lc_listNameLength);
			//$lc_svg = (string)$singleBadge['SVG'];
			$lc_img = (string)$singleBadge[HS_ARRAY_Graphic_Img];
			$lc_advType = (string)$singleBadge[HS_ARRAY_Graphic_Type];
			$lc_category = (string)$singleBadge[HS_ARRAY_Graphic_Category];
			$lc_width = $singleBadge[HS_XML_GRAPHICS_Width];
			$lc_height = $singleBadge[HS_XML_GRAPHICS_Height];

			//if (strncmp( $lc_listName, "eagle palm pin #", 15) == 0) {
			//	error_log(" Graphic Name:".$lc_listName." Graphic Len:".$lc_listNameLength." ShortName:".$lc_mbNameShort);
			//	error_log("Cat:".$category." Type:".$lc_advType);
			//}
			//error_log("type:-: ".print_r($lc_category, true).":-:".print_r($lc_advType, true).":-:".print_r($lc_listName, true));
			//if (strcmp($category, $lc_category) == 0 && 
			//   strcmp($lc_advType, $advType) == 0 && 
			//	(strpos($lc_listName, $lc_mbName) !== false && strlen($lc_svg) > 0)) {
			////if (strcmp($lc_mbName, $lc_listName) == 0 && strlen($lc_svg) > 0) {
			//	$returnGraphic = $lc_svg;
			//	break;
			//} else if (strcmp($lc_mbName, $lc_listName) == 0 && strlen($lc_img) > 0) {
			//} else 
			//echo ("<br>".$lc_mbName.' : '.$lc_listName."<br>");
			//echo ("<br>".$lc_mbNameShort.' : '.$lc_listName."<br>");
			if (strcmp($category, $lc_category) == 0 && 
				strcmp($lc_advType, $advType) == 0 && 
				(strpos($lc_listName, $lc_mbName) !== false && strlen($lc_img) > 0)) {
				$returnGraphic = $lc_img;
				break;
			} else if (strcmp($category, $lc_category) == 0 && 
				strcmp($lc_advType, $advType) == 0 &&
				(strpos($lc_listName, $lc_mbNameShort) !== false && strlen($lc_img) > 0)) {
					//echo ("<br>".' : '.$lc_listName."<br>");
				$returnGraphic = $lc_img;
				break;
			} else {
				$returnGraphic = "";
			}
		}
*/
		//error_log("RETURN :: ".print_r($singleBadge['IMG'], true));
		//return plugin_dir_path(__FILE__).$returnGraphic;
		$pluginPath = str_replace('/',DIRECTORY_SEPARATOR, plugin_dir_path(__FILE__));
		$ImageInfo = array(HS_GRAPHICS_IMG_FULLPATH=> $pluginPath.$returnGraphic, 
							HS_XML_GRAPHICS_Width => $lc_width, 
							HS_XML_GRAPHICS_Height => $lc_height);
		//echo ("<br> Info:".$ImageInfo[HS_GRAPHICS_IMG_FULLPATH].':'.$ImageInfo[HS_XML_GRAPHICS_Width].":".$ImageInfo[HS_XML_GRAPHICS_Height].'<br>');
		return $ImageInfo;
	}

	// Get Scout type from badge list
	function hs_ScoutBookLabelPrint_getScoutTypeBadge ($singleCardLines, $inGraphicBadgeList) {
		$scoutType = HS_SBLP_UNIT_CUB;
		
		//error_log(print_r($singleCardLines['Advancement']));
		if (strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_SCOUT) == 0) {
//			error_log(print_r($singleCardLines));
		}
		
		//error_log(print_r($singleCardLines['AdvancementAbbr']));
		
		foreach ($inGraphicBadgeList as $singleBadge) {
			//error_log(print_r($inGraphicBadgeList));
//			error_log(print_r($singleCardLines['AdvancementAbbr']['Name']));
			//if (strcmp($singleBadge['Name'], $singleCardLines['Advancement']) == 0) {
		//	if (strcmp( $singleCardLines['Advancement'],'Eagle Palm Pin #1 (Bronze) [2017]') == 0) {
		//	//	$scoutType = HS_SBLP_UNIT_CUB	
//				error_log(print_r($singleBadge['Name']));
				//error_log(print_r($singleBadge));
		//	}
			if ((strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_SCOUT) == 0)  || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_TENDERFOOT) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_SECOND) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_FIRST) == 0) || 
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_STAR) == 0) ||
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_LIFE) == 0) ||
				(strcmp($singleCardLines['Advancement'], HS_CSV_RANK_NAME_EAGLE) == 0)) {
				$scoutType = HS_SBLP_UNIT_BOY;
//			if (strcmp($singleBadge['Name'], "Eagle Palm Pin #1 (Bronze)") == 0 &&
//				strcmp($singleBadge['Category'], HS_SBLP_UNIT_BOY) == 0 ) {
			} else if (
			(strcmp(trim($singleBadge['Name']," "), trim($singleCardLines['AdvancementAbbr']['Name'], " ")) == 0 ||
			 strcmp(trim($singleBadge['Name']," "), trim($singleCardLines['AdvancementAbbr']['Abbr'], " ")) == 0 ) &&
				strcmp($singleBadge['Category'], HS_SBLP_UNIT_BOY) == 0 ) {
				//error_log(print_r($singleBadge['Name']));
				//error_log(print_r($singleBadge['Category']));
				$scoutType = HS_SBLP_UNIT_BOY;
			}
		}
		return $scoutType;
	}
	
	function hs_ScoutBookLabelPrint_getScoutType ($singleCardLines, $inScoutArray) {
		$scoutType = HS_SBLP_UNIT_CUB;
		$PatrolDate = '';
		$scoutNum = $singleCardLines[HS_ARRAY_Scout_MemberNum];
		$AdvStdDate = $singleCardLines[HS_ARRAY_ADV_StandardDate];
		$minAgeForScouts = strtotime(HS_BSA_Scout_MinAge);
		//error_log(print_r("<br>".$inScoutArray[HS_ARRAY_Scout_DOB]));
		//error_log(print_r($singleCardLines));
		$dobDate = $inScoutArray[0][HS_ARRAY_Scout_DOB];
		foreach ($inScoutArray as $singleScout) {
			if ($scoutNum == $singleScout[HS_ARRAY_Scout_MemberNum]) {
				$TroopDate = $singleScout[HS_ARRAY_Scout_DateJoined];
				$dobDateStr = $singleScout[HS_ARRAY_Scout_DOB];
				$dobStdDate = strtotime($dobDate);
				//error_log(print_r($singleScout));
				//error_log(print_r(" troopDate:".$TroopDate."<br>"));
				//error_log(print_r("   AdvDate:".$AdvStdDate."<br>"));
				//var_dump($singleScout);
				//var_dump(" troopDate:".$TroopDate."<br>");
				//var_dump("   AdvDate:".$AdvStdDate."<br>");
				//var_dump(" dobDatStr:".$dobDateStr);
				//var_dump(" advDatStr:".$singleScout[HS_ARRAY_ADV_Date]);
			}
		}
		//$TroopStdDate = strtotime($dobDate);
		//$DOB_Date = date_create($dobDate);
		//$dob_Year = date('Y', $dobStdDate);
		///error_log(print_r("<br> dob-Y:".$dob_Year));
		//date_add(strtotime($dob_Year), '10 Year');
		//error_log(print_r("<br> dob-Y2:".strtotime('+10 Years')));
		//error_log(print_r("<br> DOB:".$dobDateStr."<br>"));
		$DOBDate=date_create($dobDateStr);
		if ($DOBDate === false) {
			//error_log(print_r("<br> DOB:".$dobDateStr."<br>"));
			$DOBDate = date_create();
		}
		//error_log(print_r("<br> date: ".$DOBDate));
		$dateIntervialValue = date_interval_create_from_date_string(HS_BSA_Scout_MinAge);
		date_add($DOBDate, $dateIntervialValue);
//		echo "<br>".date_format($DOBDate,"Y-m-d");
		$dobStdDate = strtotime(date_format($DOBDate,"Y-m-d"));
		//echo "<br>".date('l jS F (Y-m-d)', strtotime('+10 years'));
		//date_add($DOB_Date, date_interval_create_from_date_string(HS_BSA_Scout_MinAge));
		//date_add($TroopStdDate,date_interval_create_from_date_string(HS_BSA_Scout_MinAge));
		//$TroopStdDate = $dobStdDate + $minAgeForScouts; 
		//$TroopStdDate = strtotime($TroopDate);
		//echo '<br> AdvStdDate:'.$AdvStdDate.'<br>';
		//echo '<br> TroopStdDate:'.$TroopStdDate.'<br>';
		//echo '<br> patStdDate:'.$PatStdDate.'<br>';
	//	error_log(print_r("<br> DOB+10:".$DOB_Date."<Br>"));
//		error_log(print_r("<br> dob:".date('Y-m-d',$dobStdDate)."<br>"));
//		error_log(print_r("<br> adv:".date('Y-m-d',$AdvStdDate)."<br>"));
	//	error_log(print_r("<br> Tro:".date('Y-m-d',$DOB_Date)."<br>"));

	//	error_log(print_r("<br>Adv:".$AdvStdDate." : ".$DOB_Date."<br>"));
		//var_dump(' IntVal: '.$dateIntervialValue);
		//var_dump(' DOBRaw: '.$DOBDate);
		//var_dump(' DOB: '.$dobStdDate);
		if ($AdvStdDate < $dobStdDate) {
			$scoutType = HS_SBLP_UNIT_CUB;
//			error_log(print_r("<br> ScoutType1: Cub <br>"));
		} else {
			$scoutType = HS_SBLP_UNIT_BOY;
//			error_log(print_r("<br> ScoutType1: Boy <br>"));
		}
		//echo '<br> result:'.$scoutType.'<br>';
		//error_log ("CardType: ".$singleCardLines[HS_ARRAY_Scout_CardType]);
		if (strlen($singleCardLines[HS_ARRAY_Scout_CardType]) > 0 && 
			strcmp($singleCardLines[HS_ARRAY_Scout_CardType],HS_SBLP_UNIT_CUB)==0) {
			$scoutType = HS_SBLP_UNIT_CUB;
//			error_log(print_r("<br> ScoutType2: Cub <br>"));
		} elseif (strlen($singleCardLines[HS_ARRAY_Scout_CardType]) > 0 && 
				  strcmp($singleCardLines[HS_ARRAY_Scout_CardType],HS_SBLP_UNIT_BOY)==0) {
			$scoutType = HS_SBLP_UNIT_BOY;
//			error_log(print_r("<br> ScoutType2: Boy <br>"));
		}
		return $scoutType;
	}

	function hs_ScoutBookLabelPrint_upperTrangle($pdf, $x, $y, $cardWidth, $cardHeight, $lineWidth) {
		$pdf->SetDrawColor(207, 181, 59); 
		$x1 = $x + ($cardWidth / 2);
		$y1 = $y + ($lineWidth / 2);
		$x2 = $x + $cardWidth;
		$y2 = $y + ($cardWidth / 2);
//		//$pdf->Line($x1, $y1, $x2, $y2); // draw diag
//		$x2 = $x + $cardWidth - ($lineWidth/2);
//		$y2 = $y + ($lineWidth / 2);
//		$pdf->Line($x1, $y1, $x2, $y2); // draw top
//		for ($i = 0; $x1 < $x2; $i++) {
///			$x1 = $x + ($cardWidth / 2) + $lineWidth - 1 + $i;
//			$y1 = $y + ($lineWidth / 2) + $lineWidth - 1 + $i;
//			$y2 = $y1;//$y + ($cardWidth / 2) + $lineWidth - 1 + $i;
//			$pdf->Line($x1, $y1, $x2, $y2); // draw top 2			
//		}

		$triPoints = array();
		$triPoints[] = $x1;
		$triPoints[] = $y1;
		$triPoints[] = $x + $cardWidth;
		$triPoints[] = $y;
		$triPoints[] = $x1;
		$triPoints[] = $y + ($cardWidth / 2);
		$triStyle = "all";
		$triColor = array(207, 181, 59);
		
		//$pdf->SetLineStyle(array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(207, 181, 59)));
		$x3 = $x2;
		$y3 = $y + ($cardWidth/2);
		$style6 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(207, 181, 59));
		$pdf->Polygon(array($x1,$y,$x2,$y,$x2,$y3), 'DF',  array('all' => $style6), array(207, 181, 59));		
//		$pdf->polygon($triPoints, 'DF', $triStyle, $triColor); 
/*
• Polygon($p: array, $style: string, $line_style: array, $fill_color: array)

Draws a polygon. Parameters are:

- p: points. Array with values x0, y0, x1, y1,..., x(np-1), y(np-1).
- style: style of polygon (draw and/or fill: D, F, DF, FD).
- line_style: line style. Array with index (all => style) for all borders, or (0..np-1 => style) for each border. Style is an array like for SetLineStyle.
- fill_color: fill color. Array with components (red, green, blue)
*/		
		
	}

	function hs_ScoutBookLabelPrint_lowerTrangle($pdf, $x, $y, $cardWidth, $cardHeight, $lineWidth) {
		//$pdf->SetDrawColor(207, 181, 59); 
		$x1 = $x;
		$y1 = $y + $cardHeight;
		$x2 = $x1 + ($cardWidth/2);
		$y2 = $y1;
		//$pdf->Line($x1, $y1, $x2, $y2);
		//for ($i = 0; $x1 < $x2-1; $i++) {
		//	$x1 = $x;
		//	$y1 = $y  +  $cardHeight - $lineWidth  - $i;
		//	$x2 = $x1 + ($cardWidth/2) - $lineWidth - $i;
		//	$y2 = $y1;
		//	$pdf->Line($x1, $y1, $x2, $y2); 
		//}

		$x3 = $x1;
		$y3 = $y + ($cardWidth/2)+($cardHeight-$cardWidth);
		$style6 = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => HS_CARD_MERITBADGE_CORNER_COLOR);
		$pdf->Polygon(array($x1,$y1,$x2,$y2,$x3,$y3), 'DF',  array('all' => $style6), HS_CARD_MERITBADGE_CORNER_COLOR);				
	}

	function hs_ScoutBookLabelPrint_getPackNum ($singleCardLines, $inScoutArray) {
		$packNum = '';
		$scoutNum = $singleCardLines[HS_ARRAY_Scout_MemberNum];
		foreach ($inScoutArray as $singleScout) {
			if ($scoutNum == $singleScout[HS_ARRAY_Scout_MemberNum]) {
				//$packNum = $singleScout[];
			}
		}
		return $packNum;
	}

	function hs_ScoutBookAdvancmentWithLineBreak ($advText, $origin_x, $origin_y, $pdf, $awardLineBreak) {
		$cardHeight = 262;	// 3.5 inch === 252 pt
		$cardWidth = 184; // 2.5 inch === 180 pt
		$border = 0;

		$x1 = $origin_x;
		$y1 = $origin_y + (($cardHeight / 8) *4);
		$pdf->SetXY($x1, $y1);
		$pdf->SetFontSize(12);
		$lineBreakDone = false;
/*
		$lineBreaksInfo = array(array('lineBreakText1' => ' Conservation Segment', 'lineBreakIndex1' => 1),
								array('lineBreakText1' => ' No Trace Awareness ', 'lineBreakIndex1' => 1, 'lineBreakText2' => 'Award (Retired', 'lineBreakIndex2' => 1),
								array('lineBreakText1' => ' Conservation ', 'lineBreakIndex1' => 1, 'lineBreakText2' => ' v2015 (Wolf)', 'lineBreakIndex2' => 1),
								array('lineBreakText1' => ' Conservation ', 'lineBreakIndex1' => 1, 'lineBreakText2' => ' v2015 (Bear)', 'lineBreakIndex2' => 1),
								array('lineBreakText1' => 'ss BSA v2014 (Tiger) (Retired 12/31/2020)', 'lineBreakIndex1' => 3, 'lineBreakText2' => ' (Retired 12/31/2020)', 'lineBreakIndex2' => 1),
								array('lineBreakText1' => 'ss BSA v2014 (Wolf) (Retired 12/31/2020)', 'lineBreakIndex1' => 3, 'lineBreakText2' => ' (Retired 12/31/2020)', 'lineBreakIndex2' => 1),
								array('lineBreakText1' => ' - ', 'lineBreakIndex1' => 3),
								array('lineBreakText1' => '(Grade', 'lineBreakIndex1' => 0),
								array('lineBreakText1' => ' (LDS ', 'lineBreakIndex1' => 0),
								array('lineBreakText1' => ' Award (', 'lineBreakIndex1' => 1),
								array('lineBreakText1' => ' Activity ', 'lineBreakIndex1' => 1),
								array('lineBreakText1' => ' Award pin', 'lineBreakIndex1' => 1),
								array('lineBreakText1' => ' BSA v2014 ', 'lineBreakIndex1' => 1),
								array('lineBreakText1' => ' / ', 'lineBreakIndex1' => 1),
								array('lineBreakText1' => '(Duty to Country)', 'lineBreakIndex1' => 0) );
		// test array members
		error_log( "hard code ");
		foreach ($lineBreaksInfo as $oneLine) {
			foreach ($oneLine as $a => $b) {
				error_log ( "line:".$a."=>".$b.":");
			}
		}
		error_log ("Parm code ");
		foreach ($awardLineBreak as $oneLine) {
			foreach ($oneLine as $a => $b) {
					error_log ("line:".$a."=>".$b.":");
//				foreach ($b as $c => $d) {
//					error_log ("line:".$c."=>".$d.":<br>");
//				}
//				error_log(" skip");
			}
		}
*/
		$lineBreaksInfo = $awardLineBreak;
		// Emergency Preparedness BSA v2014 (Tiger) (Retired 12/31/2020)
		// Emergency Preparedness BSA v2014 (Wolf) (Retired 12/31/2020)
		//if (strpos($advText, 'ws for Action (Duty to Country)') > 0) {
		//	error_log(" adv: ".$advText." index: ".$needleIdx);
		//}
		//error_log(" adv: ".$advText);
								
		foreach ($lineBreaksInfo as $lineBreak) {
			$needleIdx = strpos($advText, $lineBreak['lineBreakText1']);
			if ($needleIdx > 0 && $lineBreakDone == false) {
				$advText1 = substr($advText,0, $needleIdx);
				$advText2 = substr($advText, $needleIdx+$lineBreak['lineBreakIndex1']); // Add length of needle
				//echo ('<br> Text: '.$advText.' Needle: '.$lineBreak['lineBreakText1'].' needleIdx:'.$needleIdx.'<br>');
				//echo ('<br> line1: '.$advText1.'<br>');
				//echo ('<br> line2: '.$advText2.'<br>');
				// check for third line
				if (array_key_exists('lineBreakText2', $lineBreak) && strpos($advText2, $lineBreak['lineBreakText2']) > 0) {
					$needleIdx2 = strpos($advText2, $lineBreak['lineBreakText2']);
					$advText3 = substr($advText2, $needleIdx2+$lineBreak['lineBreakIndex2']);
					$advText2 = substr($advText2, 0, $needleIdx2);
					$x1 = $origin_x;
					$y1 = $origin_y + (($cardHeight / 8) *3.28);
					$pdf->SetXY($x1, $y1);
					$pdf->Cell($cardWidth, 10, $advText1, $border, 1, "C", false);  // award name line 1
					$x1 = $origin_x;
					$y1 = $origin_y + (($cardHeight / 8) *3.69);
					$pdf->SetXY($x1, $y1);
					$pdf->Cell($cardWidth, 10, $advText2, $border, 1, "C", false);  // award name line 2
					$x1 = $origin_x;
					$y1 = $origin_y + (($cardHeight / 8) *4.27);
					$pdf->SetXY($x1, $y1);
					$pdf->Cell($cardWidth, 10, $advText3, $border, 1, "C", false);  // award name line 3	
					$lineBreakDone = true;
				} elseif (array_key_exists('lineBreakText2', $lineBreak) == FALSE)  {
					$x1 = $origin_x;
					$y1 = $origin_y + (($cardHeight / 8) *3.5);
					$pdf->SetXY($x1, $y1);
					$pdf->Cell($cardWidth, 10, $advText1, $border, 1, "C", false);  // award name line 1
					$x1 = $origin_x;
					$y1 = $origin_y + (($cardHeight / 8) *4.25);
					$pdf->SetXY($x1, $y1);
					$pdf->Cell($cardWidth, 10, $advText2, $border, 1, "C", false);  // award name line 2
					$lineBreakDone = true;
				}
			}
		}
		if ($lineBreakDone == false) {
			$x1 = $origin_x;
			$y1 = $origin_y + (($cardHeight / 8) *4);
			$pdf->SetXY($x1, $y1);
			$pdf->Cell($cardWidth, 10, $advText, $border, 1, "C", false);  // Rank			
		}
	}

	function hs_ScoutBookGetScoutName ( $inScoutArray, $l_SelectedScout) {
		$returnScoutName = "Unknown";
		foreach ($inScoutArray as $singleScout) {
			if ($l_SelectedScout == $singleScout[HS_ARRAY_Scout_MemberNum]) {
				//error_log(print_r("<br> scout Name:".$singleScout[HS_ARRAY_Scout_MemberName]."<br>"));
				$returnScoutName = $singleScout[HS_ARRAY_Scout_MemberName];
			}
		}
		return $returnScoutName;
	}
	
	// end advancement poster
	// Construct routine for class
	public function __Construct() {
		global	$cookieValues;
		
		global	$fontSize;
		global	$numRow;
		global	$labelRow;
		global	$colNum;
		global	$pdf;	

		// Constants
		include_once ( "include/hs_ScoutBookLabelPrint_constants.php");
		
		// Register Database
		// There are no database tables used by this plugin
		
		// Add Javascript and CSS for admin screens
		//add_action('admin_enquene_scripts', array($this, 'hs_load_custom_wp_admin_style'));
		add_action('admin_init', array($this, 'hs_sblp_load_js_css'));

        // Add Javascript and CSS for front-end display
		add_action('wp_enqueue_scripts', array($this, 'hs_sblp_load_js_css'));

		add_action( 'admin_post_hs_ScoutBookLabelPrint_Result', array($this, 'hs_ScoutBookLabelPrint_Result') );
		add_action( 'admin_post_nopriv_hs_ScoutBookLabelPrint_Result', array($this, 'hs_ScoutBookLabelPrint_Result') );
		add_action( 'admin_post_hs_ScoutBookLabelPrint_AdvancementResult', array($this, 'hs_ScoutBookLabelPrint_AdvancementResult') );
		add_action( 'admin_post_nppriv_hs_ScoutBookLabelPrint_AdvancementResult', array($this, 'hs_ScoutBookLabelPrint_AdvancementResult') );
		add_action( 'admin_post_hs_ScoutBookLabelPrint_GraphicResult', array($this, 'hs_ScoutBookLabelPrint_GraphicResult') );

		// add settings link to plugin
//		$plugin = plugin_basename(__FILE__); 
//		add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'hs_ScoutBookLabelPrint_settings_link'));
		
		// add settings link to plugin
		add_filter( 'plugin_row_meta', array($this, 'hs_ScoutBookLabelPrint_settings_description'), 10, 2 );


		//add_action( 'init', array($this, 'hs_ScoutBookLabel_getCookie') );
		add_action( 'init',      array($this, 'hs_ScoutBookLabel_getCookie') );
//		add_action( 'wp_logout', array($this, 'hs_ScoutBookLabel_EndSession'));
//		add_action( 'wp_login',  array($this, 'hs_ScoutBookLabel_EndSession'));
		add_action( 'admin_menu', array($this,'hs_ScoutBookLabelPrint_admin'));
				
		// Translation Link
		add_action ('plugins_loaded', array($this,'hs_ScoutBookLabelPrint_load_textDomain'));

		add_shortcode('hs_ScoutBookLabelPrint', 			array($this, 'hs_ScoutBookLabelPrint_code'));
		add_shortcode('hs_ScoutBookLabelPrint_samplePDF', 	array($this, 'hs_ScoutBookLabelPrint_SamplePDF'));
		add_shortcode('hs_ScoutBookLabelPrint_sampleCSV', 	array($this, 'hs_ScoutBookLabelPrint_SampleCSV'));
		add_shortcode('hs_ScoutBookLabelPrint_advancement', array($this, 'hs_ScoutBookLabelPrint_Advancement_code'));

		// Register Option Data
		$deprecated = null;
		$autoload = 'yes';	
		$option_value_showLabelBox = 0; // 0 = no lines,
//		$option_PresentionCard = HS_SETTING_SBLP_PRESENTIONCARD_INIT;
		$option_Badge_Images   = HS_SETTING_SBLP_MBRANKXML_INIT;

		if (get_option(HS_SETTING_SBLP_SHOWBOX, '0') !== false) {
			$option_value_showLabelBox = get_option(HS_SETTING_SBLP_SHOWBOX);
			//error_log ("get flag6: ".$option_value_showLabelBox.':');
		} else {
			$option_value_showLabelBox = 0;
			$deprecated = null;
			$autoload = 'no';
			add_option (HS_SETTING_SBLP_SHOWBOX, 			$option_value_showLabelBox, $deprecated, $autoload);
			//error_log ("Add flag7: ".$option_value_showLabelBox.':');
		}

		if (add_option (HS_SETTING_SBLP_MBRANKXML, 		$option_Badge_Images, 		$deprecated, $autoload) == false) {
			update_option (HS_SETTING_SBLP_MBRANKXML, 		$option_Badge_Images);
		}
	}
}

// Variable of plugin object
global	$hs_ScoutBookLabelPrint_obj;

// Create an instance of the class to kick off the whole thing
$hs_ScoutBookLabelPrint_obj = new hs_ScoutBookLabelPrint();
?>