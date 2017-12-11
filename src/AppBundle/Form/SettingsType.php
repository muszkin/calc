<?php

namespace AppBundle\Form;

use AppBundle\AppBundle;
use AppBundle\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['attributes']["attribute.new"] = "new";

        $builder
            ->add('shop',HiddenType::class,[
                'data' => $options['shop'],
                'attr' => [
                    'class' => 'hidden'
                ]
            ])
            ->add('attribute_id',ChoiceType::class,[
                'choices' =>
                    $options['attributes']
                ,
                'label' => 'attribute.label',
                'attr' => [
                    'class' => 'input-group',
                ],

            ])
            ->add('new_attribute',TextType::class,[
                'label' => 'attribute.new.placeholder',
                'attr' => [
                    'class' => 'input-group',
                    'novalidate' => 'novalidate'
                ]
            ])
            ->add('dtext',TextareaType::class,[
                'label' => 'text.label',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'text.default',
                    'height' => '5'
                ]
            ])
            ->add('type_desc',TextType::class,[
                'label' => 'type_desc.select',
                'attr' => [
                    'class' => 'input-group'
                ],
            ])
	        ->add('type2',TextType::class,[
                'label' => 'type2',
                'attr' => [
                    'class' => 'input-group'
                ],
            ])
            ->add('active',CheckboxType::class,[
                'label' => 'active.select',
                'attr' => [
                    'class' => 'input-group',
                ],
            ])
            ->add('button',SubmitType::class,[
                'label' => "form.save",
                'attr' => [
                    'class' => 'btn btn-info input-group',
                ],
            ])
            ->getForm()
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Settings::class,
            'attributes' => null,
            'shop' => null,
            'translation_domain' => 'AppBundle',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_settings';
    }



}
