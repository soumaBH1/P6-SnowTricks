<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/user/edition/{id}', name: 'user.edit')]
    public function edit(User $user, Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {

        //Si l'utilisateur n'est pas connecté --> redirectio vers la page de connection
        if (!$this->getUser()) {
            return $this->redirectToRoute('security_login');
        }

        //Si l'utilisateur veut changer un aure compte --> redirectio vers la page home
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()->getPassword())) {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Les informations de votre compte ont bien été modifiées'
                );
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect!');
            }
        }
        return $this->render('user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/user/edition-mot-de-passe/{id}', 'user_edit_password')]
    public function editPassword(
        User $choosenUser,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
    ): Response {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($choosenUser, $form->getData()['password'])) {
                //$choosenUser->setUpdatedAt(new \DateTimeImmutable());
                $hash = $hasher->hashPassword($choosenUser,  $form->getData()['newPassword']);
                $choosenUser->setPassword($hash);

                $this->addFlash(
                    'success',
                    'Le mot de passe a été modifié.'
                );

                $manager->persist($choosenUser);
                $manager->flush();

                return $this->redirectToRoute('home');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}