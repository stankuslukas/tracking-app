<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('date_from', DateType::class, [
            'label' => 'Enter the date',
            'required'   => true,
            'mapped' => false,
            'widget' => 'single_text',
        ])
        ->add('date_to', DateType::class, [
            'label' => 'Enter the date',
            'required'   => true,
            'mapped' => false,
            'widget' => 'single_text',
        ])
        ->add('save', SubmitType::class, ['label' => 'Download CSV report']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
