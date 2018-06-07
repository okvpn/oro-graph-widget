<?php

namespace Okvpn\Bundle\OkvpnGraphWidgetBundle\Form\Type;

use Okvpn\Bundle\OkvpnGraphWidgetBundle\Entity\DatabaseTransport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatabaseTransportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'driver',
                ChoiceType::class,
                [
                    'label' => 'okvpn.graphwidget.databasetransport.driver.label',
                    'required' => true,
                    'choices' => $this->getDriverChoices()
                ]
            )
            ->add(
                'host',
                TextType::class,
                [
                    'label' => 'okvpn.graphwidget.databasetransport.host.label',
                    'required' => true,
                    'constraints' => [new NotNull()]
                ]
            )
            ->add(
                'port',
                IntegerType::class,
                [
                    'label' => 'okvpn.graphwidget.databasetransport.port.label',
                    'required' => true,
                    'constraints' => [new NotNull()]
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'okvpn.graphwidget.databasetransport.name.label',
                    'required' => true,
                    'constraints' => [new NotNull()]
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'okvpn.graphwidget.databasetransport.username.label',
                    'required' => true,
                    'constraints' => [new NotNull()]
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'label' => 'okvpn.graphwidget.databasetransport.password.label',
                    'required' => false,
                ]
            );

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $transport = $event->getData();
        if (\is_array($transport) && \array_key_exists('password', $transport)) {
            $formData = $event->getForm()->getData();
            $password = $transport['password'];
            if ($formData instanceof DatabaseTransport) {
                switch (true) {
                    case empty($password) && !empty($formData->getPassword()):
                        $transport['password'] = $formData->getPassword();
                        break;
                    case \mb_strtolower($password) === 'null':
                        $transport['password'] = null;
                        break;
                }

                $event->setData($transport);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => DatabaseTransport::class
            ]
        );
    }

    /**
     * @return array
     */
    protected function getDriverChoices()
    {
        $choices = [
            'pdo_pgsql' => 'PostgresSQL',
            'pdo_mysql' => 'Mysql'
        ];

        return Kernel::MAJOR_VERSION > 2 ? \array_flip($choices) : $choices;
    }
}
