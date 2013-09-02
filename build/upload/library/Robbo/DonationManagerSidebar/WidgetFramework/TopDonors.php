<?php namespace Robbo\DonationManagerSidebar\WidgetFramework;

use Robbo\DonationManagerSidebar\Sidebar\TopDonors as TopDonorsSidebar;

class TopDonors extends Widget {

    protected function getSidebar()
    {
        return new TopDonorsSidebar;
    }
}