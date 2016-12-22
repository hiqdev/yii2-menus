<?php
/**
 * Menus for Yii2
 *
 * @link      https://github.com/hiqdev/yii2-menus
 * @package   yii2-menus
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\yii2\menus\widgets;

use Closure;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Enhanced menu widget with icons, visible callback.
 */
class Menu extends \yii\widgets\Menu
{
    /**
     * @var string Class that will be added for parents "li"
     */
    public $treeClass = 'treeview';

    /**
     * @var boolean activate parents by default
     */
    public $activateParents = true;

    /**
     * @var string default icon class
     */
    public $defaultIcon = 'fa-angle-double-right';

    /**
     * {@inheritdoc}
     */
    public $linkTemplate = '<a href="{url}" {linkOptions}>{icon}&nbsp;{label}</a>';

    /**
     * Try to guess which module is parent for current page
     * and remain sidebarmenu accordion opened.
     * @param array $item
     * @return bool
     */
    protected function guessModule(array $item, $parentUrl = null)
    {
        $result = false;
        $moduleId = Yii::$app->controller->module->id;
        $parentModuleId = $this->getModuleName($parentUrl);
        if (!empty($item['items'])) {
            foreach ($item['items'] as $i) {
                if (isset($i['url'])) {
                    $itemModuleName = $this->getModuleName(reset($i['url']));
                    if ($itemModuleName === $moduleId && $parentModuleId === $moduleId) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get module id from url string.
     * @param $route (like '/dns/zone/index')
     * @return null|string (like 'dns')
     */
    private function getModuleName($route)
    {
        if ($route) {
            if (strpos($route, '/') !== false) {
                list($id, $route) = explode('/', $route, 2);
            } else {
                $id = $route;
                $route = '';
            }

            return $id;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderItems($items)
    {
        $n = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $tag = ArrayHelper::remove($options, 'tag', 'li');
            $class = [];
            $parentModuleUrl = isset($item['items']) ? $item : null;
            if ($item['active'] || $this->guessModule($item, $parentModuleUrl['url'][0])) {
                $class[] = $this->activeCssClass;
            }
            if ($i === 0 && $this->firstItemCssClass !== null) {
                $class[] = $this->firstItemCssClass;
            }
            if ($i === $n - 1 && $this->lastItemCssClass !== null) {
                $class[] = $this->lastItemCssClass;
            }
            $menu = $this->renderItem($item);
            if (!empty($item['items'])) {
                $class[] = $this->treeClass;
                $menu .= strtr($this->submenuTemplate, [
                    '{items}' => $this->renderItems($item['items']),
                ]);
            }
            if (!empty($class)) {
                if (empty($options['class'])) {
                    $options['class'] = implode(' ', $class);
                } else {
                    $options['class'] .= ' ' . implode(' ', $class);
                }
            }
            $lines[] = Html::tag($tag, $menu, $options);
        }

        return implode("\n", $lines);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderItem($item)
    {
        return strtr(ArrayHelper::getValue($item, 'template', isset($item['url']) ? $this->linkTemplate : $this->labelTemplate), [
            '{url}' => isset($item['url']) ? Url::to($item['url']) : null,
            '{icon}' => $item['icon'] === false ? '' : sprintf('<i class="%s"></i>', static::iconClass($item['icon'] ?: $this->defaultIcon)),
            '{label}' => $item['label'],
            '{arrow}' => !empty($item['items']) ? '<i class="fa pull-right fa-angle-left"></i>' : '',
            '{linkOptions}' => Html::renderTagAttributes(ArrayHelper::getValue($item, 'linkOptions', [])),
        ]);
    }

    public static function iconClass($icon)
    {
        return (substr($icon, 0, 3) === 'fa-' ? 'fa fa-fw ' : '') . $icon;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeItems($items, &$active)
    {
        foreach ($items as &$item) {
            if (isset($item['visible']) && $item['visible'] instanceof Closure) {
                $item['visible'] = call_user_func($item['visible']);
            }
        }

        return parent::normalizeItems($items, $active);
    }
}
