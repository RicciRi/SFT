<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Subscription;
use App\Entity\User;
use App\Enum\SubscriptionPeriod;
use App\Enum\SubscriptionStatus;
use App\Enum\SubscriptionType;
use App\Enum\UserRoles;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $company = new Company();
        $subscription = new Subscription();

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $dateImmutable = new \DateTimeImmutable();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setCreatedAt($dateImmutable);
            $user->setIsMainAccount(true);
            $user->setRoles([UserRoles::ROLE_USER]);

            $companyName = $form->get('companyName')->getData();
            $companyContactEmail = $form->get('contactEmail')->getData();
            $companyAddress = $form->get('address')->getData();
            $companyPhone = $form->get('phone')->getData();
            $companyWebsite = $form->get('website')->getData();

            $company->setMainUser($user);
            $company->setName($companyName);
            $company->setContactEmail($companyContactEmail);
            $company->setAddress($companyAddress);
            $company->setPhone($companyPhone);
            $company->setWebsite($companyWebsite);

            $subscription->setCompany($company);
            $subscription->setType(SubscriptionType::FREE);
            $subscription->setStatus(SubscriptionStatus::ACTIVE);
            $subscription->setPeriod(SubscriptionPeriod::LIFETIME);
            $subscription->setStartDate($dateImmutable);

            $entityManager->persist($user);
            $entityManager->persist($company);
            $entityManager->persist($subscription);

            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('sft.mailer@gmail.com', 'Mail Bot'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig'),
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', 'Erorr to verify email: '.$exception);

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
