<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design\boxes\email;

use Yii;
use yii\base\Widget;

class Logo extends Widget
{

  public $id;
  public $params;
  public $settings;
  public $visibility;

  public function init()
  {
    parent::init();
  }

  public function run()
  {

    global $languages_id;

    $languages = \common\helpers\Language::get_languages();
    $lang = array();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      $languages[$i]['logo'] = $languages[$i]['image'];
      $lang[] = $languages[$i];
    }

    return $this->render('../../views/logo.tpl', [
      'id' => $this->id,
      'params' => $this->params,
      'settings' => $this->settings,
      'languages' => $lang,
      'languages_id' => $languages_id,
      'visibility' => $this->visibility,
    ]);
  }
}