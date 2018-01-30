<?php
/**
 * Menus for Yii2.
 *
 * @link      https://github.com/hiqdev/yii2-menus
 * @package   yii2-menus
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016-2017, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\yii2\menus\widgets;

use yii\helpers\Html;

class MenuButton extends \yii\base\Widget
{
    public $icon = '<i class="fa fa-bars"></i>&nbsp;&nbsp;<span class="caret"></span>';

    public $items;

    public $options;

    public $menuClass = Menu::class;

    public function run()
    {
        $actionsMenu = $this->renderMenuItems();
        if ($actionsMenu === '') {
            return '';
        }

        $this->getView()->registerCss('
        .menu-button .nav > li > a {
            padding: 5px 15px;
        }
        .menu-button .popover-content {
            padding: 9px 0px;
        }
        ');
        $this->getView()->registerJs("
            // Init popover
            ;(function () {
                var actionsButton = $('.menu-button button[data-toggle=\"popover\"]');
                actionsButton.popover();
                // Show one popover and hide other popovers
                actionsButton.on('click', function (e) {
                    actionsButton.not(this).popover('hide');
                });
                // hide popover on click outside
                $(document).on('click', function (e) {
                    actionsButton.each(function () {
                        //the 'is' for buttons that trigger popups
                        //the 'has' for icons within a button that triggers a popup
                        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                            (($(this).popover('hide').data('bs.popover')||{}).inState||{}).click = false  // fix for BS 3.3.6
                        }

                    });
                });
            })();
        ");
        $html = Html::beginTag('div', [
            'class' => 'menu-button visible-lg-inline visible-md-inline visible-sm-inline visible-xs-inline',
        ]);
        $html .= Html::button($this->icon, [
            'class' => 'btn btn-default btn-xs',
            'data' => [
                'toggle' => 'popover',
                'trigger' => 'click',
                'content' => $actionsMenu,
                'html' => 'true',
                'placement' => 'bottom',
            ],
        ]);
        $html .= Html::endTag('div');

        return $html;
    }

    /**
     * @return string
     */
    protected function renderMenuItems()
    {
        $class = $this->menuClass;

        return $class::widget([
            'items' => $this->items,
            'options' => [
                'class' => 'nav',
            ],
        ]);
    }
}
