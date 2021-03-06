<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\api\models\AR\Products\Inventory;


use common\api\models\AR\EPMap;

class Prices extends EPMap
{
    protected $hideFields = [
        'inventory_id',
        'groups_id',
        'currencies_id',
        'products_id',
        'prid',
    ];

    public static function tableName()
    {
        return TABLE_INVENTORY_PRICES;
    }

    public static function primaryKey()
    {
        return ['inventory_id', 'groups_id', 'currencies_id'];
    }

    public static function getAllKeyCodes()
    {
        $keyCodes = [];
        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach (\common\helpers\Currencies::get_currencies() as $currency){
                foreach (\common\helpers\Group::get_customer_groups() as $groupInfo ) {
                    $keyCode = $currency['code'].'_'.$groupInfo['groups_id'];
                    $keyCodes[$keyCode] = [
                        'inventory_id' => null,
                        'groups_id' => $groupInfo['groups_id'],
                        'currencies_id' => $currency['currencies_id'],
                    ];
                }
            }
        }else{
            foreach (\common\helpers\Group::get_customer_groups() as $groupInfo ) {
                $keyCode = DEFAULT_CURRENCY . '_'.$groupInfo['groups_id'];
                $keyCodes[$keyCode] = [
                    'inventory_id' => null,
                    'groups_id' => $groupInfo['groups_id'],
                    'currencies_id' => 0,
                ];
            }
        }
        return $keyCodes;
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->inventory_id = $parentObject->inventory_id;
        $this->products_id = $parentObject->products_id;
        $this->prid = $parentObject->prid;
        parent::parentEPMap($parentObject);
    }


}