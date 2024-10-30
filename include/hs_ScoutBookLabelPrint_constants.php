<?PHP
/*
    Description: Plugin for Wordpress to create TextGame
    Author: P.T.Howe
    Version: 1.5.1	
*/ 

// XML Group Contants
define ( 'HS_SBLP_ROOT', 		'root');
define ( 'HS_SBLP_COUNCILLIST', 'CouncilList');
define ( 'HS_SBLP_COUNCIL', 	'Council');
define ( 'HS_SBLP_STATE',	  	'State');
define ( 'HS_SBLP_CITY', 		'City');
define ( 'HS_SBLP_NAME',   		'Name');
define ( 'HS_SBLP_NUMBER',		'Number');

define ( 'HS_SBLP_GENERAL',		'ScoutBook');
define ( 'HS_SBLP_FONTS',		'Fonts');
define ( 'HS_SBLP_FONT',		'Font');
define ( 'HS_SBLP_UNITS',		'Units');
define ( 'HS_SBLP_UNIT',		'Unit');
define ( 'HS_SBLP_OUTPUTTYPES',	'OutputTypes');
define ( 'HS_SBLP_OUTPUTTYPE',	'OutputType');
define ( 'HS_SBLP_OUTPUTTYPEID','ID');
define ( 'HS_SBLP_AWARDS_BOY',	'BoyScoutAwards');
define ( 'HS_SBLP_AWARDS_BOY_ADV', 'Advancement');
define ( 'HS_SBLP_RANKMSG',		'RankMessage');
define ( 'HS_SBLP_MBMSG',		'MeritBadgeMessage');
define ( 'HS_SBLP_ABBRLIST',	'MeritBadgeAbbrList');
define ( 'HS_SBLP_MERITBADGE',	'MeritBadge');
define ( 'HS_SBLP_ABBR',		'Abbr');
define ( 'HS_SBLP_MULTILINE',	'MultiLine');
define ( 'HS_SBLP_ADVANCEMENTTYP', 'AdvancementType');
define ( 'HS_SBLP_ADVANCEMENTTYPID', 'ID');
//define { 'HS_SBLP_ADVTYPE',		'AdvType');

define ( 'HS_SBLP_UNIT_BOY',	'Scout');
define ( 'HS_SBLP_UNIT_CUB',	'Cub');
define ( 'HS_SBLP_UNIT_Venturing', 'Ven');
define ( 'HS_SBLP_UNIT_SeaScout', 'Sea');

// Boy Scout Constants
define ( 'HS_BSA_Cub_MinAge',	 '5 years');
define ( 'HS_BSA_Scout_MinAge', '10 years');

// Cookie Constants
define ( 'HS_COOKIE_SBLP_LabelStyle',	'HS_ScoutBook_LabelStyle');
define ( 'HS_COOKIE_SBLP_UnitType',		'HS_ScoutBook_UnitType');
define ( 'HS_COOKIE_SBLP_UnitNumber',	'HS_ScoutBook_UnitNum');
define ( 'HS_COOKIE_SBLP_FontSize',		'HS_ScoutBook_FontSize');
define ( 'HS_COOKIE_SBLP_LabelPosition','HS_ScoutBook_LabelPos');
define ( 'HS_COOKIE_SBLP_Council',		'HS_ScoutBook_Council');
define ( 'HS_COOKIE_SBLP_RankMsg',		'HS_ScoutBook_RankMsg');
define ( 'HS_COOKIE_SBLP_MBMsg',		'HS_ScoutBook_MBMsg');
define ( 'HS_COOKIE_SBLP_OutputType',	'HS_ScoutBook_OutputType');

define ( 'HS_COOKIE_SBLP_Default_UnitType',	'Troop');

define ( 'HS_COOKIE_SBLP_AllScoutsChecked', 'HS_ScoutBook_AllScoutsChecked');
define ( 'HS_COOKIE_SBLP_DateFilterChecked', 'HS_ScoutBook_DateChecked');
define ( 'HS_COOKIE_SBLP_SortFilterChecked', 'HS_ScoutBook_SortChecked');
define ( 'HS_COOKIE_SBLP_SelectScout',	'HS_ScoutBook_SelectScout');
define ( 'HS_COOKIE_SBLP_StartDate',	'HS_ScoutBook_StartDate');
define ( 'HS_COOKIE_SBLP_EndDate',		'HS_ScoutBook_EndDate');
define ( 'HS_COOKIE_SBLP_CardOrder',	'HS_ScoutBook_CardOrder');
define ( 'HS_COOKIE_SBLP_OutputSize',	'HS_ScoutBook_OutputSize');
define ( 'HS_COOKIE_SBLP_OutputFormat', 'HS_ScoutBook_OutputFormat');

define ( 'HS_COOKIE_SB_JSON',			'HS_ScoutBook_Cookie_JSON');

// Constants for Array index
define ( 'HS_ARRAY_Scout_MemberNum', 	'MemberNum');
define ( 'HS_ARRAY_Scout_MemberName', 	'MemberName');
define ( 'HS_ARRAY_Scout_DOB',			'DOB');
define ( 'HS_ARRAY_Scout_UnitNum',		'unitNum');
define ( 'HS_ARRAY_Scout_UnitType',		'unitType');
define ( 'HS_ARRAY_Scout_DateJoined',	'dateJoinedBSA');
define ( 'HS_ARRAY_Scout_DenType',		'denType');
define ( 'HS_ARRAY_Scout_DenNum',		'denNum');
define ( 'HS_ARRAY_Scout_PatrolDate', 	'patrolDate');
define ( 'HS_ARRAY_Scout_Advancement',	'AdvancementAbbr');
define ( 'HS_ARRAY_Scout_CardType',		'CardType');

define ( 'HS_ARRAY_Graphic_Category',	'Category');
define ( 'HS_ARRAY_Graphic_SKU', 		'SKU');
define ( 'HS_ARRAY_Graphic_Type',		'Type');
define ( 'HS_ARRAY_Graphic_Name',		'Name');
define ( 'HS_ARRAY_Graphic_Img', 		'IMG');
define ( 'HS_ARRAY_Graphic_Rank',		'Rank');
define ( 'HS_ARRAY_Graphic_Width',		'Width');
define ( 'HS_ARRAY_Graphic_Height',		'Height');

define ( 'HS_ARRAY_ADV_MemberNum',		'MemberNum');
define ( 'HS_ARRAY_ADV_ScoutName',		'ScoutName');
define ( 'HS_ARRAY_ADV_Type',			'Type');
define ( 'HS_ARRAY_ADV_Advancement',	'Advancement');
define ( 'HS_ARRAY_ADV_Council',		'Council');
define ( 'HS_ARRAY_ADV_UnitType',		'UnitType');
define ( 'HS_ARRAY_ADV_UnitNum',		'UnitNum');
define ( 'HS_ARRAY_ADV_Date',			'Date');
define ( 'HS_ARRAY_ADV_Category',		'Category');
define ( 'HS_ARRAY_ADV_StandardDate',	'StandardDate');

// Constants for XML Graphics file
define ( 'HS_XML_GRAPHICS_Category',	'Category');
define ( 'HS_XML_GRAPHICS_SKU',			'SKU');
define ( 'HS_XML_GRAPHICS_ItemType',	'ItemType');
define ( 'HS_XML_GRAPHICS_ItemName',	'ItemName');
define ( 'HS_XML_GRAPHICS_IMG',			'IMG');
define ( 'HS_XML_GRAPHICS_ItemRank',	'ItemRank');
define ( 'HS_XML_GRAPHICS_Width',		'Width');
define ( 'HS_XML_GRAPHICS_Height',		'Height');

define ( 'HS_GRAPHICS_IMG_FULLPATH',	'FullPath');

// Constants from CSV file from ScoutBook
define ( 'HS_CSV_SCOUT_COLUMN', 		'36');	//
define ( 'HS_CSV_SCOUT_COLUMN_2021_03',	'34');	// export file changed on March 2021
define ( 'HS_CSV_ADVANCMENT_COLUMN',	'16');	//

define ( 'HS_CSV_MERIT_BADGE', 'Merit Badge');
define ( 'HS_CSV_MERIT_BADGES', 'Merit Badges');
define ( 'HS_CSV_BADGEFOFRANK', 'Badges of Rank');
define ( 'HS_CSV_RANK', 'Rank');
define ( 'HS_CSV_MISC_AWARDS',	'Misc Awards');

define ( 'HS_CSV_RANK_CS_LOOP', 'Academics & Sports Belt Loop');
define ( 'HS_CSV_RANK_BS_RANK', 'Rank');
define ( 'HS_CSV_RANK_BS_AWARD', 'Award');
define ( 'HS_CSV_RANK_CS_ADVENTURE', 'Adventure');
define ( 'HS_CSV_RANK_CS_WEBELOS', 'Webelos Activity Badge');

define ( 'HS_CSV_RANK_NAME_LION', 'Lion');
define ( 'HS_CSV_RANK_NAME_BOBCAT', 'Bobcat');
define ( 'HS_CSV_RANK_NAME_TIGER', 'Tiger');
define ( 'HS_CSV_RANK_NAME_WOLF', 'Wolf');
define ( 'HS_CSV_RANK_NAME_BEAR', 'Bear');
define ( 'HS_CSV_RANK_NAME_WEBELOS', 'Webelos');
define ( 'HS_CSV_RANK_NAME_ARROW', 'Arrow of Light');

define ('HS_CSV_RANK_NAME_SCOUT', 'Scout');
define ('HS_CSV_RANK_NAME_TENDERFOOT', 'Tenderfoot');
define ('HS_CSV_RANK_NAME_SECOND', 'Second Class');
define ('HS_CSV_RANK_NAME_FIRST', 'First Class');
define ('HS_CSV_RANK_NAME_STAR', 'Star Scout');
define ('HS_CSV_RANK_NAME_LIFE', 'Life Scout');
define ('HS_CSV_RANK_NAME_EAGLE', 'Eagle Scout');

define ('HS_CSV_RANK_NAME_SCOUT_REQ', 'Scout Rank Requirement');
define ('HS_CSV_RANK_NAME_TENDERFOOT_REQ', 'Tenderfoot Rank Requirement');
define ('HS_CSV_RANK_NAME_SECOND_REQ', 'Second Class Rank Requirement');
define ('HS_CSV_RANK_NAME_FIRST_REQ', 'First Class Rank Requirement');
define ('HS_CSV_RANK_NAME_STAR_REQ', 'Star Scout Rank Requirement');
define ('HS_CSV_RANK_NAME_LIFE_REQ', 'Life Scout Rank Requirement');
define ('HS_CSV_RANK_NAME_EAGLE_REQ', 'Eagle Scout Rank Requirement');
define ('HS_CSV_MERIT_BADGE_REQ', 'Merit Badge Requirement');
define ('HS_CSV_ADVANCEMENT_REQ', ' Requirement');


// Constants for Advancement PO
define ('HS_CSV_PO_FirstName', 	'First Name');	// A
define ('HS_CSV_PO_LastName', 	'Last Name');	// B
define ('HS_CSV_PO_Patrol', 	'Patrol');		// C
define ('HS_CSV_PO_Quantity', 	'Quantity');	// D
define ('HS_CSV_PO_SKU', 		'SKU');			// E
define ('HS_CSV_PO_ItemType', 	'Item Type');	// F
define ('HS_CSV_PO_Price', 		'Price');		// G
define ('HS_CSV_PO_ItemName', 	'Item Name');	// H
define ('HS_CSV_PO_DateEarned', 'Date Earned');	// I

define ('HS_ARRAY_FirstName', 	'FirstName');
define ('HS_ARRAY_LastName', 	'LastName');
define ('HS_ARRAY_Patrol', 		'Patrol');
define ('HS_ARRAY_Quantity', 	'Quantity');
define ('HS_ARRAY_SKU', 		'SKU');
define ('HS_ARRAY_ItemType', 	'ItemType');
define ('HS_ARRAY_Price', 		'Price');
define ('HS_ARRAY_ItemName', 	'ItemName');
define ('HS_ARRAY_ItemName_ABBR', 'ItemNameAbbr');
define ('HS_ARRAY_DateEarned', 	'DateEarned');
define ('HS_ARRAY_DateFormated', 'DateFormated');
define ('HS_ARRAY_ScoutName',	'ScoutName');
define ('HS_ARRAY_LabelLines',	'LabelLines');
define ('HS_ARRAY_CardLines',	'CardLines');

// Contants for array counting label type to calcalute sheet count
define ('HS_LABEL_COUNT_MERITBADGE','MeritBadge');
define ('HS_LABEL_COUNT_SCOUT', 	'Scout');
define ('HS_LABEL_COUNT_TENDERFOOT','Tenderfoot');
define ('HS_LABEL_COUNT_SECONDCLASS','Second');
define ('HS_LABEL_COUNT_FIRSTCLASS','First');
define ('HS_LABEL_COUNT_STAR', 		'Star');
define ('HS_LABEL_COUNT_LIFE',		'Life');
define ('HS_LABEL_COUNT_EAGLE',		'Eagle');
define ('HS_LABEL_COUNT_OTHER', 	'Other');

// Contents for replacement in message
define ( 'HS_MSG_RANK', '%rank%');
define ( 'HS_MSG_MERITBADGE', '%mb_name%');
define ( 'HS_MSG_NL', '%n%');
define ( 'HS_MSG_NEWLINE', " \n ");
define ( 'HS_MSG_EMBLEM', ' Emblem');
define ( 'HS_MSG_MB_EMBLEM', 'MB Emblem');

define ( 'HS_PDF_SETTING_FONT', 'Helvetica'); // Arial
define ( 'HS_PDF_SETTING_BOLD', 'B');
define ( 'HS_PDF_SETTING_MARGIN', '36'); // in Pt's
define ( 'HS_PDF_SETTING_MARGIN_LEFT', '31.2'); // in Pt's
define ( 'HS_PDF_SETTING_MARGIN_TOP', '34'); // in Pt's
define ( 'HS_PDF_SETTING_MARGIN_RIGHT', '36'); // in Pt's

define ( 'HS_PDF_SETTING_BSASTOCK_MARGIN', '36'); // in Pt's
define ( 'HS_PDF_SETTING_BSASTOCK_MARGIN_LEFT','15'); // in Pt's
define ( 'HS_PDF_SETTING_BSASTOCK_MARGIN_TOP', '15'); // in Pt's
define ( 'HS_PDF_SETTING_BSASTOCK_MARGIN_RIGHT', '36'); // in Pt's


//define ( 'HS_PDF_SETTING_PAPER_LETTER', 'Letter');
define ( 'HS_PDF_SETTING_ORIENTATION_PORTRAIT', 'P'); 
define ( 'HS_PDF_SETTING_ORIENTATION_LANDSCAPE','L');
define ( 'HS_PDF_ARCH_E_PORTRATE_COLS',	13);	// TCPDF_PDF_ARCH_E
define ( 'HS_PDF_ARCH_E_PORTRATE_ROWS', 13);	// TCPDF_PDF_ARCH_E
define ( 'HS_PDF_POSTER_PORTRATE_COLS',	9);	// TCPDF_PDF_POSTER_LARGE
define ( 'HS_PDF_POSTER_PORTRATE_ROWS', 9);	// TCPDF_PDF_POSTER_LARGE

define ( 'HS_PDF_ARCH_D_LAND_COLS',	13);	// TCPDF_PDF_POSTER_LARGE
define ( 'HS_PDF_ARCH_D_LAND_ROWS', 6);	// TCPDF_PDF_POSTER_LARGE


define ( 'HS_PDF_ARCH_C_PORTRATE_COLS',	6);	// TCPDF_PDF_ARCH_E
define ( 'HS_PDF_ARCH_C_PORTRATE_ROWS', 6);	// TCPDF_PDF_ARCH_E

define ( 'HS_PDF_LETTER_LANDSCAPE_COLS',4);	// TCPDF_PDF_LETTER
define ( 'HS_PDF_LETTER_LANDSCAPE_ROWS',2);	// TCPDF_PDF_LETTER

define ( 'HS_PDF_SORT_FileOrder', 'File Order');
define ( 'HS_PDF_SORT_DateOrderAsc', 'Date Order Asc');
define ( 'HS_PDF_SORT_DateOrderDesc', 'Date Order Desc');
// Define Constant for Output Type options
define ( 'HS_XML_LABELLIST', '1');
define ( 'HS_XML_LABELONLY', '2');
define ( 'HS_XML_LISTONLY',  '3');
//define ( 'HS_XML_CARDLIST',  '4');
//define ( 'HS_XML_CARDONLY',	 '5');
//define ( 'HS_XML_MERITBADGE_ONLY',	'6');
//define ( 'HS_XML_MERITBADGE_LIST',	'7');

// Card Constants
define ( 'HS_CARD_MERITBADGE_CORNER_COLOR', 'array(207, 181, 59)');
define ( 'HS_CARD_CUB_SCOUT_BLUE_COLOR', 'array(0, 63, 135)');

// Card Form 654936 - Field name constants
define ( 'HS_LABELSTYLE_FORM_ScoutName', 	'ScoutName');
define ( 'HS_LABELSTYLE_FORM_Advancement','Advancement');
define ( 'HS_LABELSTYLE_FORM_Unit', 		'Unit');
define ( 'HS_LABELSTYLE_FORM_Date', 		'Date');
define ( 'HS_LABELSTYLE_FORM_Council', 	'Council');

// Label Styles
define ( 'HS_LABELSTYLE_AVERY_6570', '6570');
define ( 'HS_LABELSTYLE_CARD', 'CARD');
define ( 'HS_LABELSTYLE_CARD_CREATED',	'CreatedCard');
define ( 'HS_LABELSTYLE_FILLER', 'Filler');
define ( 'HS_LABELSTYLE_MeritBadge_SKU_654936', 'PDF_Form_654936');
define ( 'HS_LABELSTYLE_MeritBadge_SKU_33414',	'PDF_Form_33414');

// Contents for Admin Options
define ( 'HS_SETTING_SBLP_SHOWBOX', 		'HoweScape_SBLP_ShowBox');
define ( 'HS_SETTING_SBLP_PRESENTIONCARD', 	'HoweScape_SBLP_PresentationCard');
define ( 'HS_SETTING_SBLP_MBRANKXML',		'HoweScape_SBLP_MBRank_XML');
//define ( 'HS_SETTING_SBLP_MBDIRPATH', 	'HoweScape_SBLP_MBDIRPATH');
//define ( 'HS_SETTING_SBLP_RANKXML', 	'HoweScale_SBLP_RANKXML');
//define ( 'HS_SETTING_SBLP_CARDXML',		'HoweScale_SBLP_CARDXML');

// Admin initial values
define ( 'HS_SETTING_SBLP_PRESENTIONCARD_INIT', '/images/WhiteCard.png');
define ( 'HS_SETTING_SBLP_MBRANKXML_INIT', 'images//ScoutBook_MeritBadgeImages.xml');
//define ( 'HS_SETTING_SBLP_MBDIRPATH_INIT', 'https://filestore.scouting.org/filestore/boyscouts/jpg/'.HS_MSG_MERITBADGE.'_sm.jpg');
//define ( 'HS_SETTING_SBLP_RANKXML_INIT', '');
//define ( 'HS_SETTING_SBLP_CARDXML_INIT', '');
define ( 'HS_PROTOCAL', 'http');

//TCPDF Constants
define ( 'TCPDF_PDF_UNIT_PT',	'pt');
define ( 'TCPDF_PDF_UNIT_MM',	'mm');
define ( 'TCPDF_PDF_UNIT_CM',	'cm');
define ( 'TCPDF_PDF_UNIT_IN',	'in');

define ( 'TCPDF_PDF_A3',			'A3');
define ( 'TCPDF_PDF_A4',			'A4');  // 8.5" x 11"
define ( 'TCPDF_PDF_A5',			'A5');
define ( 'TCPDF_PDF_LETTER',		'LETTER');		// 8.5 x 11
define ( 'TCPDF_PDF_Legal',			'LEGAL');		// 8.5 x 14
define ( 'TCPDF_PDF_EN_COPY_DRAUGHT','EN_COPY_DRAUGHT'); 	// 16.0 x 20.0
define ( 'TCPDF_PDF_ARCH_C',		'ARCH_C'); 		// 18.0 x 24.0
define ( 'TCPDF_PDF_ARCH_C_DISPLAY','ARCH_C (18.0 x 24.0)');
define ( 'TCPDF_PDF_POSTER_LARGE',	'ARCH_D'); 		// 24.0 x 36.0
define ( 'TCPDF_PDF_POSTER_DISPLAY', 'ARCH_D (24.0 x 36.0)');
define ( 'TCPDF_PDF_ARCH_D_LAND',			'ARCH_D_Landscape'); 		// 24.0 x 36.0
define ( 'TCPDF_PDF_ARCH_D_LAND_DISPLAY', 	'ARCH_D Landscape (36.0 x 24.0)');
define ( 'TCPDF_PDF_ARCH_E',		'ARCH_E');
define ( 'TCPDF_PDF_ARCH_E_DISPLAY','ARCH_E (36.00 x 48.00)');
define ( 'TCPDF_PDF_BORDER_DRAW',	1);
define ( 'TCPDF_PDF_BORDER_NODRAW', 0);
//
define ( 'TCPDF_REPORT_TYPE_POSTER', 'PrintPoster');
define ( 'TCPDF_REPORT_TYPE_REPORT', 'PrintReport');
define ( 'TCPDF_REPORT_TYPE_POSTER_DESP', 'Printer Poster');
define ( 'TCPDF_REPORT_TYPE_REPORT_DESP', 'Printed Report');

// Constants for extended class
define ( 'Extension_PageType_PlainPaper',	'PlainPaper');
define ( 'Extension_PageType_BSAStock',		'BSAStock');

// Define Cookie JSON object default for starting point
define ( 'HS_COOKIE_SBLP_INITIAL', '{"SB_LABEL_PRINT":{"LabelStyle":"Avery 6570", 
														"UnitType":"Troop",
														"UnitNumber":"0001",
														"FontSize":"9",
														"LabelPosition":"1",
														"Council":"Chester County Council",
														"RankMessage":"Has met the Reqs for the %n% %rank%",
														"MeritBadgeMessage":"Has met the Reqs for the %n% %mb_name%",
														"OutputContent":"Labels and List",
														"Version":"1.0"},
									"SB_ADVANCEMENT_CARD":{"Council":"Chester County Council",
															"UnitType":"Cub,Scout",
															"DateFilter":"false",
															"StartDate":"2021-11-01",
															"EndDate":"2021-11-06",
															"SortFilter":"true",
															"CardOrder":"Date Order Asc",
															"TitleFilter":"false",
															"TitleSample":"Advancement Cards",
															"SelectedScout":"126634648",
															"OutputFormat":"PrintPoster",
															"OutputSize": "ARCH_E" }}');

// Define constants 
//define ('HS_AWARD_TITLE_LINE_BREAK', 'LineBreakInfo');	
							
// Define Line Break for awards
define ( 'HS_AWARD_TITLE_LINE_BREAK', '{{"lineBreakText" : " No Trace Awareness ", "lineBreakIndex" => 1, "lineBreakText2" : "Award (Retired", "lineBreakIndex2" => 1},
										{"lineBreakText" : " Conservation ", "lineBreakIndex" => 1, "lineBreakText2" : " v2015 (Wolf)", "lineBreakIndex2" => 1},
										{"lineBreakText" : " Conservation ", "lineBreakIndex" => 1, "lineBreakText2" : " v2015 (Bear)", "lineBreakIndex2" => 1},
										{"lineBreakText" : "ss BSA v2014 (Tiger) (Retired 12/31/2020)", "lineBreakIndex" => 3, "lineBreakText2" : " (Retired 12/31/2020)", "lineBreakIndex2" => 1},
										{"lineBreakText" : "ss BSA v2014 (Wolf) (Retired 12/31/2020)", "lineBreakIndex" => 3, "lineBreakText2" : " (Retired 12/31/2020)", "lineBreakIndex2" => 1},
										{"lineBreakText" : " - ", "lineBreakIndex" : 3},
										{"lineBreakText" : "(Grade", "lineBreakIndex" : 0},
										{"lineBreakText" : " (LDS ", "lineBreakIndex" : 0},
										{"lineBreakText" : " Award (", "lineBreakIndex" : 1},
										{"lineBreakText" : " Activity ", "lineBreakIndex" : 1},
										{"lineBreakText" : " Award pin", "lineBreakIndex" : 1},
										{"lineBreakText" : " BSA v2014 ", "lineBreakIndex" : 1},
										{"lineBreakText" : " / ", "lineBreakIndex" : 1},
										{"lineBreakText" : "(Duty to Country)", "lineBreakIndex" : 0} }');
?>