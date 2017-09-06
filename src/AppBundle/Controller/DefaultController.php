<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Post;
use AppBundle\Form\AuthorType;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $postRepository = $this->getDoctrine()
            ->getRepository('AppBundle:Post');

        $list = $repository->getAllThemes()->getArrayResult();
        $postListByYear = $postRepository->getPostsGroupedByYear();


        return $this->render('default/index.html.twig', [
            "themeList" => $list,
            'postList' => $postListByYear
        ]);
    }

    /**
     * @Route("/theme/{id}", name="theme_details", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function themeAction(Request $request, $id){

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $theme = $repository->find($id);
        // Pour les tests
//        $allThemes = $repository->getAllThemes()->getArrayResult();

        if(! $theme){
            throw new NotFoundHttpException("Thème introuvable");
        }


        //Gestion des nouveaux posts
        $user = $this->getUser();
        $roles = isset($user) ? $user->getRoles() : [];
        $formView = null;

        if (in_array('ROLE_AUTHOR', $roles)) {
            // Création du formulaire
            $post = new Post();
            $post->setCreatedAt(new \DateTime());
            $post->setAuthor($user)
            ->setTheme($theme);

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
            "theme" => $theme,
            "postList" => $theme->getPosts(),
//            "all" =>$allThemes, // Pour les tests
            'postForm' => $formView
        ]);
    }

//    /**
//     * @Route("/post-par-annee/{year}", name="post_by_year", requirements={"year":"\d{4}"})
//     * @param $year
//     * @return Response
//     */
//    public function postByYearAction($year) {
//        $postrepository = $this->getDoctrine()->getRepository('AppBundle:Post');
//
//        return $this->render('default/theme.html.twig', [
//            'title' => "liste des posts par année ({$year})",
//            'postList' => $postrepository->getPostsByYear($year)
//        ]);
//    }

    /**
     * @Route("/inscription", name="author_registration")
     * @param Request $request
     * @return Response
     */
    public function registrationAction(Request $request) {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Encodage du mot de passe
            $encoderFactory = $this->get('security.encoder_factory');
            $encoder = $encoderFactory->getEncoder($author);
            $author->setPassword($encoder->encodePassword($author->getPlainPassword(), null));
            $author->setPlainPassword(null);

            $em->persist($author);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/author-registration.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/author-login", name="author_login")
     * @return Response
     */
    public function authorLoginAction() {
        $securityUtils = $this->get('security.authentication_utils');

        return $this->render('default/generic-login.html.twig', [
            'action' => $this->generateUrl('author_login_check'),
            'title' => 'Indentification des auteurs',
            'userName' => $securityUtils->getLastUsername(),
            'error' => $securityUtils->getLastAuthenticationError()
        ]);
    }
}
