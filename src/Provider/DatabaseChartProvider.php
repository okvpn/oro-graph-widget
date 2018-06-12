<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Okvpn\Bundle\GraphWidgetBundle\Services\TransportConnectionFactory;

class DatabaseChartProvider
{
    const MAX_ROWS = 5000;
    const UNDEFINED = 'main';

    /**
     * OX axis
     */
    const X_HINT = 'x';

    /**
     * OY axis
     */
    const Y_HINT = 'y';

    /**
     * In multi line chars this parameters used to groups by
     */
    const LINE_HINT = 'line';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var TransportConnectionFactory
     */
    protected $connectionFactory;

    /**
     * @param ManagerRegistry $registry
     * @param TransportConnectionFactory $connectionFactory
     */
    public function __construct(ManagerRegistry $registry, TransportConnectionFactory $connectionFactory)
    {
        $this->registry = $registry;
        $this->connectionFactory = $connectionFactory;
    }

    /**
     * @param int $transport
     * @param string $sql
     * @return array
     */
    public function getChartData($transport, $sql)
    {
        if (\is_numeric($transport)) {
            $transport = $this->registry->getRepository('OkvpnGraphWidgetBundle:DatabaseTransport')
                ->find($transport);
            if ($transport === null) {
                throw new \InvalidArgumentException("DatabaseTransport#$transport not found in database.");
            }
        }

        $xType = null;
        $result = [];
        $connection = $this->connectionFactory->createDbalConnection($transport);
        $stmt = $connection->executeQuery($sql);
        while ($data = $stmt->fetch()) {
            if (!\array_key_exists(self::Y_HINT, $data) || !\array_key_exists(self::Y_HINT, $data)) {
                throw new \InvalidArgumentException('Attribute "x" and "y" is required in sql query');
            }

            $line = $data[self::LINE_HINT] ?? self::UNDEFINED;
            $x    = $data[self::X_HINT];
            $y    = $data[self::Y_HINT];
            if ($x === null || $y === null) {
                continue;
            }

            $result[$line][] = ['x' => $x, 'y' => $y];
            if ($xType === null) {
                $xType = $this->guessesType($x);
            }

            if (\count($result) > self::MAX_ROWS) {
                break;
            }
        }

        if ($xType === null) {
            $xType = 'string';
        }

        return [$xType, $result];
    }

    protected function guessesType($value)
    {
        if (null === $value) {
            return null;
        }

        if (\is_numeric($value)) {
            return strpos($value, '.') !== false ? 'decimal' : 'integer';
        }

        if (\preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return 'datetime';
        }

        if (\preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return 'date';
        }

        return 'string';
    }
}
