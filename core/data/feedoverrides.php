<?php

  /********************************************************************
  Version 2.0
    FeedOverride is in charge of listing Attribute Mappings and similar activities
	So <price>7.00</price> can be overridden to <g:sale_price>7.00</g:sale_price>
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-15
  Note: FeedOverrides is also a kind of "FeedDefaults" and a "FeedAdjustments" too
    This name is getting a little overloaded and might be better organized as three
	separate classes.

  ********************************************************************/

class PFeedOverride {

  public $overrides = array();

  function __construct($providerName, $parent) {

	global $pfcore;
	$loadOverrides = 'loadOverrides' . $pfcore->callSuffix;
	$this->$loadOverrides($providerName);

	foreach($this->loadedOptions as $this_option) {
	  //$xyz 			means single setting true or = something
	  //x = y			means custom mapping (my field to given attribute)
	  //x = $y			means custom mapping (my field to fixed value y)
	  $this_option = trim($this_option);
	  if (strlen($this_option) == 0) {
	    continue;
	  }

	  if (substr($this_option, 0, 1) == '$') {
	    $this->interpretSingleSetting($this_option, $parent);
	  } else {
	    $this->interpretOverride($this_option);
	  }

	}

  }

  private function loadOverridesJ($providerName) {
	$db = JFactory::getDBO();

	//Attribute Mappings
	$sql = "
            SELECT name, value FROM #__cartproductfeed_options
            WHERE name LIKE '" . $providerName . "_cp_%'";
	$db->setQuery($sql);
	$db->query();
	$overrides_from_options = $db->loadObjectList();
	foreach($overrides_from_options as $this_option) {
	  $key = substr($this_option->name, strlen($providerName . '_cp_'));
	  $this->overrides[$key] = $this_option->value;
	}

	//Advanced options
	$sql = "
            SELECT value FROM #__cartproductfeed_options
            WHERE name = '" . $providerName . "-cart-product-settings'";
	$db->setQuery($sql);
	$db->query();
	$loadedOptions = $db->loadResult();
	if (strlen($loadedOptions) > 0)
	  $this->loadedOptions = explode("\n", $loadedOptions);
	else
	  $this->loadedOptions = array();
  }
  
  private function loadOverridesW($providerName) {
	global $wpdb;

	//Attribute Mappings
	$sql = "
            SELECT * FROM $wpdb->options
            WHERE $wpdb->options.option_name LIKE '" . $providerName . "_cp_%'";
	$overrides_from_options = $wpdb->get_results($sql);
	foreach($overrides_from_options as $this_option) {
	  $key = substr($this_option->option_name, strlen($providerName . '_cp_'));
	  $this->overrides[$key] = $this_option->option_value;
	}

	//Advanced options
	$this->loadedOptions = explode("\n", get_option($providerName . '-cart-product-settings'));
  }

  //determine if value should be overridden. No! This caused ghost attributes
  public function exists($value) {
    $result = false;
    foreach($this->overrides as $a) {
	  if ($a == $value) {
	    $result = true;
		break;
	  }
	}
	return $result;
  }

  /*function indexOf($attribute) {
    $result = -1;
	foreach($this->overrides
  }*/

  function interpretSingleSetting($this_option, $parent) {

    $valueIndex = strpos($this_option, '=');
	if ($valueIndex === false) {
	  $value = '';
	  $this_option = trim($this_option);
	} else {
	  $value = trim(substr($this_option, $valueIndex + 1));
	  $this_option = trim(substr($this_option, 0, $valueIndex - 1));
	}

	//ignore comments
	if (substr($this_option, 0, 1) == ';') {return;}
	if (substr($this_option, 0, 2) == '//') {return;}
	$this_option = strtolower($this_option);
    //Some thought was given to allowing "$parent->$this_option = $value"
	//but that looks like security trouble. A chain of if-statements more secure

	if ($this_option == '$currency') {$parent->currency = $value;} //Deprecated. Use $currency_format
	if ($this_option == '$currency_shipping') {$parent->currency_shipping = $value;}
	//if ($this_option == '$bing_force_google_category') {$parent->bingForceGoogleCategory = true;} //Not IMPL
	if ($this_option == '$bing_force_price_discount') {$parent->bingForcePriceDiscount = true;} //Debug for beta testers
	if ($this_option == '$currency_format') {$parent->currency_format = $value;}
	if ($this_option == '$default_brand') {$parent->default_brand = $value;}
	if ($this_option == '$exclude_variable_attributes') {$parent->productList->exclude_variable_attributes = true;}
	if ($this_option == '$field_delimiter') {$parent->fieldDelimiter = $value;}
	if ($this_option == '$max_description_length') {$parent->max_description_length = $value;}
	if ($this_option == '$productTypeFromLocalCategory') {$parent->productTypeFromLocalCategory = true;}
	if ($this_option == '$productTypeFromWooCommerceCategory') {$parent->productTypeFromLocalCategory = true;} //Deprecated
	if ($this_option == '$strip_html_markup') {$parent->stripHTML = true;}
	if ($this_option == '$system_wide_shipping_type') {$parent->system_wide_shipping_type = $value;}  //Deprecated. Use $shipping
	if ($this_option == '$timeout') {$parent->timeout = $value;}
	if ($this_option == '$weight_unit') {$parent->weight_unit = $value;}

	if ($this_option == '$descriptions') {
	  if ($value == 'long') {$parent->descriptionFormat = 1;}
	  if ($value == 'short') {$parent->descriptionFormat = 2;}
	}
	if ($this_option == '$google_merchant_center') {
	  $parent->gmc_enabled = true;
		if (strlen($value) > 0)
		  $parent->gmc_attributes[] = $value;

	}
	if ($this_option == '$strict_description') {
	  $parent->descriptionStrict = true;
	  if (strlen($value) > 0)
	    $parent->descriptionStrictReplacementChar = $value;
	}
	
	/***********************************************************
	 System-Wide Discount
	 ***********************************************************/

	if ($this_option == '$discount') {
	  /*
	    Note: the spaces for the explode function to work
		Note: BEDMAS means multiplier stronger than additive value
	    $discount = 5			Take 5 dollars off
		$discount = 5 s			Take 5 dollars off sale price (if sale given - if sale not given, do not apply discount)
		$discount = 0.95 *		Take 95% of price (5% discount)
		$discount = 0.95 * s	Take 95% of sale price (5% discount)
	  */
	  $parent->discount = true;
	  $discount_parameters = explode(' ', $value);
	  //Look for the number
	  foreach($discount_parameters as $this_parameter)
	    if (is_numeric($this_parameter)) {
		  $number_value = $this_parameter;
		  break;
		}
	  if (in_array('*', $discount_parameters)) {
	    //multiplier. Default number_value -> 1.00
		if (!isset($number_value)) $number_value = 1;
		if (in_array('s', $discount_parameters))
		  $parent->discount_sale_multiplier = $number_value;
		else
		  $parent->discount_multiplier = $number_value;
	  } else {
	    //Additive value
		if (!isset($number_value)) $number_value = 0;
		if (in_array('s', $discount_parameters))
		  $parent->discount_sale = $number_value;
		else
		  $parent->discount_amount = $number_value;
	  }
	}

	/***********************************************************
	 System-Wide Shipping - Legacy
	 ***********************************************************/

	//Deprecated shipping code. Migrate people away from this
	if ($this_option == '$system_wide_shipping') {
	  $parent->system_wide_shipping = true;
	  $parent->system_wide_shipping_rate = $value;
	  if (($value == 'false') || ($value == 'off') || ($value == 'no')) {
	    $parent->system_wide_shipping = false;
	  }
	}

	/***********************************************************
	 System-Wide Shipping
	 ***********************************************************/
	 
	if ($this_option == '$shipping') {
	  /*
	    Note: the spaces for the explode function to work
		Note: BEDMAS means multiplier stronger than additive value
		$shipping = off|false	Shipping cost is forced off
	    $shipping = 5			Shipping cost is $5
		$shipping = 0.95 *		Shipping is 95% of the full price
		$shipping = 0.95 * s	Shipping is 95% of the sale price
		$shipping = grnd|air t	Type
	  */
	  $parameters = explode(' ', $value);
	  //Look for the number
	  foreach($parameters as $this_parameter)
	    if (is_numeric($this_parameter)) {
		  $number_value = $this_parameter;
		  break;
		}
	  if (in_array('*', $parameters)) {
	    //multiplier. Default number_value -> 1.00
		if (!isset($number_value)) $number_value = 1;
		if (in_array('s', $parameters))
		  $parent->shipping_sale_multiplier = $number_value;
		else
		  $parent->shipping_multiplier = $number_value;
	  } elseif (in_array('t', $parameters))
	    $parent->system_wide_shipping_type = $parameters[0];
	  else {
	    //Additive value
		if (!isset($number_value)) $number_value = 0;
		$parent->shipping_amount = $number_value;
	  }

	  //Toggle Shipping on/off
	  $parent->system_wide_shipping = true;
	  if (($value == 'false') || ($value == 'off') || ($value == 'no'))
	    $parent->system_wide_shipping = false;
	}

	if ($this_option == '$system_wide_tax') {
	  $parent->system_wide_tax = true;
	  $parent->system_wide_tax_rate = $value;
	}

  }

  function interpretOverride($this_option) {
    $valueIndex = strpos($this_option, '=');
	if ($valueIndex === false) {
	  $value = '';
	  $this_option = trim($this_option);
	} else {
	  $value = trim(substr($this_option, $valueIndex + 1));
	  $this_option = trim(substr($this_option, 0, $valueIndex - 1));
	}
	$this->overrides[$this_option] = $value;
  }

}