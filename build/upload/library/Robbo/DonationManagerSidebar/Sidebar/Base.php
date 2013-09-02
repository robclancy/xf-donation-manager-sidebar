<?php namespace Robbo\DonationManagerSidebar\Sidebar;

abstract class Base {

    protected $viewParams = array();

    public function preload()
    {
        $this->viewParams = $this->load();

        return $this->viewParams === false ? false : true;
    }

    public function render(\XenForo_Template_Abstract $template)
    {
        return $template->create($this->getTemplateName(), array_merge($template->getParams(), $this->viewParams))->render();
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Loads data and returns an array to use as template params.
     * @return array
     */
    abstract protected function load();

    /**
     * Template to inject into whatever we are using.
     * @return string
     */
    abstract public function getTemplateName();

    abstract public function getName();
}
