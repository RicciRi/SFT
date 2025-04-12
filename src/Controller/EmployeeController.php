<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employees')]
#[IsGranted(new Expression('is_granted("ROLE_COMPANY_ADMIN") or user.isMainAccount()'))]
final class EmployeeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    #[Route('/', name: 'app_employee')]
    public function index()
    {
        $company = $this->getUser()->getCompany();
        $employees = $company->getEmployees();

        return $this->render('employee/index.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/new', name: 'app_employee_new')]
    public function new(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(EmployeeType::class, $user, [
            'is_edit' => false,
        ]);
        $form->remove('isActive');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData());
            $company = $this->getUser()->getCompany();

            $user->setPassword($hashedPassword);
            $user->setCompany($company);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsMainAccount(false);
            $user->setIsVerified(true);
            $user->setIsActive(true);

            $company->addEmployee($user);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'New employee added successfully');

            return $this->redirectToRoute('app_employee');
        }

        return $this->render('employee/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_employee_show')]
    public function show(User $employee)
    {
        if (!$employee) {
            $this->addFlash('error', 'Can\'t find employee.');

            return $this->redirectToRoute('app_employee');
        }

        if ($employee->getCompany() != $this->getUser()->getCompany()) {
            $this->addFlash('error', 'No permission to change this employee.');

            return $this->redirectToRoute('app_employee');
        }

        return $this->render('employee/show.html.twig', [
            'employee' => $employee,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_employee_edit')]
    public function edit(User $employee, Request $request)
    {
        if (!$employee) {
            $this->addFlash('error', 'Can\'t find employee.');

            return $this->redirectToRoute('app_employee');
        }

        if ($employee->getCompany() != $this->getUser()->getCompany()) {
            $this->addFlash('error', 'No permission to change this employee.');

            return $this->redirectToRoute('app_employee');
        }

        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $hashedPassword = $this->userPasswordHasher->hashPassword($employee, $plainPassword);
                $employee->setPassword($hashedPassword);
            }

            $employee->setUpdatedAt(new \DateTime());

            $this->entityManager->flush();

            $this->addFlash('success', 'Employee updated successfully');

            return $this->redirectToRoute('app_employee');
        }

        return $this->render('employee/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_employee_delete')]
    public function delete(User $employee)
    {
        if (!$employee) {
            $this->addFlash('error', 'Can\'t find employee.');

            return $this->redirectToRoute('app_employee');
        }

        if ($employee->getCompany() != $this->getUser()->getCompany()) {
            $this->addFlash('error', 'No permission to change this employee.');

            return $this->redirectToRoute('app_employee');
        }

        if (!$this->getUser()->isMainAccount() && !$this->isGranted('ROLE_COMPANY_ADMIN')) {
            $this->addFlash('error', 'No permission to make this request!!!');

            return $this->redirectToRoute('app_employee');
        }

        $employee->setIsActive(false);
        $this->entityManager->flush();

        $this->addFlash('success', 'Employee deleted successfully!');

        return $this->redirectToRoute('app_employee');
    }
}
