<?php

namespace App\Form;

use App\Entity\ProjectHours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HourModType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timestamp_start', DateTimeType::class, [
                'widget' => 'single_text',
                'with_seconds' => false,
                'label' => 'Starting time',
                'label_attr' => [
                    'class' => 'h3'
                ]
            ])
            ->add('timestamp_end', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Ending time',
                'label_attr' => ['class' => 'h3 mt-4'],
                'attr' => ['class' => 'mb-5']
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectHours::class,
        ]);
    }
}
