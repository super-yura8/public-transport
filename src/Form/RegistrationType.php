<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $regexConstraintsOptions = [
            'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,30}$/',
            'message' => 'Your password must consist from 8 to
             30 characters and has at least one letter and one number'
        ];

        $builder
            ->add('email', EmailType::class,)
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new Regex($regexConstraintsOptions),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
