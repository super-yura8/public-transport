<?php

namespace App\Form;

use App\Entity\TransportRun;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TransportRunType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('arrivalTime', IntegerType::class)
            ->add('transportStop', EntityType::class, ['class' => \App\Entity\TransportStop::class])
            ->add('transport', EntityType::class, ['class' => \App\Entity\Transport::class])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransportRun::class,
        ]);
    }
}
