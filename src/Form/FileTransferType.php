<?php

namespace App\Form;

use App\Entity\FileTransfer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class FileTransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipientEmail', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                    new NotBlank([
                        'message' => 'Please enter an email address.',
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Email must be at least {{ limit }} characters long.',
                        'max' => 255,
                        'maxMessage' => 'Email cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('subject', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the subject.',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Subject must be at least {{ limit }} characters.',
                        'max' => 255,
                        'maxMessage' => 'Subject cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a message.',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Message must be at least {{ limit }} characters.',
                        'max' => 4096,
                        'maxMessage' => 'Message cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('expirationAt', null, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FileTransfer::class,
        ]);
    }
}
