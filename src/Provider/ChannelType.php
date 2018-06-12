<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class ChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'okvpn.graphwidget.databasetransport.entity_label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/okvpngraphwidget/images/database.png';
    }
}
