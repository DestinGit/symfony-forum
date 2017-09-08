<?php
/**
 * Created by PhpStorm.
 * User: formation
 * Date: 08/09/2017
 * Time: 11:21
 */

namespace AppBundle\Form\Handler;


use AppBundle\Entity\Manager\PostManager;
use AppBundle\Entity\Post;
use Gedmo\Uploadable\Uploadable;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;

class PostFormHandler
{
    /**
     * @var Post
     */
    private $post;

    /**
     * @var string
     */
    private $formClassName;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var PostManager
     */
    private $manager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var UploadableManager
     */
    private $uploadableManager;

    /**
     * @var Form
     */
    private $form;

    /**
     * PostFormHandler constructor.
     * @param Post $post
     * @param string $formClassName
     * @param FormFactory $formFactory
     * @param PostManager $manager
     * @param RequestStack $requestStack
     */
    public function __construct(Post $post, $formClassName, FormFactory $formFactory,
                                PostManager $manager, RequestStack $requestStack,
                                UploadableManager $uploadableManager)
    {
        $this->post = $post;
        $this->formClassName = $formClassName;
        $this->formFactory = $formFactory;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->uploadableManager = $uploadableManager;
    }

    /**
     * @return bool
     */
    public function process() {
        $this->form = $this->formFactory->create($this->formClassName, $this->post);

        //  Gérer la requête sur ce formulaire (hydratation)
        $this->form->handleRequest($this->requestStack->getCurrentRequest());

        $success = false;

        // Test si le formulaire est valid et soumis
        if ($this->form->isSubmitted() and $this->form->isValid()) {

            $success = true;
            // Gestion de l'upload
            if ($this->post->getImageFilename() instanceof Uploadable) {
                $this->uploadableManager->markEntityToUpload(
                    $this->post,
                    $this->post->getImageFilename()
                );
            }

            // Persistance des données
            $this->manager->setPost($this->post)->save();
        }

        return $success;
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param Post $post
     * @return PostFormHandler
     */
    public function setPost($post)
    {
        $this->post = $post;
        return $this;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     * @return PostFormHandler
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    public function getFormView() {
        return $this->form->createView();
    }
}