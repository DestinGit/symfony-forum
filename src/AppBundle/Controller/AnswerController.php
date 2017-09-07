<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AnswerController extends Controller
{
    /**
     * @Route("/api/{idAnswer}/{status}",
     *     requirements={"idAnswer"="\d+", "status"="\d+"})
     * @param Request $request
     * @return array|JsonResponse
     */
    public function ajaxUserAction(Request $request, $idAnswer, $status) {
        $response = [];

        if($request->isXmlHttpRequest()) {
            $answerRepository = $this->getDoctrine()->getRepository('AppBundle:Answer');
            $answer = $answerRepository->find($idAnswer);
            $answer->setStatus($status);

            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();

            $response = new JsonResponse([true]);
            $response->headers->set("Access-Control-Allow-Origin", "*");
        }
        return $response;
    }

}
