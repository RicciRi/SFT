<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // USER FORM
            ->add('email', EmailType::class, [
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
                    'required' => true,
                    'class' => 'form-control',
                    'placeholder' => 'email@example.com',
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => '********',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
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
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])

            // COMPANY FORM
            ->add('companyName', TextType::class, [
                'mapped' => false,
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
                'mapped' => false,
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
                'mapped' => false,
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
                'mapped' => false,
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
                'mapped' => false,
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
