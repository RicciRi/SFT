<?php

namespace App\Form;

use App\Entity\Payment;
use App\Entity\Subscription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount')
            ->add('currency')
            ->add('paymentMethod')
            ->add('transactionId')
            ->add('paymentDate', null, [
                'widget' => 'single_text',
            ])
            ->add('status')
            ->add('receiptUrl')
            ->add('subscription', EntityType::class, [
                'class' => Subscription::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
