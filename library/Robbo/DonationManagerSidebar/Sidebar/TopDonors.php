<?php namespace Robbo\DonationManagerSidebar\Sidebar;

class TopDonors extends Base {

    public function load()
    {
        $donorModel = \XenForo_Model::create('Merc_DonationManager_Model_Donor');

        $topDonors = $donorModel->prepareDonors($donorModel->getDonors(array(), array(
            'limit' => \XenForo_Application::getOptions()->sidebarTopDonorsAmount,
            'order' => 'amount',
            'direction' => 'desc',
            'join' => \Merc_DonationManager_Model_Donor::FETCH_USER,
        )));

        if ( ! $topDonors)
        {
            return false;
        }

        return compact('topDonors');
    }

    public function getTemplateName()
    {
        return 'robbo_donationsidebar_topdonors';
    }

    public function getName()
    {
        return 'Donation Manager: Top Donors';
    }
}