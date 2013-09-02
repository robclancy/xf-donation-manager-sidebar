<?php namespace Robbo\DonationManagerSidebar\WidgetFramework;

use Robbo\DonationManagerSidebar\Sidebar\TopDonations as TopDonationsSidebar;

class TopDonations extends Widget {

    protected function getSidebar()
    {
        return new TopDonationsSidebar;
    }
}