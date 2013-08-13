<?php namespace Robbo\DonationManagerSidebar\WidgetFramework;

abstract class Widget extends \WidgetFramework_WidgetRenderer {

    protected $sidebar;

    protected $active = true;

    public function __construct()
    {
        $this->sidebar = $this->getSidebar();

        if ($this->sidebar->preLoad() === false)
        {
            $this->active = false;
        }
    }

    abstract protected function getSidebar();

    protected function _getConfiguration()
    {
        return array(
            'name' => $this->sidebar->getName(),
            'options' => array(),
        );
    }

    protected function _getOptionsTemplate() { return false; }

    protected function _getRenderTemplate(array $widget, $positionCode, array $params)
    {
        return $this->sidebar->getTemplateName();
    }

    protected function _render(array $widget, $positionCode, array $params, \XenForo_Template_Abstract $renderTemplateObject)
    {
        if ( ! $this->active)
        {
            return false;
        }

        return $this->sidebar->render($renderTemplateObject);
    }
}