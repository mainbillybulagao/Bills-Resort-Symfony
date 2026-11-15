<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Full Name',
            ])

            ->add('email', EmailType::class)

            ->add('roles', ChoiceType::class, [
                'label' => 'User Role',
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Staff' => 'ROLE_STAFF',
                    'Customer' => 'ROLE_CUSTOMER',
                ],
                'multiple' => true,
                'expanded' => false,
            ])

            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Active' => 'Active',
                    'Inactive' => 'Inactive',
                ],
            ])

            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => !$options['is_edit'],
                'label' => $options['is_edit']
                    ? 'New Password (leave blank to keep current password)'
                    : 'Password',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
