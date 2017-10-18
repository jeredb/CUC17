<?php 
require_once('../cascade_ws_ns/auth.php');
include('Parsedown.php');


use cascade_ws_AOHS      as aohs;
use cascade_ws_constants as c;
use cascade_ws_asset     as a;
use cascade_ws_property  as p;
use cascade_ws_utility   as u;
use cascade_ws_exception as e;

if ( isset( $argv ) && sizeof( $argv ) <= 1 )
{
    echo 'linkMaker' . PHP_EOL . '====================' . PHP_EOL;
    echo 'Options:' . PHP_EOL;
    echo ' -sample - Outputs sample CSV'. PHP_EOL;
    echo ' -run    - takes CSV and buils Learn More' . PHP_EOL;
    echo ' syntax:   "php index.php -run learn.csv [_content/Folder ID] [block name]"'. PHP_EOL;
	echo PHP_EOL;
	exit;
}



try
{
    if ( isset( $argv ) && $argv[1] == "-sample" )
    {
        $filename = 'learn-sample.csv';
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}");
        header("Expires: 0");
        header("Pragma: public");
        $output = fopen($filename, 'w');
        fputcsv($output, array('Link Type (I/E)', 'Internal - Site Name', 'Internal - Link Path', 'Internal - Type (P/F)', 'Internal - Anchor', 'External URL', 'Link text - Custom'));
        fclose($output);
        echo 'Done' . PHP_EOL . PHP_EOL;
    }
    
    if ( isset( $argv ) && $argv[1] == "-run" )
    {
        $siteName   = '_content';
        $CSVFile    = $argv[2];
        $folderName = $argv[3];
        $blockName  = $argv[4];
        
        // Making sure all the different elements of the command are there.
        
        if ($CSVFile == NULL)
        { echo PHP_EOL . 'No CSV file included in the command' . PHP_EOL . PHP_EOL; exit; }
        if ($folderName == NULL)
        { echo PHP_EOL . 'No folder in _content included in the command' . PHP_EOL . PHP_EOL; exit; }
        if ($blockName == NULL)
        { echo PHP_EOL . 'No block name included in the command' . PHP_EOL . PHP_EOL; exit; }
        
        $file = fopen($CSVFile, 'r');
        
        // Check to see if folder is there. If not, create it.
                
        $contentFolder = $cascade->getFolder( $folderName, $siteName );
        $contentFolder->getId();
        if ( is_null($contentFolder) )
        {
            echo "Didn't find that folder. Going to create it" . PHP_EOL;
            $siteBaseFolder = $cascade->getAsset( a\Site::TYPE, $siteName )->getBaseFolder();
            $baseFolder = $cascade->createFolder( $siteBaseFolder, $folderName )->edit(); 
            echo "Created Folder: " . $folderName . ' in ' . $siteName . '/' . $folderName . PHP_EOL;
        } else {
            echo "Found folder: " . $folderName . PHP_EOL;
        }
        
        // Set datadefinition for the Learn More Block
        $dataDefinition = $cascade->getAsset( 
            a\DataDefinition::TYPE, 
            'widgets/widget', 
            '__main' );
        // Set base folder for asset creation         
        $baseFolder = $cascade->getAsset( 
            a\Folder::TYPE, 
            $folderName, 
            $siteName );
        // Create learn more block and set 'type'
        $learnMoreBlock = $cascade->createDataDefinitionBlock(
            $baseFolder,
            $blockName,
            $dataDefinition )->
            setText(
                'type', 
                'Learn More')->
            setText(
                'name',
                'Learn More')->
            edit();
        // u\DebugUtility::dump( $learnMoreBlock->getIdentifiers() );
        $siblings = ($learnMoreBlock->getNumberofSiblings('link;0') - 1);
        
        
        while(! feof($file))
        {
            echo 'Siblings: ' . $siblings . PHP_EOL;
            $data = fgetcsv($file);
                        
            // Skip Header row
            if ($data[0] == 'Link Type (I/E)')
            {  }
            else
            {
                echo 'Link Type: ' . $data[0]. PHP_EOL;
                $internalSite   = $data[1];
                $internalPath   = $data[2];
                $internalType   = $data[3];
                $internalAnchor = $data[4];
                $externalURL    = $data[5];
                $linkText       = $data[6];

                if ($data[0] == 'I')
                {
                    $linkType == 'Internal';
                    if ($internalType == 'P')
                    {
                        $internalPage = $cascade->getAsset( a\Page::TYPE, $internalPath, $internalSite );
                    } 
                    else
                    {
                        $internalPage = $cascade->getAsset( a\File::TYPE, $internalPath, $internalSite );
                    }
                    if ($siblings == 0)
                    {
                        echo 'Should be setting the link fields…' . PHP_EOL;
                        $linkType = 'Internal';
                        echo $linkType . PHP_EOL;
                        echo $internalSite . ' ' .$internalPage->getPath() . PHP_EOL;
                        $learnMoreBlock->
                        setText(
                            'link;0;link-type',
                            $linkType )->
                        setLinkable(
                            'link;0;internal',
                            $internalPage )->
                        setText(
                            'link;0;external',
                            NULL )->
                        setText(
                            'link;0;link-text-chooser',
                            'Custom Text' )->
                        setText(
                            'link;0;custom',
                            $linkText )->
                        edit(); // Commit!
                        
                        if ($internalAnchor != NULL) {
                            $learnMoreBlock->
                            setText(
                                'link;0;anchor',
                                $internalAnchor)->
                            edit();
                        }
                        $siblings++;
                    }
                    else
                    {
                        $linkType = 'Internal';
                        echo $linkType . PHP_EOL;
                        echo $internalSite . ' ' .$internalPage->getPath() . PHP_EOL;
                        $learnMoreBlock->appendSibling( 'link;0' )->
                        setText(
                            'link;' . $siblings . ';link-type',
                            $linkType )->
                        setLinkable(
                            'link;' . $siblings . ';internal',
                            $internalPage )->
                        setText(
                            'link;' . $siblings . ';external',
                            NULL )->
                        setText(
                            'link;' . $siblings . ';link-text-chooser',
                            'Custom Text' )->
                        setText(
                            'link;' . $siblings . ';custom',
                            $linkText )->
                        edit(); // Commit!
                        
                        if ($internalAnchor != NULL) {
                            $learnMoreBlock->
                            setText(
                                'link;' . $siblings . ';anchor',
                                $internalAnchor)->
                            edit();
                        }
                        $siblings++;                        
                    }
                } 
                else 
                {
                    $linkType == 'External';
                    if ($siblings == 0)
                    {
                        echo 'Should be setting the link fields…' . PHP_EOL;
                        echo $externalURL . PHP_EOL;
                        $linkType = 'External';
                        echo $linkType . PHP_EOL;
                        $learnMoreBlock->
                        setText(
                            'link;0;link-type',
                            $linkType )->
                        setLinkable(
                            'link;0;internal',
                            NULL )->
                        setText(
                            'link;0;external',
                            $externalURL )->
                        setText(
                            'link;0;link-text-chooser',
                            'Custom Text' )->
                        setText(
                            'link;0;custom',
                            $linkText )->
                        edit(); // Commit!
                        $siblings++;
                    }
                    else
                    {
                        echo $externalURL . PHP_EOL;
                        $linkType = 'External';
                        echo $linkType . PHP_EOL;
                        $learnMoreBlock->appendSibling( 'link;0' )->
                        setText(
                            'link;' . $siblings . ';link-type',
                            $linkType )->
                        setLinkable(
                            'link;' . $siblings . ';internal',
                            NULL )->
                        setText(
                            'link;' . $siblings . ';external',
                            $externalURL )->
                        setText(
                            'link;' . $siblings . ';link-text-chooser',
                            'Custom Text' )->
                        setText(
                            'link;' . $siblings . ';custom',
                            $linkText )->
                        edit(); // Commit!
                        $siblings++;                        
                    }
                }
                
                
                
            }    
                
        }
        
    }
       
}
catch( \Exception $e ) 
{
    echo S_PRE . $e . E_PRE; 
}
?>