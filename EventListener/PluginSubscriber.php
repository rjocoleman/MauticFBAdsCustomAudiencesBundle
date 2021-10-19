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

use Doctrine\ORM\EntityManager;
use MauticPlugin\MauticFBAdsCustomAudiencesBundle\Helper\FbAdsApiHelper;
use Mautic\LeadBundle\Segment\ContactSegmentService;
use Mautic\PluginBundle\Event\PluginIntegrationEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\PluginEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\CoreBundle\Service\FlashBag;

/**
 * Class PluginSubscriber.
 */
class PluginSubscriber implements EventSubscriberInterface
{

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
    * CampaignSubscriber constructor.
    *
    * @param IntegrationHelper       $integrationHelper
    * @param LoggerInterface          $logger
    */
    public function __construct(
        IntegrationHelper $integrationHelper,
        LoggerInterface $logger,
        EntityManager $entityManager,
        ContactSegmentService $leadSegmentService,
        Flashbag $flashBag
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->logger = $logger;
        $this->em     = $entityManager;
        $this->leadSegmentService = $leadSegmentService;
        $this->flashBag                 = $flashBag;
    }  

    /**
    * @return array
    */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PLUGIN_ON_INTEGRATION_CONFIG_SAVE => ['onIntegrationConfigSave', 0],
        ];
    }

    public function onIntegrationConfigSave(PluginIntegrationEvent $event) {
        if ($event->getIntegrationName() == 'FBAdsCustomAudiences') {
            //$integration = $event->getIntegration();
            $changes = $event->getEntity()->getChanges();

            if (isset($changes['isPublished'])) {
                try {
                    $integration = $event->getIntegration();
                    $api = FbAdsApiHelper::init($integration);

                    if ($api) {
                        $lists = $this->em->getRepository('MauticLeadBundle:LeadList')->getLists();

                        if ($changes['isPublished'][1] == 0) {
                            foreach ($lists as $list) {
                                FbAdsApiHelper::deleteList($list['name']);
                            }
                        } else {
                            foreach ($lists as $list) {
                                $listEntity = $this->em->getRepository('MauticLeadBundle:LeadList')->getEntity($list['id']);
                                $audience = FbAdsApiHelper::addList($listEntity);
                                $leads = $this->leadSegmentService->getNewLeadListLeads($listEntity, [], null);

                                $users = array();

                                foreach ($leads as $lead) {
                                    if (!empty($lead['email'])){
                                        $users[] = $lead['email'];
                                    }
                                }
              
                                if (!empty($users)){
                                    FbAdsApiHelper::addUsers($audience, $users);
                                }
                            }
                        }
                    }
                } catch (\Exception $e){
                    $entity = $event->getEntity();
                    $entity->setIsPublished(false);
                    $event->setEntity($entity);

                    $this->flashBag->add(
                        'mautic.integration.FBAds.integration.plugin_activation_failed_flash',
                        [
                            '%name%' => $event->getIntegrationName(),
                            '%reason%' => $e->getMessage(),
                        ]
                    );                  

                    $this->logger->warning($event->getIntegrationName().": Facebook authorization failed: ". $e->getMessage()); 
                }
            }
        }
    }
}