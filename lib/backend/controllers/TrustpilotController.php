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

use common\extensions\Trustpilot\Client;
use Yii;
use backend\models\Admin;

/**
 * default controller to handle user requests.
 */
class TrustpilotController extends Sceleton
{
    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_MARKETING_TRUSTPILOT'];
    
    protected $selected_platform_id;
    protected $filters = [];
    
    public function __construct($id, $module = null) {
        parent::__construct($id, $module);
        
        $this->selected_platform_id = \common\classes\platform::firstId();
        
        $try_set_platform = Yii::$app->request->get('platform_id', 0);
        if ( Yii::$app->request->isPost ) {
          $try_set_platform = Yii::$app->request->post('platform_id', $try_set_platform);
        }
        if ( $try_set_platform>0 ) {
          foreach (\common\classes\platform::getList(false) as $_platform) {
            if ((int)$try_set_platform==(int)$_platform['id']){
              $this->selected_platform_id = (int)$try_set_platform;
            }
          }
          Yii::$app->get('platform')->config($this->selected_platform_id)->constant_up();
        }
    }
    
    private function initFilters()
    {
        $filters = Yii::$app->request->post('export',[]);
        $orderStatusesSelect = [
            '' => TEXT_TRUSTPILOT_EXPORT_CONFIGURED_STATUSES,
        ];
        foreach( \common\helpers\Order::getStatusesGrouped() as $option){
            $orderStatusesSelect[$option['id']] = html_entity_decode($option['text'],null,'UTF-8');
        }
        $this->filters['order_status'] = [
            'items' => $orderStatusesSelect,
            'value' => [],
        ];
        
        $this->filters['date_type_range'] = [
            'value' => [],
        ];
        $this->filters['date_type_range'] = [
            'value' => [],
        ];
        $this->filters['interval'] = [
            'items' => [
                '' => TEXT_ALL,
                '1' => TEXT_TODAY,
                'week' => TEXT_WEEK,
                'month' => TEXT_THIS_MONTH,
                'year' => TEXT_THIS_YEAR,
                '3' => TEXT_LAST_THREE_DAYS,
                '7' => TEXT_LAST_SEVEN_DAYS,
                '14' => TEXT_LAST_FOURTEEN_DAYS,
                '30' => TEXT_LAST_THIRTY_DAYS,
            ],
            'value' => Yii::$app->request->get('interval',[]),
        ];
        $this->filters['by_totals'] = [
            'items' => [
                'ot_subtotal' => SUB_TOTAL,
                'ot_total' => TABLE_HEADING_TOTAL,
            ],
            'value' => Yii::$app->request->get('interval',[]),
        ];
        $this->filters['date_from'] = [
            'value' => [],
        ];
        $this->filters['date_to'] = [
            'value' => [],
        ];
        $this->filters['re_export'] = [
            'value' => [],
        ];
        $this->filters['by_totals_val_from'] = [
            'value' => [],
        ];
        $this->filters['by_totals_val_to'] = [
            'value' => [],
        ];
        $this->filters['add_totals'] = [
            'value' => [],
        ];
        $this->filters['all_orders'] = [
            'value' => [],
        ];
        $post = Yii::$app->request->post('export');
        foreach( \common\classes\platform::getList() as $platform ) {
            foreach( array_keys($this->filters) as $key ) {
                $value = '';
                if ( $key=='date_type_range' ) $value = 'presel';
                if ( isset($post[$platform['id']]) && isset($post[$platform['id']][$key]) ) {
                    $value = $post[$platform['id']][$key];
                }
                $this->filters[$key]['value'][$platform['id']] = $value;
                if ( ($key=='date_from' || $key=='date_to') && !empty($value) ) {
                    $value_time = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $value);
                    if ( $value_time ) {
                        $this->filters[$key]['value_datetime'][$platform['id']] = $value_time->format('Y-m-d H:i:s');
                    }
                }
            }
        }
    }
    
    public function actionIndex()
    {
        $this->selectedMenu = array('marketing', FILENAME_TRUSTPILOT);
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl(FILENAME_TRUSTPILOT.'/'), 'title' => BOX_MARKETING_TRUSTPILOT/*HEADING_TITLE*/);
        $this->view->headingTitle = BOX_MARKETING_TRUSTPILOT/*HEADING_TITLE*/;

        $platformList = \common\classes\platform::getList();
        foreach ($platformList  as $idx=>$platformVariant){
            $platformList[$idx]['tabLink'] = Yii::$app->urlManager->createUrl([FILENAME_TRUSTPILOT.'/', 'platform_id'=>$platformVariant['id']]);
        }

        if ( Yii::$app->request->isPost ){
            foreach( $platformList as $platformVariant ) {
              \common\extensions\Trustpilot\Trustpilot::onPlatformConfigUpdate((int)$platformVariant['id']);
            }
            $this->redirect(Yii::$app->urlManager->createUrl([FILENAME_TRUSTPILOT.'/', 'platform_id'=>$this->selected_platform_id]));
        }
        
        $this->initFilters();

        return $this->render('index.tpl', [
            'platforms' => $platformList,
            'selected_platform_id' => $this->selected_platform_id,
            'isMultiPlatform' => \common\classes\platform::isMulti(),
            'form_action' => Yii::$app->urlManager->createUrl([FILENAME_TRUSTPILOT.'/']),
            'urlExport' => Yii::$app->urlManager->createUrl([FILENAME_TRUSTPILOT.'/export','export'=>1]),
            'filters' => $this->filters
        ]);
    }
    public function actionExport()
    {
        $this->initFilters();
        $filters = [
            'platform_id' => intval($this->selected_platform_id),
        ];
        foreach ( $this->filters as $key=>$info ) {
            if ( $key=='date_from' || $key=='date_to' ) {
                $filters[$key] = isset($info['value_datetime'][intval($this->selected_platform_id)])?$info['value_datetime'][intval($this->selected_platform_id)]:'';
            }else{
                $filters[$key] = $info['value'][intval($this->selected_platform_id)];
            }
        }
        \common\extensions\Trustpilot\Trustpilot::export($filters);
        die;
    }

    public function actionAuthPopup()
    {
        $this->layout = false;

        $platform_id = Yii::$app->request->get('platform_id');
        $stage = Yii::$app->request->get('stage','fetch');
        if ( $stage=='open' ) {
            $apiKey = Yii::$app->request->get('apiKey', '');

            $client = new Client([
                'apiKey' => $apiKey,
            ]);

            $popUpUrl = Yii::$app->urlManager->createAbsoluteUrl([FILENAME_TRUSTPILOT.'/auth-popup', 'platform_id' => $platform_id, 'stage' => 'fetch'], Yii::$app->request->isSecureConnection ? 'https' : 'http');
            $this->redirect($client->createAuthLink($popUpUrl));
        }elseif($stage=='save'){
            $tokenParams = ltrim(Yii::$app->request->post('tokenParams',''),'#');
            parse_str($tokenParams, $tokenParams);
            if ( is_array($tokenParams) && isset($tokenParams['access_token']) ) {
                tep_db_query(
                    "UPDATE ".TABLE_PLATFORMS_CONFIGURATION." ".
                    "SET configuration_value='".tep_db_input($tokenParams['access_token'])."' ".
                    "WHERE configuration_key='TRUSTPILOT_API_TOKEN' AND platform_id='".(int)$platform_id."'"
                );
            }
            return '<script type="text/javascript">window.close();</script>';
        }else{
            return '
                <form id="frmTpToken" method="post" action="'.Yii::$app->urlManager->createUrl([FILENAME_TRUSTPILOT.'/auth-popup', 'platform_id' => $platform_id, 'stage' => 'save']).'">
                  <input type="hidden" id="txtTokenParams" name="tokenParams" value="">
                </form>
                <script type="text/javascript">
                  document.getElementById(\'txtTokenParams\').value = location.hash;
                  document.getElementById(\'frmTpToken\').submit();
                </script>
            ';
        }
    }

}