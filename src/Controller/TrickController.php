<?php

namespace App\Controller;

use DateTime;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\TrickType;
use App\Form\CommentType;
use App\Repository\TrickRepository;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitTypeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    #[Route('/trick', name: 'trick')]
    public function index(TrickRepository $repo): Response
    {
        // $repo = $this->getDoctrine()->getRepository(Trick::class);
        $tricks = $repo->findAll();
        return $this->render('trick/index.html.twig', [
            'controller_name' => 'TrickController',
            'tricks' => $tricks
        ]);
    }


    #[Route('/', name: 'home')]
    public function home(TrickRepository $repo): Response
    {
        $tricks = $repo->findAll();
        return $this->render('trick/home.html.twig', [
            'controller_name' => 'TrickController',
            'tricks' => $tricks
        ]);
    }

    #[Route('/trick/new', name: 'trick_create')]
    #[Route('/trick/{id}/edit}', name: 'trick_edit')]
    public function form(Trick $trick = null, Request $request, ObjectManager $manager)
    {
        //créer et MAJ
        if ($trick == null) {
            $trick = new Trick();
        }

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request); //analyser la requete http pour analyser si l'ont soumis ou envoie la requete
        dump($trick);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$trick->getID()) { //si l'trick n'a pas d'identifiant donc il s'agit de création
                $trick->setCreatedAt(new \DateTimeImmutable());
            }
            $manager->persist($trick);
            $manager->flush();

            //afficher l trick crée
            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }
        return $this->render('trick/create.html.twig', [
            'formTrick' => $form->createView(),
            'editMode' => $trick->getId() !== null
        ]);
    }
    #[Route('/trick/{id}', name: 'trick_show')]
    public function show(Trick $trick, Request $request, ObjectManager $manager)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setTrick($trick);
            //dd($comment);
            $manager->persist($comment); //enregistrer le comment 
            $manager->flush(); //envoyer dans la BDD

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId()]);
        }
        return $this->render('trick/show.html.twig', ['trick' => $trick, 'commentForm' => $form->createView()]);
    }
}
