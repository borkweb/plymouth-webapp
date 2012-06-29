<?php

class Category extends \PSU\Collection {
	public static $child = 'items';

	public statc function checklist_category () {
		return PSU::db('hr')->getAll("SELECT * FROM checklist_item_categories");
	}//end checklist_items
