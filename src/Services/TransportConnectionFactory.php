<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Services;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Logging\SQLLogger;
use Okvpn\Bundle\GraphWidgetBundle\Entity\DatabaseTransport;

class TransportConnectionFactory
{
    /**
     * @var SQLLogger
     */
    protected $logger;

    /**
     * @var ConnectionFactory
     */
    protected $connectionFactory;

    public function __construct(
        ConnectionFactory $connectionFactory,
        SQLLogger $logger = null
    ) {
        $this->logger = $logger;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param DatabaseTransport $transport
     * @return \Doctrine\DBAL\Connection
     */
    public function createDbalConnection(DatabaseTransport $transport)
    {
        $configuration = new Configuration();
        if ($this->logger) {
            $configuration->setSQLLogger($this->logger);
        }

        $connection = [
            'driver' => $transport->getDriver(),
            'port' => $transport->getPort(),
            'host' => $transport->getHost(),
            'password' => $transport->getPassword(),
            'user' => $transport->getUsername(),
            'dbname' => $transport->getName()
        ];

        return $this->connectionFactory->createConnection($connection, $configuration);
    }
}
