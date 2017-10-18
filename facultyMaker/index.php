<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once('../cascade_ws_ns/auth.php');
include('Parsedown.php');


use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;
// CURL
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://www.digitalmeasures.com/login/service/v4/SchemaData/INDIVIDUAL-ACTIVITIES-University/USERNAME:XXXXXXXXX/PCI,ADMIN,EDUCATION,ADMIN_ASSIGNMENTS",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "authorization: Basic XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    "cache-control: no-cache",
    "postman-token: XXXXXXXX-XXXX-XXX-XXXX-XXXXXXXXXXXX"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  error_log("cURL Error #:" . $err, 0);
} else {
  //echo $response;
}

// Cascade variables
$cascade_Site = "_people-TEST";
$metadata_set_name = 'Person';
$metadata_set_site = '__main';
$data_definition   = $cascade->getAsset( 
						a\DataDefinition::TYPE, 
                    	"/_people/person", 
                        "__main");
$block_folder      = $cascade->getFolder(
                        "/_blocks", 
                        $cascade_Site );
$ms                = $cascade->getAsset( 
                        a\MetadataSet::TYPE, 
                        "179876760a02005e338d85350bcefc67" );
$folder_metadata  = $cascade->getAsset(   // Folder Metadata
						a\MetadataSet::TYPE, 
						"3ad0b1100a02005e338d8535d5cf4703" ); 
$ct = $cascade->getAsset(           // Content Type
						a\ContentType::TYPE,
                    	"/page", 
                        "__main");

$data = simplexml_load_string($response);
error_log('Total Records: '. count($data->Record), 0);


for ( $j = 0; $j < count($data->Record); $j++ ) {

    // Initialization
	$PREFIX = '';
	$FNAME = '';
	$PFNAME = '';
	$MNAME = '';
	$LNAME = '';
	$SUFFIX = '';
	$RANK = '';
	$BUILDING = '';
	$ROOMNUM = '';
	$OPHONE1 = '';
	$OPHONE2 = '';
	$OPHONE3 = '';
	$EMAIL = '';
	$WEBSITE = '';
	$BIO = '';
	$RANK = '';
	$TEACHING_INTERESTS = '';
	$RESEARCH_INTERESTS = '';
	$EDUCATION = '';
	$DEPT = '';
	$department = '';
	$DEG = '';
	$DEGOTHER = '';
    $EDUCATION = array();
    
    // PCI data
    foreach($data->Record[$j]->PCI[0] as $key=>$value) { $$key = str_replace("&", "and", $value); }
    // ADMIN data
    foreach($data->Record[$j]->ADMIN[0] as $key=>$value) { $$key = str_replace("&", "and", $value); }
    // Education data
    for ($i=0; $i <= count($data->Record[$j]->EDUCATION); $i++ ) { $EDUCATION[] = $data->Record[$j]->EDUCATION[$i]; }
	// Set department
    $DEPT = (string) $data->Record[$j]->ADMIN[0]->ADMIN_DEP[0]->DEP;
    // if department isn't set, get the next record's department data
    if ( $data->Record[$j]->ADMIN[0]->ADMIN_DEP[0]->DEP == '' ) { $DEPT = $data->Record[$j]->ADMIN[1]->ADMIN_DEP[0]->DEP; }
    
    // Exception handling / normalization…
    if ( $DEPT == '' ) { $DEPT = '_Unsorted'; }
    if ( $RANK == 'Full Professor' ) { $RANK = 'Professor'; }
    if ( $DEPT == 'Modern and Classical Language and Literatures' ) { $DEPT = 'Modern and Classical Languages and Literatures'; }
    if ( $DEPT == 'Communications' OR $DEPT == 'Communication Studies' ) { $DEPT = 'Communication'; }
    if ( $DEPT == 'Physics, Computer Science and Engineering' ) { $DEPT = 'Physics Computer Science and Engineering'; }
    if ( $DEPT == 'Sociology, Social Work and Anthropology' ) { $DEPT = 'Sociology Social Work and Anthropology'; }
    if ( !empty($PFNAME)) { $FNAME = $PFNAME; } 
    
    $number_degrees    = count($data->Record[0]->EDUCATION);
    $banner_id         = $data->Record[$j]->attributes()->username;
    $hash              = md5($banner_id);
    $photo_name        = '_people/' . $hash . '/_image';
    $photo_file = $cascade->getAsset(a\DataBlock::TYPE, $photo_name, "_images");
// echo $hash;
    $LNAME_folder      = str_replace("'",' ', $LNAME); // No apostrophes!
    $last_first        = $LNAME_folder . ', ' . $FNAME; // {Last}, {First}
	$department        = (string) $DEPT;
	$first_initial     = $FNAME[0];

    if (is_null($block_folder)){
        error_log("Need to create a folder", 0);
        $block_folder = $cascade->createFolder(
                            $cascade->getFolder(
                            '/', 
                            $cascade_Site ),
                            '_blocks' )->
                        edit();      
    }
    $blockAddress = $folder_name . '/' . $hash;
    $block = $cascade->getDataDefinitionBlock($blockAddress, $cascade_Site);
    error_log('Record: ' . $j . ' - ' . $last_first . ' - Block location: ' . $blockAddress,0); 
    if (is_null($block))
    {
        error_log("Creating Block", 0);
        $block = $cascade->createDataDefinitionBlock( 
            $block_folder,
            $hash,
            $data_definition )->edit();
		blockContent();
		error_log('Adding Degrees', 0);
		education();
		error_log('Setting Metadata', 0);
    	metadata();
        error_log('Complete - ' . $last_first, 0);
    	$newfolder = folder(); 
        error_log('Created - ' . $folder_name, 0);
    	index($newfolder);
        error_log('Built - index', 0);
    } else {
        error_log("Updating Block", 0);
        blockContent();
		error_log('Adding Degrees');
		education();
    	error_log('Setting Metadata');
    	metadata();
        error_log('Complete - ' . $last_first, 0);
    	$newfolder = folder(); 
        error_log('Created - ' . $folder_name, 0);
    	index($newfolder);
        error_log('Built - index', 0);
    }
}

function metadata() {
	Global $block, $ms, $department;
	$block->setMetadataSet( $ms );
	$block->getMetadata()->
			setDynamicField('role', 'faculty' )->
			setDynamicField('department', $department);
			// add in set display name metadata with last, first for reference point
	$block->edit();
}

function blockContent() {
	Global $block, $PREFIX, $FNAME, $MNAME, $LNAME, $RANK, $BUILDING, $ROOMNUM, $OPHONE1, $OPHONE2, $OPHONE3, $EMAIL, $WEBSITE, $BIO, $TEACHING_INTERESTS, $RESEARCH_INTERESTS, $photo_name, $photo_file;
	$Parsedown = new Parsedown();
	$block->
	setText('role', 'Faculty')->
	setText('salutation', $PREFIX)->
	setText('first', $FNAME)->
	setText('middle', $MNAME)->
	setText('last', $LNAME)->
	setText('title', $RANK)->
	setText('building', $BUILDING)->
	setText('room', $ROOMNUM)->
	setText('phone', '(' . $OPHONE1 . ') ' . $OPHONE2 . '-' . $OPHONE3 )->
	setText('email', $EMAIL)->
	setText('url', $WEBSITE)->
	setBlock('image', $photo_file)->
	setText('faculty;bio', $Parsedown->text($BIO))->
	setText('faculty;rank', $RANK)->
	setText('faculty;teachingareas', nl2br($TEACHING_INTERESTS))->
	setText('faculty;researchareas;researcharea;0', nl2br($RESEARCH_INTERESTS))->
	edit();

}

function education() {
	Global $block, $number_degrees, $EDUCATION;
	while ( $block->getNumberOfSiblings('faculty;degrees;degree;0') < $number_degrees) {
		$block->appendSibling( 'faculty;degrees;degree;0' );
	}
	for ($i=0; $i < (count($EDUCATION)-1); $i++ ) {		
		error_log('Educational Record: ' . $i . ' ' . $EDUCATION[$i]->DEG . ' from ' . $EDUCATION[$i]->SCHOOL . ' in ' . $EDUCATION[$i]->MAJOR, 0);
		$degree = str_replace('&', 'and', $EDUCATION[$i]->DEG);
		$school = str_replace('&', 'and', $EDUCATION[$i]->SCHOOL);
		$field = str_replace('&', 'and', $EDUCATION[$i]->MAJOR);
		$block->
			setText('faculty;degrees;degree;'.$i.';institution', $school)->
			setText('faculty;degrees;degree;'.$i.';type', $degree)->
			setText('faculty;degrees;degree;'.$i.';field', $field)->
			edit();
	}
}

function folder() {
Global $cascade, $folder_metadata, $cascade_Site, $EMAIL;
	$no_domain = str_replace("@cnu.edu", "", $EMAIL);
	$folder_name = str_replace(".", "", $no_domain);
    $folder = $cascade->getFolder( $folder_name, $cascade_Site );
    $base_folder = $cascade->getAsset( a\Site::TYPE, $cascade_Site )->getBaseFolder();
// 	$page = $cascade->getPage( "index", $cascade_Site );
	$folder = $cascade->
		createFolder(
			$base_folder, 
			$folder_name )->
		setMetadataSet( $folder_metadata )->
		setShouldBeIndexed( true )->   // all indexable
		setShouldBePublished( true )-> // all publishable
		edit(); // commit!!!
		return $folder;
	}

function index($folder) {
Global $cascade, $folder_name, $block, $ct, $first_last, $cascade_Site;
	$person_folder = $cascade->getAsset( a\Folder::TYPE, $folder->getId() );
            $person_page = $cascade->getPage( $person_folder->getPath() . "/index", $cascade_Site );
                $person_page = $cascade->
                    createDataDefinitionPage(
                        $person_folder, // folder
                        'index',        // system page name
                        $ct             // content type
                    )->
                    setText('type', 'Tertiary')-> // set `page type` data definition field
                    setBlock("main;content;0", $block)-> // set the main/content block chooser
                edit();
                $person_page_id = $person_page->getId();
                $person_page->
                    getMetadata()->
                    setDisplayName($first_last)-> // set Display Name field
                    setTitle($first_last)->       // set Title field
                    setDynamicFieldValue('breadcrumb', $first_last);  //set `breadcrumb name` dynamic metadata field
                $person_page->edit();
                }