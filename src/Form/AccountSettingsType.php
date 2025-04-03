<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // USER FORM
            ->add('email', EmailType::class, [
                'constraints' => [
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
                    'required' => true,
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com',
                ],

                'label_attr' => ['class' => 'settings-label'],

            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'invalid_message' => 'Passwords must match.',
                'first_options' => [
                    'label' => 'New Password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                        'placeholder' => '********',
                    ],
                    'label_attr' => ['class' => 'settings-label'],

                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'form-control',
                        'placeholder' => '********',
                    ],
                    'label_attr' => ['class' => 'settings-label'],

                ],
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your name!',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Name field should be at least {{ limit }} characters',
                        'max' => 255,
                        'maxMessage' => 'Your name should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Vito',
                ],
                'label' => 'First Name',
                'label_attr' => ['class' => 'settings-label'],

            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your Surname!',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Surname field should be at least {{ limit }} characters',
                        'max' => 255,
                        'maxMessage' => 'Your Surname should be max {{ limit }} characters',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Scaletta',
                ],
                'label' => 'Last Name',
                'label_attr' => ['class' => 'settings-label'],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
