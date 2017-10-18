<?php 
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

require_once('../cascade_ws_ns/auth.php');
include('Parsedown.php');


//use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;


if ( isset( $argv ) && sizeof( $argv ) < 2 )
{
	echo PHP_EOL . 'Error - 2 Arguments required.' . PHP_EOL . PHP_EOL; 
	echo 'Folder Name in _content site' . PHP_EOL;
	echo 'Markdown File' . PHP_EOL;
	echo PHP_EOL;
	exit;
}

try
{
    $siteName   = '_content';
    $folderName = $argv[1];
    $folder = $cascade->getFolder( $folderName, $siteName );
    
    if ( is_null($folder) )
    {
        echo "Didn't find that folder. Going to create it" . PHP_EOL;
        $siteBaseFolder = $cascade->getAsset( a\Site::TYPE, $siteName )->getBaseFolder();
        echo $siteBaseFolder->getName();
        $basefolder = $cascade->createFolder( $siteBaseFolder, $folderName )->edit(); 
    } else {
        echo "Found folder : " . $folderName . PHP_EOL;
    }
    $baseFolder = $cascade->getAsset( a\Folder::TYPE, $folderName, $siteName );
    $folderParameter = file_get_contents($argv[2]);
    $Parsedown = new Parsedown();
    $html = $Parsedown->text($folderParameter);
    $counter = 0;
    $rows = preg_split('/<hr \/>/', $html, -1, preg_split_offset_capture | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    foreach($rows as $row)
    {  
        
    
        preg_match('/^<h[1-4]>(.*)<\/h[1-4]>$/m', $row, $title);
        $blockTitle = preg_replace('/[^\w]/', '', $title[1]);
        $data_definition =
            $cascade->getAsset( 
             a\DataDefinition::TYPE, 
             'widgets/widget', '__main' );
        $block_folder =
             $cascade->getAsset( a\Folder::TYPE, $baseFolder->getId(), $siteName );
        $cascade->createDataDefinitionBlock(
             $block_folder,
             $blockTitle,
             $data_definition )->
        setText('type', 'Generic')->
        setText( 'content', $row )->
         edit();
            echo 'Block Title: ' . $title[1] . PHP_EOL;
            echo 'Block Content:' . PHP_EOL;
            echo $row . PHP_EOL . PHP_EOL . PHP_EOL;
    }
    

//     if( is_null( $block ) )
//     {
//         // create a data definition block 
//         // to be attached to a region at the page level
//         $data_definition =
//             $cascade->getAsset( 
//                 a\DataDefinition::TYPE, 
//                 'Test Data Definition Container/Simple Text', $site_name );
//         $block_folder =
//             $cascade->getAsset( a\Folder::TYPE, 'blocks', $site_name );
//         $cascade->createDataDefinitionBlock(
//             $block_folder,
//             $block_name,
//             $data_definition )->
//         setText( 'text', 'Some content for the data block.' )->
//         edit();
//     }        
}
catch( \Exception $e ) 
{
    echo S_PRE . $e . E_PRE; 
}
?>