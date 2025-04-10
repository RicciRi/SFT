<?php

// src/Controller/SettingsController.php

namespace App\Controller;

use App\Form\AccountSettingsType;
use App\Form\CompanyType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/settings')]
#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class SettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/general', name: 'app_settings_general')]
    public function general(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AccountSettingsType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->render('settings/general.html.twig', [
                'form' => $form,
                'user' => $user,
                'active_tab' => 'general',
                'edit' => true,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $existingUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser !== $user) {
                $this->addFlash('error', 'Email already in use! Try another one!');

                return $this->redirectToRoute('app_settings_general');
            }

            $user->setEmail($email);

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Account updated successfully.');

            return $this->redirectToRoute('app_settings_general');
        }

        return $this->render('settings/general.html.twig', [
            'form' => $form,
            'user' => $user,
            'active_tab' => 'general',
            'edit' => $request->query->getBoolean('edit', false),
        ]);
    }

    #[Route('/company', name: 'app_settings_company')]
    public function company(Request $request): Response
    {
        $company = $this->getUser()->getCompany();

        if (!$this->isGranted('ROLE_COMPANY_ADMIN') && !$this->getUser()->isMainAccount()) {
            $form = null;
        } else {
            $form = $this->createForm(CompanyType::class, $company);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->entityManager->flush();

                $this->addFlash('success', 'Company info changed successfully!');

                return $this->redirectToRoute('app_settings_company');
            }
        }

        return $this->render('settings/company.html.twig', [
            'form' => $form,
            'company' => $company,
            'active_tab' => 'company',
            'edit' => $request->query->getBoolean('edit', false),
        ]);
    }

    #[Route('/subscription', name: 'app_settings_subscription')]
    #[IsGranted(new Expression('is_granted("ROLE_COMPANY_ADMIN") or user.isMainAccount()'))]
    public function subscription(): Response
    {
        $subscription = $this->getUser()->getCompany()->getActiveSubscription();

        return $this->render('settings/subscription.html.twig', [
            'subscription' => $subscription,
            'active_tab' => 'subscription',
        ]);
    }

    //    #[Route('/payment', name: 'app_settings_payment')]
    //    #[IsGranted(new Expression('is_granted("ROLE_COMPANY_ADMIN") or user.isMainAccount()'))]
    //    public function payment(Request $request): Response
    //    {
    //        $payment = $this->getUser()->getCompany()->getActiveSubscription()->
    //
    //        return $this->render('settings/payment.html.twig', [
    //            'payment' => $payment,
    //            'active_tab' => 'payment',
    //            'edit' => $request->query->getBoolean('edit', false),
    //        ]);
    //    }
}
