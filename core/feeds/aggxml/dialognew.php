<?php

	/********************************************************************
	Version 3.0
		Combine one or more feeds
		Copyright 2014 Purple Turtle Productions. All rights reserved.
		license	GNU General Public License version 3 or later; see GPLv3.txt
	By: Keneto 2014-12
	********************************************************************/

class AggXmlDlg extends PBaseFeedDialog {

	function __construct() {
		parent::__construct();
		$this->service_name = 'AggXml';
		$this->service_name_long = 'XML Aggregate Feed';

		global $pfcore;
		$loadFeeds = 'loadFeeds' . $pfcore->callSuffix;
		$this->$loadFeeds();

		$this->feeds = array();
		$providers = new PProviderList();
		foreach($this->feedsAll as $thisFeed) {
			$thisFeed->prettyName = $providers->getPrettyNameByType($thisFeed->type);
			$thisFeed->checked = false;
			$thisFeed->checkedString = '';
			if ($providers->getFileFormatByType($thisFeed->type) == 'xml')
				$this->feeds[] = $thisFeed;
		}
	}

	public function loadFeedsJ() {
		$this->feedsAll = array();
	}

	public function loadFeedsJS() {
		$this->feedsAll = array();
	}

	public function loadFeedsW() {
		global $wpdb;
		$feed_table = $wpdb->prefix . 'cp_feeds';
		$sql = "SELECT id,type,filename,product_count from $feed_table";
		$this->feedsAll = $wpdb->get_results($sql);
	}

	public function loadFeedsWe() {
		$this->loadFeedsW();
	}

	public function mainDialog($feed = null) {
		global $pfcore;
		if ($feed != null) {
			//If the Feed already exists, we need to fill in some check boxes
			$checkedFeeds = $pfcore->settingGet('cpf_aggrfeedlist_' . $feed->id);
			$checkedFeeds = explode(',', $checkedFeeds);
			foreach ($this->feeds as $thisFeed) {
				foreach($checkedFeeds as $check)
					if ($thisFeed->id == $check) {
						$thisFeed->checked = true;
						$thisFeed->checkedString = ' checked="checked"';
						break;
					}
			}
		}
		parent::mainDialog($feed);
	}

}

?>