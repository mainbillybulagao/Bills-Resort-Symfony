<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roomName', TextType::class, [
                'label' => 'Room Name'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('price', NumberType::class, [
                'label' => 'Price',
                'input' => 'string',
                'scale' => 2
            ])
            ->add('capacity', NumberType::class, [
                'label' => 'Maximum Guests',
                'html5' => true,
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('image', TextType::class, [
                'label' => 'Image Filename',
                'required' => false
            ])
            ->add('status', TextType::class, [
                'label' => 'Status'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
