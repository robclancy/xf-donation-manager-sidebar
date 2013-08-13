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

/*


if (!XenForo_Visitor::getInstance()->hasPermission('donation', 'viewIndex'))
        {
            return;
        }

        $options = XenForo_Application::get('options');
        $donationModel = $this->_getDonationModel();
        $donorModel = $this->_getDonorModel();
        $goalModel = $this->_getGoalModel();
        $visitor = XenForo_Visitor::getInstance();
        $viewParams = array();

        if ($options->donationSidebar['goal'])
        {
            $goals = $goalModel->getGoals(array(
                'feature' => 1,
                'archived' => 0,
                'hideFuture' => 1
            ));

            $viewParams += array(
                'goals' => $goalModel->prepareGoals($goals),

                'canDonate' => XenForo_Visitor::getInstance()->hasPermission('donation', 'donate'),
            );
        }

        if ($options->donationSidebar['topDonations'] && $visitor->hasPermission('donation', 'viewDonations'))
        {
            $viewParams['topDonations'] = $donationModel->getDonations(array(), array(
                'limit' => $options->sidebarTopDonationsAmount,
                'order' => 'amount',
                'direction' => 'desc',
                'join' => Merc_DonationManager_Model_Donation::FETCH_USER,
            ));
        }

        if ($options->donationSidebar['topDonors'] && $visitor->hasPermission('donation', 'viewDonors'))
        {
            $viewParams['topDonors'] = $donorModel->prepareDonors($donorModel->getDonors(array(), array(
                'limit' => $options->sidebarTopDonorsAmount,
                'order' => 'amount',
                'direction' => 'desc',
                'join' => Merc_DonationManager_Model_Donation::FETCH_USER,
            )));
        }

        if ($viewParams)
        {
            $newContent = $this->_template->create('merc_donation_sidebar', array_merge($viewParams, $this->_template->getParams()));

            if ($options->donationSidebarPosition == 'top')
            {
                $contents = $newContent . $contents;
            }
            else
            {
                $needles = array(
                    'staff' => '<!-- end block: sidebar_online_staff -->',
                    'members' => '<!-- end block: sidebar_online_users -->',
                    'stats' => '<!-- end block: forum_stats -->'
                );

                $needle = $needles[$options->donationSidebarPosition];
                $pos = strpos($contents, $needle);
                if ($pos !== false)
                {
                    $pos += strlen($needle);
                    $contents = substr($contents, 0, $pos) . $newContent . substr($contents, $pos);
                }
            }
        }*/