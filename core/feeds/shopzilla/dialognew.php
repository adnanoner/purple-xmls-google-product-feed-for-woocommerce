<?php

  /********************************************************************
  Version 2.0
    Front Page Dialog for Shopzilla
	  Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-05

  ********************************************************************/

class ShopzillaDlg extends PBaseFeedDialog {

  function __construct() {
    parent::__construct();
    $this->service_name = 'Shopzilla';
    $this->service_name_long = 'Shopzilla Products XML Export';
	$this->options = array(
        'Manufacturer',
        'Bid',
        'Promotional Code'
	);
  }

  function list_categories() {
    $data = file_get_contents('categories_' . strtolower($this->service_name) . '.txt');
    $arr = explode("\n", $data);
    $key = 0;
    $result = NULL;

    foreach ($arr as $k => $value) {
        if ($value == '') {
            $value = '--- Select ' . $this->service_name . ' Category ---';
			$result .= "<option value='" . $value. "'>" . $value . "</option>";
			continue;
        }
		$list_val=  explode(";", $value);
        $result .= "<option value='" . trim($list_val[1]). "'>" . htmlentities(trim($list_val[0])) . "</option>";
    }

    return $result;
  }

}