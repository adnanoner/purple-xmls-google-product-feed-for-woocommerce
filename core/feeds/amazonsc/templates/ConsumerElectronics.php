<?php
	//********************************************************************
	//Amazon Seller Template
	//2014-12
	//********************************************************************

	$this->addAttributeMapping('feed_product_type', 'feed_product_type',true,true)->localized_name = 'Product Type';
	$this->addAttributeMapping('', 'part_number', true)->localized_name = 'Mfr Part Number';
	$this->addAttributeMapping('', 'brand_name', true)->localized_name = 'Model Number';
	$this->addAttributeMapping('', 'model')->localized_name = 'Item Type';
	$this->addAttributeMapping('', 'condition_type')->localized_name = 'Item Condition';
	$this->addAttributeMapping('', 'condition_note')->localized_name = 'Condition Note';

?>