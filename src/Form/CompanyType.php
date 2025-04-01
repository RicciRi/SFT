<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an Email!',
                    ]),
                    new Length([
                        'max' => 250,
                        'maxMessage' => 'Your Company Name should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'required' => true,
                    'class' => 'form-control',
                    'placeholder' => 'Symfony File Transfer',
                ],
            ])
            ->add('contactEmail', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an Email!',
                    ]),
                    new Email([
                        'message' => 'Please, enter valid Email.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your email should be at least {{ limit }} characters',
                        'max' => 250,
                        'maxMessage' => 'Your email should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com',
                ],
            ])
            ->add('address', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your email should be at least {{ limit }} characters',
                        'max' => 250,
                        'maxMessage' => 'Your email should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'USA, New York',
                ],
            ])
            ->add('phone', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your phone number should be at least {{ limit }} characters',
                        'max' => 250,
                        'maxMessage' => 'Your phone number should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '+48 732 581 469',
                ],
            ])
            ->add('website', TextType::class, [
                'constraints' => [
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Your website link should be at least {{ limit }} characters',
                        'max' => 250,
                        'maxMessage' => 'Your website link  should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'sft.com',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
