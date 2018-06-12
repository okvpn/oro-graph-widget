<?php

namespace Okvpn\Bundle\GraphWidgetBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ManagerRegistry $registry,
        AuthorizationCheckerInterface $authorizationChecker,
        array $senders = []
    ) {
        if (empty($senders)) {
            $senders[] = 'okvpn_database';
        }

        $this->authorizationChecker = $authorizationChecker;
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
                    'choices' => $this->getChoices(),
                    'constraints' => [
                        new NotBlank()
                    ]
                ]
            )
            ->add(
                'sql',
                TextareaType::class,
                [
                    'required' => true,
                    'label' => 'Sql query',
                    'disabled' => !$this->authorizationChecker->isGranted('okvpn_sql_query'),
                    'constraints' => [
                        new NotBlank()
                    ]
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
