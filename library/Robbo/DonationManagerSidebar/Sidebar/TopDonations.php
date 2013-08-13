<?php namespace Robbo\DonationManagerSidebar\Sidebar;

class TopDonations extends Base {

    public function load()
    {
        $donationModel = \XenForo_Model::create('Merc_DonationManager_Model_Donation');

        $topDonations = $donationModel->getDonations(array(), array(
            'limit' => \XenForo_Application::getOptions()->sidebarTopDonationsAmount,
            'order' => 'amount',
            'direction' => 'desc',
            'join' => \Merc_DonationManager_Model_Donation::FETCH_USER,
        ));

        if ( ! $topDonations)
        {
            return false;
        }

        return compact('topDonations');
    }

    public function getTemplateName()
    {
        return 'robbo_donationsidebar_topdonations';
    }

    public function getName()
    {
        return 'Donation Manager: Top Donations';
    }
}