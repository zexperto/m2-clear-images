<?php

use Magento\Framework\App\Bootstrap;

include 'app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');

$connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

$imgsQuery = $connection->select()
    ->from(['imgs' => $resource->getTableName('catalog_product_entity_media_gallery')], ['value']
    );
$result = $connection->fetchAll($imgsQuery);

$store_images = array_column ($result, "value");

//print_r($store_images);

$fileSystem = $objectManager->get('\Magento\Framework\Filesystem');

$absoluteMediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();

$productImages  = $absoluteMediaPath."catalog/product";


// get real files
$realFiles = [];
$scanned_directory = getDirContents($productImages, $realFiles, $productImages);

$to_be_deleted = array_diff($realFiles,$store_images);


function getDirContents($dir, &$results = array(), $productImages){
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
           // print  str_replace ('',"",$path)."<br>";
            $results[] =  str_replace ($productImages,"",$path);
        } else if($value != "." && $value != ".." && $value !="cache") {
            getDirContents($path, $results, $productImages);
            //$results[] = $path;
        }
    }
    return $results;
}

print "The database images count = ".count($store_images).
            ", And the real images count =".count($realFiles).
             ", Images to be deleted ".count($to_be_deleted).
            "<br>";
foreach ($to_be_deleted as $item){
    $full_path = $productImages.$item;
    print $full_path."<br>";
    unlink($full_path);
}

?>
