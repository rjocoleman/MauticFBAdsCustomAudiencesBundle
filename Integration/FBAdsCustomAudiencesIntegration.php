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

}
