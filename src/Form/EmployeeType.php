<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserRoles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
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
                'mapped' => false,
                'required' => !$isEdit,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => '********',
                ],
                'constraints' => $isEdit ? [] : [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Employee' => UserRoles::ROLE_COMPANY_EMPLOYEE->value,
                    'Admin' => UserRoles::ROLE_COMPANY_ADMIN->value,
                ],
                'expanded' => false, // обычный <select>
                'multiple' => false, // только 1 роль
                'label' => 'Role',
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Is Active',
                'required' => false,
                'attr' => ['class' => 'form-checkbox form-control'],
            ]);

        $builder->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                    function ($roles) {
                        return is_array($roles) ? ($roles[0] ?? null) : null;
                    },
                    function ($role) {
                        return $role ? [$role] : [];
                    },
                ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'is_edit' => false,
        ]);
    }
}
