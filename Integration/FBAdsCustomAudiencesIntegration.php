<?php

/*
 * @copyright   2017 Trinoco. All rights reserved
 * @author      Trinoco
 *
 * @link        http://trinoco.nl
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticFBAdsCustomAudiencesBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class FBAdsCustomAudiencesIntegration.
 */

class FBAdsCustomAudiencesIntegration extends AbstractIntegration
{ 
  const VERSION_URL              = 'https://api.github.com/repos/d-code-ltd/MauticFBAdsCustomAudiencesBundle/releases';
  const CHANGELOG_URL            = 'https://www.leadengine.hu/en/downloads/custom-audiences-facebook-ads-mautic-plugin/#elementor-action%3Aaction%3Dpopup%3Aopen%26settings%3DeyJpZCI6NTQ0NywidG9nZ2xlIjpmYWxzZX0%3D';

  public function getName()
  {
    return 'FBAdsCustomAudiences';
  }

  public function getIcon()
  {
      return 'plugins/MauticFBAdsCustomAudiencesBundle/Assets/img/custom-audience-icon-78.svg';
  }

  /**
   * Name to display for the integration. e.g. iContact  Uses value of getName() by default.
   *
   * @return string
   */
  public function getDisplayName()
  {
    return 'Facebook Ads Custom Audiences Sync';
  }

  /**
   * Return's description of the the plugin.
   *
   * @return string
   */
  public function getDescription()
    {
        return 'The plugin enables integration with Facebook Ads and allows syncing its <strong><a href="https://en-gb.facebook.com/business/help/341425252616329?locale=en_GB" target="_blank">Custom Audiences</a></strong> with <strong>Mautic segments</strong>.
                <br /><br />More details: <ul><li><a href="https://docs.google.com/document/d/1xKvPwJnyv8B-dGzerdI8rgnYea2l1tOoLO9Rlw54ABk/edit" target="_blank">en</a></li><li><a href="https://docs.google.com/document/d/1HbsD1BlFXX__HZ94eqcZbp5Ye-we2A_K8AFd8-iLn5o/edit" target="_blank">hu</a></li></ul><p align="right">version: <strong><a href="'.self::CHANGELOG_URL.'" target="_blank">'.$this->settings->getPlugin()->getVersion().'</a></strong></p>';
    }

  /**
   * Return's authentication method such as oauth2, oauth1a, key, etc.
   *
   * @return string
   */
  public function getAuthenticationType()
  {
    // Just use none for now and I'll build in "basic" later
    return 'none';
  }

  /**
   * Get the array key for clientId.
   *
   * @return string
   */
  public function getClientIdKey()
  {
    return 'app_id';
  }

  /**
   * Get the array key for client secret.
   *
   * @return string
   */
  public function getClientSecretKey()
  {
    return 'app_secret';
  }

  /**
   * Get the array key for the auth token.
   *
   * @return string
   */
  public function getAuthTokenKey()
  {
    return 'access_token';
  }

  /**
   * Get the array key for client secret.
   *
   * @return string
   */
  public function getAdAccountIdKey() {
    return 'ad_account_id';
  }

  /**
   * Get the array key for feature setting customer_file_source.
   *
   * @return string
   */
  public function getCustomerFileSourceKey() {
    return 'customer_file_source';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredKeyFields()
  {
    return [
      'app_id'      => 'mautic.integration.keyfield.FBAds.app_id',
      'app_secret'      => 'mautic.integration.keyfield.FBAds.app_secret',
      'access_token'    => 'mautic.integration.keyfield.FBAds.access_token',
      'ad_account_id' => 'mautic.integration.keyfield.FBAds.ad_account_id',
    ];
  }

  /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ($formArea == 'features') {
   
             $builder->add(
                  'customer_file_source',
                  ChoiceType::class,
                  [
                      'label'    => 'mautic.integration.FBAds.customer_file_source.label',
                      'choices'  => [
                        'mautic.integration.FBAds.customer_file_source.USER_PROVIDED_ONLY'              => 'USER_PROVIDED_ONLY',
                        'mautic.integration.FBAds.customer_file_source.PARTNER_PROVIDED_ONLY'           => 'PARTNER_PROVIDED_ONLY',
                        'mautic.integration.FBAds.customer_file_source.BOTH_USER_AND_PARTNER_PROVIDED'  => 'BOTH_USER_AND_PARTNER_PROVIDED',
                      ],
                      'required' => true,
                      'attr'     => [
                          'class' => 'form-control',
                          'tooltip' => 'mautic.integration.FBAds.customer_file_source.tooltip',                                               
                      ],
                      'expanded'    => false,
                      'multiple'    => false,
                      'preferred_choices' => ['BOTH_USER_AND_PARTNER_PROVIDED'], //default behaviour
                      'required'    => true,
                      'placeholder' => '',
                  ]
              );           
        }        
    }

    /**
     * {@inheritdoc}
     
     * @return array|false
     */
    public function checkForNewVersion(){        
        $integrationSettings = $this->getIntegrationSettings();
        $version = $integrationSettings->getPlugin()->getVersion();        
                
        $payload = [
            'per_page' => '1'
        ];
        
        $response = $this->makeRequest(
            self::VERSION_URL, 
            $payload, 
            'GET',
            [
                'ignore_event_dispatch' => true,
            ]
        );
      
        if (!empty($response)){
          $response = $response[0];
        }

        if (isset($response['tag_name']) && version_compare($version, $response['tag_name'], '<')){            
            return $response;
        } else {
            return false;
        }
    }
}
