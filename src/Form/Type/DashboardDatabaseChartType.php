<?php

namespace Okvpn\Bundle\OkvpnGraphWidgetBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;

class DashboardDatabaseChartType extends AbstractType
{
    /**
     * @var array
     */
    protected $senders;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    public function __construct(ManagerRegistry $registry, array $senders = [])
    {
        if (empty($senders)) {
            $senders[] = 'database';
        }

        $this->senders = $senders;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'connection',
                ChoiceType::class,//Entity type not works in widget type due to invalid serialization
                [
                    'label' => 'Database connection',
                    'required' => true,
                    'choices' => $this->getChoices()
                ]
            )
            ->add(
                'sql',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'Sql query'
                ]
            );
    }

    /**
     * @return array
     */
    protected function getChoices()
    {
        $choices = [];
        $qb = $this->registry->getManager()->getRepository(Channel::class)
            ->createQueryBuilder('channel');

        $qb
            ->where(
                $this->senders ? $qb->expr()->in('channel.type', $this->senders) : '1 != 1'
            )
            ->andWhere('channel.enabled = true');

        $channels = $qb->getQuery()->getResult();
        /** @var Channel $channel */
        foreach ($channels as $channel) {
            $choices[$channel->getId()] = $channel->getName();
        }

        return Kernel::MAJOR_VERSION > 2 ? \array_flip($choices) : $choices;
    }
}
