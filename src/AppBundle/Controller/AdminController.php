<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Theme;
use AppBundle\Form\ThemeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 * Class AdminController
 * @package AppBundle\Controller
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_home")
     * @return Response
     */
    public function indexAction() {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/login", name="admin_login")
     * @return Response
     */
    public function admin_loginAction() {
        return $this->render('default/generic-login.html.twig',
            [
                'action' => $this->generateUrl('admin_login_check'),
                'title' => 'Login des administrateurs'
            ]);
    }

    /**
     * @Route("/themes", name="admin_themes")
     * @return Response
     */
    public function themeAction(Request $request){
        $repository = $this->getDoctrine()
            ->getRepository("AppBundle:Theme");

        $themeList = $repository->findAll();

        $theme = new Theme();

        $form = $this->createForm(ThemeType::class, $theme);

        // On hydrate l'entité "$theme" avec les données venant de la requete
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($theme);

            $em->flush();

            return $this->redirectToRoute('admin_themes');
        }

        return $this->render("admin/theme.html.twig", [
            "themeList" => $themeList,
            'themeForm' => $form->createView()
            ]);
    }

}