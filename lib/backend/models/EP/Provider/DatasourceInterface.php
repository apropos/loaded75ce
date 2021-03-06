<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP\Provider;


use backend\models\EP\Messages;

interface DatasourceInterface
{

    public function getProgress();

    public function prepareProcess(Messages $message);

    public function processRow(Messages $message);

    public function postProcess(Messages $message);

}