<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;

/**
 * default controller to handle user requests.
 */
class ZonesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_TAXES_ZONES'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'locations', 'zones');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('zones/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      $this->topButtons[] = '<a href="#" class="create_item" onclick="return zoneEdit(0)">'.TEXT_INFO_HEADING_NEW_ZONE.'</a>';
	  
	  $this->view->zonesTable = array(
		array(
			'title' => TABLE_HEADING_COUNTRY_NAME,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_ZONE_NAME,
			'not_important' => 0,
		),
		array(
			'title' => TABLE_HEADING_ZONE_CODE,
			'not_important' => 0,
		),
	  );

      return $this->render('index');
    }

	public function actionList(){
        global $languages_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_prepare_input($_GET['search']['value']);
            $search = " and (c.countries_name like '%" . tep_db_input($keywords) . "%' or c.countries_iso_code_2 like '%" . tep_db_input($keywords) . "%' or c.countries_iso_code_3 like '%" . tep_db_input($keywords) . "%' or z.zone_name like '%" . tep_db_input($keywords) . "%' or z.zone_code like '%" . tep_db_input($keywords) . "%')";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "c.countries_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "z.zone_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "z.zone_code " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;					
                default:
                    $orderBy = "c.sort_order, cd.categories_name";
                    break;
            }
        } else {
            $orderBy = "c.countries_name, z.zone_name";
        }
		
		$current_page_number = ($start / $length) + 1;
        $responseList = array();

		$zones_query_raw = "select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id and c.language_id = '".$languages_id."' " . $search . " order by ".$orderBy;
		$zones_split = new \splitPageResults($current_page_number, $length, $zones_query_raw, $zones_query_numrows);
		$zones_query = tep_db_query($zones_query_raw);
		
		while ($zones = tep_db_fetch_array($zones_query)) {
	
			$responseList[] = array(
				$zones['countries_name'] . tep_draw_hidden_field('id', $zones['zone_id'], 'class="cell_identify"'),
				$zones['zone_name'],
				$zones['zone_code']
			);
		}
		
		$response = array(
            'draw' => $draw,
            'recordsTotal' => $zones_query_numrows,
            'recordsFiltered' => $zones_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
	}
	
	public function actionZonesactions(){
      global $language, $languages_id;
      \common\helpers\Translation::init('admin/zones');
		
		$zones_id = Yii::$app->request->post('zones_id', 0);
		$this->layout = false;
		if ($zones_id){
			$zone = tep_db_fetch_array(tep_db_query("select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id and c.language_id = '".$languages_id."' and z.zone_id = '" . (int)$zones_id . "'"));
			$cInfo = new \objectInfo($zone, false);
			$heading = array();
			$contents = array();		

			$heading[] = array('text' => '<b>' . $cInfo->zone_name . '</b>');
			echo '<div class="or_box_head">' . $cInfo->zone_name . '</div>';
            echo '<div class="row_or_wrapp">';
            echo '<div class="row_or"><div>' . TEXT_INFO_ZONES_NAME . '</div><div>' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')</div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_COUNTRY_NAME . '</div><div>' . $cInfo->countries_name . '</div></div>';
            echo '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-edit btn-no-margin" onclick="zoneEdit('.$zones_id.')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="zoneDelete('.$zones_id.')">' . IMAGE_DELETE . '</button></div>';
			$contents[] = array('align' => 'center', 'text' => '<input type="button" value="' . IMAGE_EDIT . '" class="btn btn-no-primary" onclick="zoneEdit('.$zones_id.')"><input type="button" value="' . IMAGE_DELETE . '" class="btn btn-primary" onclick="zoneDelete('.$zones_id.')">');

			$contents[] = array('text' => '<br><br>' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')');
			$contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . ' ' . $cInfo->countries_name);
			/*$box = new \box;
			echo $box->infoBox($heading, $contents);*/
		}
	  
	}
	
    public function actionEdit(){
      global $language, $languages_id;
      \common\helpers\Translation::init('admin/zones');
	  
	  $zones_id = Yii::$app->request->get('zones_id', 0);
 	  $zone = tep_db_fetch_array(tep_db_query("select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id and c.language_id = '".$languages_id."' and z.zone_id = '" . (int)$zones_id . "'"));
	  $cInfo = new \objectInfo($zone, false);

	  $heading = array();
	  $contents = array();	  
	   
      $contents = array('form' => tep_draw_form('zones', FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_NAME . '<br>' . tep_draw_input_field('zone_name', $cInfo->zone_name));
      $contents[] = array('text' => '<br>' . TEXT_INFO_ZONES_CODE . '<br>' . tep_draw_input_field('zone_code', $cInfo->zone_code));
      $contents[] = array('text' => '<br>' . TEXT_INFO_COUNTRY_NAME . '<br>' . tep_draw_pull_down_menu('zone_country_id', \common\helpers\Country::get_countries(), $cInfo->countries_id));

      $contents[] = array('align' => 'center', 'text' => '<br>' . '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-primary" onclick="zoneSave('.($cInfo->zone_id?$cInfo->zone_id:0).')">
	  <input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-primary" onclick="resetStatement()">');
      echo tep_draw_form('zones', FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=save');
      if($zones_id){
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_ZONE . '</div>';
	  } else {
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_ZONE . '</div>';
	  }
      echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_ZONES_NAME . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('zone_name', $cInfo->zone_name) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_ZONES_CODE . '</div>';
      echo '<div class="main_value">' . tep_draw_input_field('zone_code', $cInfo->zone_code) . '</div>';
      echo '</div>';
      echo '<div class="main_row">';
      echo '<div class="main_title">' . TEXT_INFO_COUNTRY_NAME . '</div>';
      echo '<div class="main_value">' . tep_draw_pull_down_menu('zone_country_id', \common\helpers\Country::get_countries(), $cInfo->countries_id) . '</div>';
      echo '</div>';
      echo '<div class="btn-toolbar btn-toolbar-order"><input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="zoneSave('.($cInfo->zone_id?$cInfo->zone_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()"></div>';
      echo '</form>';
  	  /*$box = new \box;
	  echo $box->infoBox($heading, $contents);*/
	  
	}
	
    public function actionSave(){
      global $language;
      \common\helpers\Translation::init('admin/zones');
	  
	  $zones_id = Yii::$app->request->get('zones_id', 0);

	  if($zones_id == 0){
        $zone_country_id = tep_db_prepare_input($_POST['zone_country_id']);
        $zone_code = tep_db_prepare_input($_POST['zone_code']);
        $zone_name = tep_db_prepare_input($_POST['zone_name']);

        tep_db_query("insert into " . TABLE_ZONES . " (zone_country_id, zone_code, zone_name) values ('" . (int)$zone_country_id . "', '" . tep_db_input($zone_code) . "', '" . tep_db_input($zone_name) . "')");

		$action		 = 'added';
	  } else {
        $zone_country_id = tep_db_prepare_input($_POST['zone_country_id']);
        $zone_code = tep_db_prepare_input($_POST['zone_code']);
        $zone_name = tep_db_prepare_input($_POST['zone_name']);

        tep_db_query("update " . TABLE_ZONES . " set zone_country_id = '" . (int)$zone_country_id . "', zone_code = '" . tep_db_input($zone_code) . "', zone_name = '" . tep_db_input($zone_name) . "' where zone_id = '" . (int)$zones_id . "'");

		$action		 = 'updated';
	  }


		echo json_encode(array('message' => 'County ' . $action, 'messageType' => 'alert-success'));
        
	}

	
    public function actionDelete(){
      global $language;
      \common\helpers\Translation::init('admin/zones');	
        $zones_id = Yii::$app->request->post('zones_id', 0);
		
		if ($zones_id)
			tep_db_query("delete from " . TABLE_ZONES . " where zone_id = '" . (int)$zones_id . "'");

		echo 'reset';
		
	}
}
