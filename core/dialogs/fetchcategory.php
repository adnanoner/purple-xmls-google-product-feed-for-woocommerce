<?php

  /********************************************************************
  Version 2.0
    Go get the category
	By: Keneto 2014-05-18

  ********************************************************************/
  
  //require_once '../../../../../wp-load.php';
  
  $data = file_get_contents('categories_' . strtolower($_POST['service_name']) . '.txt');
  $data = explode("\n", $data);
  $searchTerm = strtolower($_POST['partial_data']);
  $count = 0;
  $canDisplay = true;
  foreach($data as $this_item) {
    if (strlen($this_item) * strlen($searchTerm) == 0) {
	  continue;
	}

    if (strpos(strtolower($this_item), $searchTerm) !== false) {
	
      //Transform item from chicken-scratch into something the system can recognize later
	  $option = str_replace(" & ", ".and.", str_replace(" / ", ".in.", trim($this_item)));
	  
	  //Transform a category from chicken-scratch into something the user can read
	  $text = htmlentities(trim($this_item));
	
	  if ($canDisplay) {
	    echo '<div class="categoryItem" onclick="doSelectCategory(this, \'' . $option . '\')">' . $text . '</div>';
	  }
	  $count++;
	  
	  if ((strlen($searchTerm) < 3) && ($count > 15)) {
	    $canDisplay = false;
	  }
	}
  }
  
  if ($count == 0) {
    //echo 'No matching categories found';
  }
  if (!$canDisplay) {
    echo '<div class="categoryItem">(' . $count . ' results)</div>';
  }

?>