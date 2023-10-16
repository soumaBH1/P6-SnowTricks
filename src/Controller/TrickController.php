<?php

namespace App\Controller;

use DateTime;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\ImageType;
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
use Symfony\Component\HttpFoundation\JsonResponse;

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

        if ($form->isSubmitted() && $form->isValid()) {
            //on recupere les images transmises
            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                //générer un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                //copier le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                //on stocke l'image dans la BDD
                $img = new Image();
                $img->setName($fichier);
                $img->setPath($fichier);
                $trick->addImage($img);
            }
            if (!$trick->getID()) { //si l'trick n'a pas d'identifiant donc il s'agit de création
                $trick->setCreatedAt(new \DateTimeImmutable());
            }
            $manager->persist($trick);
            $manager->flush();
            //afficher l trick crée
            return $this->redirectToRoute('trick_show', ['id' => $trick->getId(), 'trick' => $trick]);
        }
        return $this->render('trick/create.html.twig', [
            'formTrick' => $form->createView(),
            'editMode' => $trick->getId() !== null
        ]);
    }
    #[Route('/trick/{id}', name: 'trick_show')]
    public function show(Trick $trick, Request $request, ObjectManager $manager)
    {

        //ajouter un commentaire
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on recupere les images transmises
            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                //générer un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                //copier le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                //on stocke l'image dans la BDD
                $img = new Image();
                $img->setName($fichier);
                $img->setPath($fichier);
                $trick->addImage($img);
            }
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setTrick($trick);
            //dd($comment);
            $manager->persist($comment); //enregistrer le comment 
            $manager->flush(); //envoyer dans la BDD

            return $this->redirectToRoute('trick_show', ['id' => $trick->getId(), 'trick' => $trick]);
        }
        return $this->render('trick/show.html.twig', ['trick' => $trick, 'commentForm' => $form->createView()]);
    }
    #[Route('/trick/{id}/delete', name: 'trick_delete')]
    public function deleteTrick(trick $trick, Request $request, ObjectManager $manager)
    {

        $manager->remove($trick);
        $manager->flush();

        return $this->redirectToRoute("trick");
    }

    #[Route("/supprime/image/{id}", name: "trick_delete_image", methods: ["DELETE"])]

    public function deleteImage(Image $image, Request $request, ObjectManager $manager)
    {
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // On récupère le nom de l'image
            $nom = $image->getName();
            // On supprime le fichier
            unlink($this->getParameter('images_directory') . '/' . $nom);

            // On supprime l'entrée de la base
            $manager->remove($image);
            $manager->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}