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
    public $defaultIcon = null;

    /**
     * {@inheritdoc}
     */
    public $linkTemplate = '<a href="{url}" {linkOptions}>{icon}{iconSpace}{label}</a>';

    /**
     * {@inheritdoc}
     */
    public $labelTemplate = '{icon}{iconSpace}{label}';

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
                [$id] = explode('/', ltrim($route, '/'), 2);
            } else {
                $id = $route;
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
            $isAccordionOpen = $this->guessModule($item, $parentModuleUrl['url'][0]);
            if ($item['active'] || $isAccordionOpen) {
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
     *
     * @param array $item
     * Additional data might be in the item:
     * - isNew: boolean, optional, marks this item new and should be highlighted
     * - linkOptions: array, optional, the HTML attributes for the menu link tag
     * - icon: string, optional, https://fontawesome.com icon
     * - iconSpace: boolean, optional, added space after icon
     *
     * @return string
     */
    protected function renderItem($item)
    {
        $icon = $item['icon'] ?? null;
        $no_icon = $icon ? false : ($icon === false || empty($item['url']) || empty($this->defaultIcon));

        return strtr(ArrayHelper::getValue($item, 'template', isset($item['url']) ? $this->linkTemplate : $this->labelTemplate), [
            '{url}' => isset($item['url']) ? Url::to($item['url']) : null,
            '{icon}' => $no_icon ? '' : sprintf('<i class="%s"></i>', static::iconClass($icon ?: $this->defaultIcon)),
            '{iconSpace}' => $no_icon ? '' : '&nbsp;',
            '{label}' => $item['label'],
            '{arrow}' => sprintf(
                '<span class="pull-right-container">%s %s</span>',
                !empty($item['items']) ? '<small class="fa fa-angle-left pull-right "></small>' : '',
                ($item['isNew'] ?? false) ? '<small class="label pull-right bg-red">new</small>' : ''
            ),
            '{linkOptions}' => Html::renderTagAttributes(ArrayHelper::getValue($item, 'linkOptions', [])),
        ]);
    }

    public static function iconClass($icon)
    {
        return (strpos($icon, 'fa-') === 0 ? 'fa fa-fw ' : '') . $icon;
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
