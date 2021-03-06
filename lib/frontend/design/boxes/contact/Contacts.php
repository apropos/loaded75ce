<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes\contact;

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Contacts extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {

    $data = Info::platformData();
    $address = $data;
    $address['name'] = '';
    $address['reg_number'] = '';

    if ($this->settings[0]['time_format'] == '24'){
      foreach ($data['open'] as $key => $item){
        $data['open'][$key]['time_from'] = date("G:i", strtotime($item['time_from']));
        $data['open'][$key]['time_to'] = date("G:i", strtotime($item['time_to']));
      }
    }

    return IncludeTpl::widget(['file' => 'boxes/contact/contacts.tpl', 'params' => [
      'data' => $data,
      'phone' => '+' . preg_replace("/[^0-9]/i", "", $data['telephone']),
      'address' => \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($data['country_id']), $address, 0, ' ', '<br>', true),
      'settings' => $this->settings
    ]]);

  }
}