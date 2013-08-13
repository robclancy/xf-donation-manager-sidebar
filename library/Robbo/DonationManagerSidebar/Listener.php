<?php namespace Robbo\DonationManagerSidebar;

class Listener {

    public static function initDependencies(\XenForo_Dependencies_Abstract $dependencies, array $data)
    {
        \XenForo_CodeEvent::addListener('widget_framework_ready', __CLASS__.'::widgetFrameworkReady');
    }

    public static function widgetFrameworkReady(&$renderers)
    {
        $renderers[] = 'Robbo\DonationManagerSidebar\WidgetFramework\Goal';
    }
}