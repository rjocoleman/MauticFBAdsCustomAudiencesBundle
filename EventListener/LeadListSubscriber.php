<?php

/*
 * @copyright   2017 Trinoco. All rights reserved
 * @author      Trinoco
 *
 * @link        http://trinoco.nl
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticFBAdsCustomAudiencesBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\ORM\EntityManager;
use FacebookAds\Object\CustomAudience;
use Mautic\LeadBundle\Event\LeadListEvent;
use Mautic\LeadBundle\Event\ListChangeEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\LeadBundle\LeadEvents;
use Mautic\CoreBundle\Service\FlashBag;

use MauticPlugin\MauticFBAdsCustomAudiencesBundle\Helper\FbAdsApiHelper;

/**
 * Class LeadListsSubscriber.
 */
class LeadListSubscriber implements EventSubscriberInterface
{
    /**
    * @var \FacebookAds\Api
    */
    protected $fbAPI;

    /**
    * @var IntegrationHelper
    */
    protected $integrationHelper;

    /**
    * @var \Doctrine\ORM\EntityManager
    */
    protected $em;

    /**
     * @var FlashBag
     */
    private $flashBag;

    /**
    * LeadSubscriber constructor.
    */
    public function __construct(IntegrationHelper $integrationHelper, EntityManager $entityManager, Flashbag $flashBag)
    {
        $this->integrationHelper = $integrationHelper;
        $this->em     = $entityManager;
        $this->flashBag                 = $flashBag;
        $this->fbAPI = $this->fbApiInit();
    }

    /**
    * @return array
    */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LIST_POST_SAVE => ['onLeadListPostSave', 0],
            LeadEvents::LIST_POST_DELETE => ['onLeadListPostDelete', 0],
            LeadEvents::LEAD_LIST_BATCH_CHANGE => ['onLeadListBatchChange', 0],
            LeadEvents::LEAD_LIST_CHANGE       => ['onLeadListChange', 0],
        ];
    }

    /**
    * Initializes the Facebook Ads API.
    *
    * @return bool|\FacebookAds\Api|null
    */
    protected function fbApiInit() {
        $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
        if (!$integration || !$integration->getIntegrationSettings()->isPublished()) {
            return FALSE;
        }

        try {    
            return FbAdsApiHelper::init($integration);
        } catch (\Exception $e){
            $this->flashBag->add(
                'mautic.integration.FBAds.integration.plugin_runtime_error_flash',
                [
                    '%name%' => $$integration->getName(),
                    '%reason%' => $e->getMessage(),
                ]
            );

            return FALSE;
        }  
    }

    /**
    * Add list to facebook.
    *
    * @param ListChangeEvent $event
    */
    public function onLeadListPostSave(LeadListEvent $event) {
        if (!$this->fbAPI) {
            return;
        }

        try {
            $list = $event->getList();
            FbAdsApiHelper::addList($list);
        } catch (\Exception $e){
            $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
            $this->flashBag->add(
                'mautic.integration.FBAds.integration.plugin_runtime_error_flash',
                [
                    '%name%' => $integration->getName(),
                    '%reason%' => $e->getMessage(),
                ]
            );
        }  
    }

    /**
    * Delete list from facebook.
    *
    * @param ListChangeEvent $event
    */
    public function onLeadListPostDelete(LeadListEvent $event)
    {
        if (!$this->fbAPI) {
            return;
        }

        try {
            $list = $event->getList();
            FbAdsApiHelper::deleteList($list->getName());
        } catch (\Exception $e){
            $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
            $this->flashBag->add(
                'mautic.integration.FBAds.integration.plugin_runtime_error_flash',
                [
                    '%name%' => $integration->getName(),
                    '%reason%' => $e->getMessage(),
                ]
            );
        }  
    }

    /**
    * Add/remove leads from facebook based on batch lead list changes.
    *
    * @param ListChangeEvent $event
    */
    public function onLeadListBatchChange(ListChangeEvent $event)
    {
        if (!$this->fbAPI) {
            return;
        }

        try {
            if ($audience = FbAdsApiHelper::getFBAudience($event->getList()->getName())) {
                $users = array();
                foreach ($event->getLeads() as $lead_id) {
                    $lead = $this->em->getRepository('MauticLeadBundle:Lead')->getEntity($lead_id);

                    if ($lead->getEmail()) {
                        $users[] = $lead->getEmail();
                    }
                }

                if ($event->wasAdded()) {
                    if (!empty($users)){
                        FbAdsApiHelper::addUsers($audience, $users);
                    }
                } else {
                    if (!empty($users)){
                        FbAdsApiHelper::removeUsers($audience, $users);
                    }
                }
            }

            // Save memory with batch processing
            unset($event, $users, $audience);
        } catch (\Exception $e){
            $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
            $this->flashBag->add(
                'mautic.integration.FBAds.integration.plugin_runtime_error_flash',
                [
                    '%name%' => $integration->getName(),
                    '%reason%' => $e->getMessage(),
                ]
            );
        }  
    }

    /**
    * Add/remove leads from campaigns based on lead list changes.
    *
    * @param ListChangeEvent $event
    */
    public function onLeadListChange(ListChangeEvent $event)
    {
        if (!$this->fbAPI) {
            return;
        }

        try {
            /** @var \Mautic\LeadBundle\Entity\Lead $lead */
            $lead   = $event->getLead();

            if ($audience = FbAdsApiHelper::getFBAudience($event->getList()->getName())) {
                $users = array(
                    $lead->getEmail()
                );

                if ($event->wasAdded()) {
                    if (!empty($users)){
                        FbAdsApiHelper::addUsers($audience, $users);
                    }
                } else {
                    if (!empty($users)){
                        FbAdsApiHelper::removeUsers($audience, $users);
                    }
                }
            }
        } catch (\Exception $e){
            $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
            $this->flashBag->add(
                'mautic.integration.FBAds.integration.plugin_runtime_error_flash',
                [
                    '%name%' => $integration->getName(),
                    '%reason%' => $e->getMessage(),
                ]
            );
        }  
    }
}
