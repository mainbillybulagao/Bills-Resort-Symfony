<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerBookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Full Name',
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email Address',
            ])

            ->add('contactNumber', TextType::class, [
                'label' => 'Contact Number',
            ])

            ->add('checkIn', DateType::class, [
                'label' => 'Check-in Date',
                'widget' => 'single_text',
            ])

            ->add('checkOut', DateType::class, [
                'label' => 'Check-out Date',
                'widget' => 'single_text',
            ])

            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'roomName',
                'placeholder' => 'Select a Room',
                'label' => 'Choose Your Room',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
