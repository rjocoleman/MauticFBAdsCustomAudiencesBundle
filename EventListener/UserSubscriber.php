<?php

namespace MauticPlugin\MauticFBAdsCustomAudiencesBundle\EventListener;

use Mautic\CoreBundle\Service\FlashBag;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\UserBundle\Event\LoginEvent;
use Mautic\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UserSubscriber implements EventSubscriberInterface
{    
    /**
     * @var TranslatorInterface|Translator
     */
    private $translator;

    /**
     * @var integrationHelper
     */
    private $integrationHelper;

    /**
     * @var FlashBag
     */
    private $flashBag;

    /**
     * TimelineEventLogSubscriber constructor.
     *
     * @param TranslatorInterface    $translator
     * @param ModelFactory           $modelFactory
     */
    public function __construct(
        TranslatorInterface $translator,        
        IntegrationHelper $integrationHelper,
        FlashBag $flashBag
    ) {
        $this->translator             = $translator;        
        $this->integrationHelper      = $integrationHelper;
        $this->flashBag                 = $flashBag;      
    } 

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_LOGIN => ['onUserLogin', 0],            
        ];
    }

    public function onUserLogin(LoginEvent $event){
        $this->checkForNewVersion($event);
        $this->addFlashAboutUnreadNotification($event);
    }

    public function checkForNewVersion(LoginEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
        if (!$integration){
            return;
        }
        $integrationSettings = $integration->getIntegrationSettings();
        if (!$integration || $integrationSettings->getIsPublished() === false) {
            return;
        }        

        $user = $event->getUser();

        $newVersionResponse = $integration->checkForNewVersion();
        if (!empty($newVersionResponse)){
            $message = $this->translator->trans('mautic.integration.FBAds.integration.new_version_description',
                [
                    '%name%'    => $integration->getName(),
                    '%version%' => $newVersionResponse['tag_name'],
                    '%link%'    => $integration::CHANGELOG_URL,
                ]
            );
            $header  = $this->translator->trans(
                'mautic.integration.FBAds.integration.new_version',
                [
                    '%name%' => $integration->getName(),
                ]
            );

            $integration->getNotificationModel()->addNotification(
                $message,
                $integration->getName(),
                false,
                $header,
                'fa-bullhorn',
                null,
                $user
            );
        }
    }

    /**
     *  Check if there is unread notification
     */
    public function addFlashAboutUnreadNotification(LoginEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('FBAdsCustomAudiences');
        if (!$integration){
            return;
        }

        $user = $event->getUser();

        $unreadNotification = $integration->getNotificationModel()->getRepository()->getNotifications($user->getId(), null, false, $integration->getName(), 1);        
        foreach ($unreadNotification as $item){
            $this->flashBag->add(
                $item['header'].': '.$item['message'].' ('.$item['dateAdded']->format('Y-m-d H:i:s').')',
                null,
                Flashbag::LEVEL_WARNING,
                false,                        
            );
        }
    }
}
