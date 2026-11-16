<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminBookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Customer Name',
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email Address',
            ])

            ->add('contactNumber', TextType::class, [
                'label' => 'Contact Number',
            ])

            ->add('checkIn', DateTimeType::class, [
                'label' => 'Check-in Date',
                'widget' => 'single_text',
            ])

            ->add('checkOut', DateTimeType::class, [
                'label' => 'Check-out Date',
                'widget' => 'single_text',
            ])

            ->add('status', ChoiceType::class, [
                'label' => 'Booking Status',
                'choices' => [
                    'Pending' => 'Pending',
                    'Confirmed' => 'Confirmed',
                    'Rejected' => 'Rejected',
                ],
            ])

            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'roomName',
                'label' => 'Room',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
