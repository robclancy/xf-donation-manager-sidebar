<?php namespace Robbo\DonationManagerSidebar\WidgetFramework;

use Robbo\DonationManagerSidebar\Sidebar\Goal as GoalSidebar;

class Goal extends Widget {

    protected function getSidebar()
    {
        return new GoalSidebar;
    }
}