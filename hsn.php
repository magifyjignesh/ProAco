<?php
function save_image($inPath,$outPath)
{ //Download images from remote server
    $in=    fopen($inPath, "rb");
    $out=   fopen($outPath, "wb");
    while ($chunk = fread($in,8192))
    {
        fwrite($out, $chunk, 8192);
    }
    fclose($in);
    fclose($out);
}
 
 

include_once('simple_html_dom.php');
/* ini_set('execution_time','3600');
ini_set('memory_limit','1024M'); */
error_reporting(E_ALL | E_STRICT);
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
$app = Mage::app('default'); 
 
function p($pr){
	echo '<pre>';
	print_r($pr);
	die;
}
function pp($pr){
	echo '<pre>';
	print_r($pr);
	 
}
 

function remove_tag($str, $remove)  
{ 
    while ((strpos($str, '< ') !== false) || (strpos($str, '/ ') !== false)) { 
        $str = str_replace(array('< ', '/ '), array('<', '/'), $str); 
    } 
    foreach ((array) $remove as $tag) { 
        $search_arr = array('<'  . strtolower($tag), '<'  . strtoupper($tag),  
                            '</' . strtolower($tag), '</' . strtoupper($tag)); 
        foreach ($search_arr as $search) { 
            $start_pos = 0; 
            while (($start_pos = strpos($str, $search, $start_pos)) !== false) { 
                $end_pos = strpos($str, '>', $start_pos); 
                $len = $end_pos - $start_pos + 1; 
                $str = substr_replace($str, '', $start_pos, $len); 
            } 
        } 
    } 
    return $str; 
}
 
 

$request = "https://www.kimonolabs.com/api/7e3va2d0?apikey=X7lgSy4Tr2HsLdMLzoyvb32SVoCvsZ4t";
$response = file_get_contents($request);
$results = json_decode($response, TRUE);

$products = $results['results']['collection1'];
 
foreach($products as $product){
	 
	$url = $product['image']['href'];
	$html = file_get_html($url);
	$article = $html->find("html body div#nonFooter table", 0);
	$html1 =  $html->find("table[width=287]",0) ;
	 
	//$html1->find("div#product_disclaimer",0)->innertext = ''; 
	  /*  $html1->find("div#detail-share-bar",1)->innertext = '';  */  
	$name = strtoupper($product['name']['text']);//$html1->find("h2.vertenblkboldprodhead",0)->plaintext;
	 
	$sku = $product['sku']['text'];//$html1->find("span",0)->plaintext;
	$image = str_replace("thumbnail","standard",$product['image']['src']);
	$imageUrl = explode("/",$image);
	$imageUrl = end($imageUrl);
	
	$description = str_replace(trim($name),'',str_get_html($html1));
	$description = str_replace(trim($sku),'',str_get_html($description));
	
	$content = preg_replace("/<img[^>]+\>/i", " ", $description);
	$content = remove_tag($content,'span');
	$content = remove_tag($content,'br');
	$content = str_replace("Contact your nearest location for pricing","",$content);
	$content = str_replace("More      detailed dimensions","",$content);
	$content = str_replace("Due to variations in computer monitors, finishes and fabrics may appear differently online. Visit your nearest Baker  location to view actual samples.","",$content);
	$item['name'] = utf8_decode($name);
	$item['sku'] = $sku; 
	$item['image'] = $image; 
	$item['image_url'] = $imageUrl;
	
	$item['description'] = utf8_decode($content);
	
	$pdfname = explode("/",$product['pdf_url']['href']);
	if(end($pdfname)){
		$item['pdf_name'] = end($pdfname);
		save_image($product['pdf_url']['href'],'/var/www/magify.com/sw/media/pdf/'.end($pdfname));
	} 
	 
	$hpos = strpos($content, 'Height');
	$wpos = strpos($content, 'Width');
	$dpos = strpos($content, 'Depth');

	$max = 16;
	if(strlen($content) > $max) {
		$hh = str_replace(array("i", "in", "inc", "inch", "inche", "inches"), "", substr($content, $hpos, $max));
	    $item['height'] = $hh ;//substr($content, $hpos, $max);
	} 
	
	$max = 16;
	if(strlen($content) > $max) {
		$ww = str_replace(array("i", "in", "inc", "inch", "inche", "inches"), "", substr($content, $wpos, $max));
	    $item['width'] = $ww ;//substr($content, $hpos, $max);
	   //$item['width'] = substr($content, $wpos, $max);
	} 
	
	$max = 16;
	if(strlen($content) > $max) {
		$dd = str_replace(array("i", "in", "inc", "inch", "inche", "inches"), "", substr($content, $dpos, $max));
	    $item['depth'] = $dd ;//substr($content, $hpos, $max);
	    //$item['depth'] = substr($content, $dpos, $max);
	} 
	 
	 
		$data[] = $item;
	 
	
	 
} 
  
 
  $parentId = '2';

	$category = new Mage_Catalog_Model_Category();
	$category->setName('Sideboards');
	$category->setUrlKey('sideboards');
	$category->setIsActive(1);
	$category->setDisplayMode('PRODUCTS');
	$category->setIsAnchor(0);

	$parentCategory = Mage::getModel('catalog/category')->load($parentId);
	$category->setPath($parentCategory->getPath());              

	$category->save();
	$categoryId = $category->getId();

    $entityTypeId = Mage::getModel('catalog/product')
                  ->getResource()
                  ->getEntityType()
                  ->getId(); //product entity type

	$attributeSet = Mage::getModel('eav/entity_attribute_set')
					  ->setEntityTypeId($entityTypeId)
					  ->setAttributeSetName("Sideboards");

	$attributeSet->validate();
	$attributeSet->save();
	$attrSetId = $attributeSet->getId();

	$attributeSet->initFromSkeleton(4)->save(); 
 

foreach($data as $prod){
	 
	try{
		$image = explode(".",$prod['image_url']);
		$url1 = 'http://s7d4.scene7.com/is/image/KIG/'.$image[0];
		$url2 = $url1.'?qlt=85,1&op_sharpen=1&rgn=0,0,2000,2000&scl=4.651162790697675';
		//save_image($url2,'/var/www/magify.com/sw/media/catalog/product/'.$prod['image_url']);

		Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
		$product = Mage::getModel('catalog/product');
		
		//p(product->getIdBySku($prod['sku']));
		if(!$product->getIdBySku($prod['sku'])){
			
			$product->setStoreId(1) 
				->setWebsiteIds(array(1)) 
				->setAttributeSetId($attrSetId)
				->setTypeId('simple') 
				->setCreatedAt(strtotime('now')) 
				->setUpdatedAt(strtotime('now')) 
				->setSku($prod['sku']) 
				->setName(trim($prod['name'])) 
				->setWeight(1.0000)
				->setStatus(1) 
				->setHeight($prod['height'])
				->setWidth($prod['width'])
				->setDepth($prod['depth'])
				->setDescription(utf8_decode($prod['description']))
				->setShortDescription(utf8_decode($prod['description']))
				->setSpecificationUrl(Mage::getBaseUrl('media').'pdf/'.$prod['pdf_name'])
				->setTaxClassId(4) 
				->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) 
				->setPrice(999.99) 
				// ->addImageToMediaGallery('/var/www/magify.com/sw/media/catalog/product/'.$prod['image_url'], array('image','thumbnail','small_image'), false, false)
				->setStockData(array(
					   'use_config_manage_stock' => 0,  
					   'manage_stock'=>1,  
					   'min_sale_qty'=>1,  
					   'max_sale_qty'=>2,  
					   'is_in_stock' => 1,  
					   'qty' => 999  
					)
				)
				->setCategoryIds(array($categoryId)); 
			try{
				$product->save();
				echo $product->getId();
				echo "<br/>";
			}	catch(Exception $e){
				echo $e->getMessage();
			}
		}
	} catch(Exception $e){
			echo $e->getMessage();
		}
   
}
?>