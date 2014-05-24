<?php

  /********************************************************************
  Version 2.0
    FeedOverride is in charge of listing Attribute Mappings and similar activities
	So <price>7.00</price> can be overridden to <g:sale_price>7.00</g:sale_price>
	By: Keneto 2014-05-15
  Note: One day, this needs to be moved to Joomla/VirtueMart compatibility

  ********************************************************************/

class PFeedOverride {

  public $overrides = array();

  function __construct($providerName, $parent) {
	global $wpdb;

	$sql = "
            SELECT * FROM $wpdb->options
            WHERE $wpdb->options.option_name LIKE '" . $providerName . "_cp_%'";
	$overrides_from_options = $wpdb->get_results($sql);
	foreach($overrides_from_options as $this_option) {
	  $key = substr($this_option->option_name, strlen($providerName . '_cp_'));
	  $this->overrides[$key] = $this_option->option_value;
	}
	
	//Look for any advanced options
	$loadedOptions = explode("\n", get_option($providerName . '-cart-product-settings'));

	foreach($loadedOptions as $this_option) {
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

    //Some thought was given to allowing "$parent->$this_option = $value"
	//but that looks like security trouble. A chain of if-statements more secure
	if ($this_option == '$currency') {$parent->currency = $value;}
	if ($this_option == '$max_description_length') {$parent->max_description_length = $value;}
	if ($this_option == '$strip_html_markup') {$parent->stripHTML = true;}
	if ($this_option == '$weight_unit') {$parent->weight_unit = $value;}
	
	if ($this_option == '$descriptions') {
	  if ($value == 'long') {$parent->descriptionFormat = 1;}
	  if ($value == 'short') {$parent->descriptionFormat = 2;}
	}
	if ($this_option == '$strict_description') {
	  $parent->descriptionStrict = true;
	  if (strlen($value) > 0)
	    $parent->descriptionStrictReplacementChar = $value;
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