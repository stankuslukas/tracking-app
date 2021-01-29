<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required'   => true,
                'label' => 'Enter the title',
            ])
            ->add('comment', TextType::class, [
                'required'   => false,
                'label' => 'Enter the comment (optional)',
            ])
            ->add('date', DateType::class, [
                'label' => 'Enter the date',
                'required'   => true,
                'mapped' => false,
                'widget' => 'single_text',
            ])
            ->add('time_spent', IntegerType::class, array(
                'required'   => true,
                'label' => 'How much time you are about to spend?',
                'attr'  => array('min' => '1'),
            ))
            ->add('save', SubmitType::class, ['label' => 'Submit']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
