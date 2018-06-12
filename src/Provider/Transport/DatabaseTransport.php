<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Provider\Transport;

use Okvpn\Bundle\GraphWidgetBundle\Form\Type\DatabaseTransportType;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;

class DatabaseTransport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity) {}

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
    public function getSettingsFormType()
    {
        return DatabaseTransportType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'Okvpn\Bundle\GraphWidgetBundle\Entity\DatabaseTransport';
    }
}
