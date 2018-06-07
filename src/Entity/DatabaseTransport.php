<?php

namespace Okvpn\Bundle\OkvpnGraphWidgetBundle\Entity;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class DatabaseTransport extends Transport
{
    /**
     * @var ParameterBag
     */
    private $settingsBag;

    /**
     * @var string
     *
     * @ORM\Column(name="okvpn_database_username", type="string", length=100, nullable=true)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="okvpn_database_password", type="crypted_string", length=1024, nullable=true)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="okvpn_database_host", type="string", length=100, nullable=true)
     */
    protected $host;

    /**
     * @var integer
     *
     * @ORM\Column(name="okvpn_database_port", type="integer", nullable=true)
     */
    protected $port;

    /**
     * @var string
     *
     * @ORM\Column(name="okvpn_database_driver", type="string", length=64, nullable=true)
     */
    protected $driver;

    /**
     * @var string
     *
     * @ORM\Column(name="okvpn_database_name", type="string", length=64, nullable=true)
     */
    protected $name;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return DatabaseTransport
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return empty($this->password) ? null : $this->password;
    }

    /**
     * @param string $password
     * @return DatabaseTransport
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return DatabaseTransport
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     * @return DatabaseTransport
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     * @return DatabaseTransport
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DatabaseTransport
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if ($this->settingsBag === null) {
            $this->settingsBag = new ParameterBag(
                [
                    'host'   => $this->host,
                    'password' => $this->password,
                    'username' => $this->username,
                    'driver' => $this->driver,
                    'name' => $this->name,
                    'port' => $this->port,
                ]
            );
        }

        return $this->settingsBag;
    }
}
