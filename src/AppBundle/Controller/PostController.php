<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{

    /**
     * @param $id
     * @Route("/post/{id}",
     *          name="post_details",
     *          requirements={"id":"\d+"}
     * )
     * @return Response
     */
    public function detailsAction($id){

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Post");

        $post = $repository->find($id);

        $user = $this->getUser();
        $roles = isset($user) ? $user->getRoles() : [];

        $isMe = false;
        $isConnected = false;
        $status = 1;
        // si je suis connecté
        if (in_array('ROLE_AUTHOR', $roles)) {
            if($post->getAuthor()->getId() == $user->getId()) {
                $isMe = true;
                $status = 3;
            }
            $isConnected = true;
            // j'affiche le formulaire de commentaire
        }

        if(! $post){
            throw new NotFoundHttpException("post introuvable");
        }

        return $this->render("post/details.html.twig", [
            "post" => $post,
            "answerList" => $post->getAnswers(),
            'status' => $status,
            'isMe' => $isMe,
            'isConnected' => $isConnected
        ]);
    }

    /**
     * @Route("/post-par-annee/{year}", name="post_by_year", requirements={"year":"\d{4}"})
     * @param $year
     * @return Response
     */
    public function postByYearAction(Request $request, $year) {
        $postRepository = $this->getDoctrine()->getRepository('AppBundle:Post');


        //Gestion des nouveaux posts
        $user = $this->getUser();
        $roles = isset($user) ? $user->getRoles() : [];
        $formView = null;

        if (in_array('ROLE_AUTHOR', $roles)) {
            // Création du formulaire
            $post = new Post();
            $post->setCreatedAt(new \DateTime());
            $post->setAuthor($user);
            $form = $this->createForm(PostType::class, $post);

            // On hydrate l'entité "$post" avec les données venant de la requete
            $form->handleRequest($request);

            // Traitement du formulaire
            if ($form->isSubmitted() && $form->isValid()) {

                $uploadManager = $this->get('stof_doctrine_extensions.uploadable.manager');
                $uploadManager->markEntityToUpload($post, $post->getImageFilename());

                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();

                // Redirection
                return $this->redirectToRoute('homepage');
            }
            $formView = $form->createView();
        }

        return $this->render('default/theme.html.twig', [
            'title' => "liste des posts par année ({$year})",
            'postList' => $postRepository->getPostsByYear($year),
            'postForm' => $formView
        ]);
    }

    //symfony paramconverter
    //https://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/annotations/converters.html
    /**
     * @Route("/post/modif/{id}", name="post_edit")
     * @param Request $request
     * @param Post $post
     * @return Response
     */
    public function editAction(Request $request, Post $post) {

        // Sécurisation de l'opération
        $user = $this->getUser();
        $roles = isset($user) ? $user->getRoles() : [];
        $userId = isset($user) ? $user->getId() : null;
        if (!in_array('ROLE_AUTHOR', $roles) || $userId != $post->getAuthor()->getId()) {
            throw new AccessDeniedHttpException('Vous n\'avez pas le droit pour modifier ce post');
        }

        // Création du formulaire
        $form = $this->createForm(PostType::class, $post);

        // Hydratation de l'entité
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('theme_details', ['id' => $post->getTheme()->getId()]);
        }

        return $this->render('post/edit.html.twig', ['postForm' => $form->createView()]);
    }

}