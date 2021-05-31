<?php

// Pluggable
require_once( ABSPATH . "wp-includes/pluggable.php" );

// Image Handler
require_once( "image-handler.php" );

function hbdev_run_import() {
	
	// $file = plugin_dir_url( __FILE__ ) . 'example.xml';
	// $imageFile = plugin_dir_url( __FILE__ ) . 'response-img.xml';


	// Set the XML Feed URL Here
	$productXML = simplexml_load_file('https://rctdatafeed.azurewebsites.net/xml/a325ca80-0eb1-41a9-8c09-afa42d866618/v1/Products/onhand');
	// $productXML = simplexml_load_file($file);

	// [Product Arr]
	$productArr = $productXML->Value->ProductDto;

	// FASTEN the Import process , set to false at the end
	wp_defer_term_counting( true );
	wp_defer_comment_counting( true );


	// Loop thru all the properties
	foreach ( $productArr as $props ) {

		// Get the SKU
		$sku = (string)$props->Code;
		$sku = trim( $sku );

		// On Hand
		$stock = (string)$props->OnHand;

		// Check post by SKU ID
		$existingPost = get_posts( array(
			'post_type' => 'product', 
			'meta_key' => '_sku', 
			'meta_value' => $sku
		));

		
		if ( !empty($existingPost)  ) {

			// get existing product stock
			$existingStock = get_post_meta( $existingPost[0]->ID, '_stock', true);

			
			if ( $existingStock !== $stock ) {
				update_post_meta( $existingPost[0]->ID, '_stock', $stock);
				$afterUpdated = get_post_meta( $existingPost[0]->ID, '_stock', true);
			
				echo '<span style="color: red;">Duplicate Product could not be added >> Code: ' . $sku . ' >> ID: ' . $existingPost[0]->ID . ' >> Stock: ' .  $existingStock  . '</span><span style="color: blue; font-weight: bold; "> ( Updated >> Stock: ' . $afterUpdated . ' ) </span><br/>' ;
			
			} else {

				echo '<span style="color: red;">Duplicate Product could not be added >> Code: ' . $sku . ' >> ID: ' . $existingPost[0]->ID . ' >> Stock: ' .  $existingStock  . '</span><br/>' ;
			}
			continue;
		}                          

		// Get the Title
		$title = (string)$props->Title;

		// Get the desc
		$desc = (string)$props->Description;

		// Get the Selling Price
		$price = (string)$props->SellingPrice;

		// Category
		$cat = (string)$props->ProductLine;

		// Images
		// $imageXML = simplexml_load_file($imageFile);
		$imageXML = simplexml_load_file('https://rctdatafeed.azurewebsites.net/xml/a325ca80-0eb1-41a9-8c09-afa42d866618/v1/Products/images/' . $sku );
		
		if ( $imageXML ) {

			$images = $imageXML->ProductImageDto;

		
			// Init IMAGES arr
			$imagesArr = [];

			if ( !empty( $images) ) {

				foreach ( $images as $image ) {
					$imageURL = (string)$image->imageUrl;
					array_push($imagesArr, $imageURL);
				}
	
				// Featured Image
				$thumb = $imagesArr[0];

			} else { 
				$thumb = ' ';
			}

		}
		
		


		if ( empty($existingPost) ) {
			
			// Insert new property
			$product_id = wp_insert_post(array (
				'post_type' => 'product',
				'post_title' => $title,
				'post_content' => $desc,
				'post_status' => 'publish',
			));

			if ( $product_id ) {
				update_post_meta($product_id, '_sku', $sku);                //  SKU
				update_post_meta($product_id, '_manage_stock', 'yes');      //  Manage Stock
				update_post_meta($product_id, '_stock', $stock);            //  Stock

				
				update_post_meta($product_id, '_price', $price);            //  Price
				update_post_meta($product_id, '_regular_price', $price);    //  Price

				wp_set_object_terms( $product_id, $cat, 'product_cat' );    // Cat

				if ( !empty($imagesArr) ) {
					$imagesArr_src = array_map( "hbel_image_array_src", $imagesArr);
					$imagesStr = implode( PHP_EOL, $imagesArr_src);
					update_post_meta($product_id, '_hprod_images', $imagesStr);   //  Custom Meta for Images

				}

				// Upload Featured Image
				if ( $thumb !== ' ') {
					$attach_id = hbel_insert_attachment_from_url( (string)$thumb );

				} 

				if ( $attach_id ) {
					set_post_thumbnail( $product_id, $attach_id );	   
				}
			    echo '<span style="color: green;">Product added >> Code: ' . $sku . ' >> ID: ' . $product_id . ' >> Stock ' . $stock . '</span><br/>' ;

			}
			continue;
			
		}

	}

	wp_defer_term_counting( false );
	wp_defer_comment_counting( false );


}
