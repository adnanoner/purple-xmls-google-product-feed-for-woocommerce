<?php

  /********************************************************************
  Version 2.0
    Save a change in attribute mappings
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-05-13

  ********************************************************************/

  require_once dirname(__FILE__) . '/../../../../../../wp-load.php';

  update_option($_POST['service_name'] . '_cp_' . $_POST['attribute'], $_POST['mapto']);



?>