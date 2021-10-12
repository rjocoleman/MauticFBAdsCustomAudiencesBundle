<?php

/*
 * @copyright   2017 Trinoco. All rights reserved
 * @author      Trinoco
 *
 * @link        http://trinoco.nl
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Advertising',
    'description' => 'Enables integration with Facebook Ads Custom Audiences Syncing your segments.',
    'version'     => '2.0.0',
    'author'      => 'Trinoco',
    'services' => [
        'events' => [
            'mautic.plugin.fbadsaudience.lead.subscriber' => [
                'class'     => 'MauticPlugin\MauticFBAdsCustomAudiencesBundle\EventListener\LeadListSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'doctrine.orm.entity_manager',
                    'mautic.core.service.flashbag',
                ],
            ],
            'mautic.plugin.fbadsaudience.plugin.subscriber' => [
                'class'     => 'MauticPlugin\MauticFBAdsCustomAudiencesBundle\EventListener\PluginSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                    'doctrine.orm.entity_manager',
                    'mautic.lead.model.lead_segment_service',
                    'mautic.core.service.flashbag',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.fbadscustomaudiences' => [
                'class'     => \MauticPlugin\MauticFBAdsCustomAudiencesBundle\Integration\FBAdsCustomAudiencesIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ],
];
