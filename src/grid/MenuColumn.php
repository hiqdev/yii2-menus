<?php
/**
 * Menus for Yii2
 *
 * @link      https://github.com/hiqdev/yii2-menus
 * @package   yii2-menus
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\yii2\menus\grid;

use hiqdev\yii2\menus\widgets\MenuButton;
use yii\grid\DataColumn;

class MenuColumn extends DataColumn
{
    /**
     * {@inheritdoc}
     */
    public $format = 'raw';

    /**
     * {@inheritdoc}
     */
    public $header = '&nbsp;';

    /**
     * {@inheritdoc}
     */
    public $contentOptions = [
        'class' => 'text-center',
    ];

    public $menuClass;

    /**
     * {@inheritdoc}
     */
    public function getDataCellValue($model, $key, $index)
    {
        if ($this->value !== null) {
            return parent::getDataCellValue($model, $key, $index);
        } else {
            $class = $this->menuClass;
            return $class::widget(['model' => $model], MenuButton::class);
        }
    }
}
