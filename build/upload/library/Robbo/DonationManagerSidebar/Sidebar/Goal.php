<?php namespace Robbo\DonationManagerSidebar\Sidebar;

class Goal extends Base {

    public function load()
    {
        $goalModel = \XenForo_Model::create('Merc_DonationManager_Model_Goal');

        $goals = $goalModel->prepareGoals($goalModel->getGoals(array(
            'feature' => 1,
            'archived' => 0,
            'hideFuture' => 1
        )));

        if ( ! $goals)
        {
            return false;
        }

        return array(
            'goals' => $goalModel->prepareGoals($goalModel->getGoals(array(
                'feature' => 1,
                'archived' => 0,
                'hideFuture' => 1
            ))),
            'canDonate' => \XenForo_Visitor::getInstance()->hasPermission('donation', 'donate')
        );
    }

    public function getTemplateName()
    {
        return 'robbo_donationsidebar_goal';
    }

    public function getName()
    {
        return 'Donation Manager: Featured Goals';
    }
}