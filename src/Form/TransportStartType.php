<?php

namespace App\Form;

use App\Entity\TransportStart;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportStartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('times', CollectionType::class, ['entry_type' => IntegerType::class, 'allow_add' => true,])
            ->add('transport', EntityType::class, ['class' => \App\Entity\Transport::class])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TransportStart::class,
            'allow_extra_fields' => true
        ]);
    }
}
