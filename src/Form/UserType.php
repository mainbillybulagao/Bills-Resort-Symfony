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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $passwordConstraints = [];

        if (!$options['is_edit']) {
            $passwordConstraints = [
                new NotBlank(
                    message: 'Please enter a password.'
                ),
                new Length(
                    min: 8,
                    minMessage: 'Password must contain at least 8 characters.'
                ),
            ];
        }

        $builder
            ->add('name', TextType::class, [
                'label' => 'Full Name',
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter the full name.'
                    ),
                    new Length(
                        max: 100
                    ),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter an email address.'
                    ),
                    new Email(
                        message: 'Please enter a valid email address.'
                    ),
                ],
            ])

            ->add('roles', ChoiceType::class, [
                'label' => 'User Role',
                'choices' => [
                    'Administrator' => 'ROLE_ADMIN',
                    'Staff' => 'ROLE_STAFF',
                    'Customer' => 'ROLE_CUSTOMER',
                ],
                'multiple' => true,
                'expanded' => false,
                'constraints' => [
                    new NotBlank(
                        message: 'Please select a user role.'
                    ),
                ],
            ])

            ->add('status', ChoiceType::class, [
                'label' => 'Account Status',
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
                'constraints' => $passwordConstraints,
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
