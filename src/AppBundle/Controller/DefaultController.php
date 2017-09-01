<?php

namespace AppBundle\Controller;

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
    public function indexAction()
    {
        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $postRepository = $this->getDoctrine()
            ->getRepository('AppBundle:Post');

        $list = $repository->getAllThemes()->getArrayResult();
        $postListByYear = $postRepository->getPostsGroupedByYear();

        return $this->render('default/index.html.twig', ["themeList" => $list, 'postList' => $postListByYear]);
    }

    /**
     * @Route("/theme/{id}", name="theme_details", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function themeAction($id){

        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $theme = $repository->find($id);

        // Pour les tests
        $allThemes = $repository->getAllThemes()->getArrayResult();

        if(! $theme){
            throw new NotFoundHttpException("Thème introuvable");
        }


        return $this->render('default/theme.html.twig', [
            "theme" => $theme,
            "postList" => $theme->getPosts(),
            "all" =>$allThemes // Pour les tests
        ]);
    }

    /**
     * @Route("/post-par-annee/{year}", name="post_by_year", requirements={"year":"\d{4}"})
     * @param $year
     * @return Response
     */
    public function postByYearAction($year) {
        $postrepository = $this->getDoctrine()->getRepository('AppBundle:Post');

        return $this->render('default/theme.html.twig', [
            'title' => "liste des posts par année ({$year})",
            'postList' => $postrepository->getPostsByYear($year)
        ]);
    }
}
