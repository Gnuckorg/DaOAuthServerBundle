<?php

namespace Da\OAuthServerBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\ProfileController as BaseProfileController;

class ProfileController extends BaseProfileController
{
    /**
     * Edit the user for an authspace.
     *
     * @Route("/profile/{authspace}")
     */
    public function editAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm(array('csrf_protection' => false));
        $form->setData($user);

        if ('POST' === $request->getMethod()) {
            $data = $request->request->get($form->getName());
            if (null === $data) {
                $formName = $request->request->get('form_name', '');
                $data = $request->request->get($formName);
            }

            $user->setRaw(json_encode($data['raw']));
            unset($data['raw']);

            $form->submit($data);

            if ($form->isValid()) {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->container->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->container->get('router')->generate('fos_user_profile_show');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            } else {
                if ($request->request->get('account', false)) {
                    return new Response(
                        json_encode(array('error' => $this->getFormErrors($form))),
                        400,
                        array('Content-Type' => 'application/json')
                    );
                }
            }
        }

        return $this->container->get('templating')->renderResponse(
            'FOSUserBundle:Profile:edit.html.'.$this->container->getParameter('fos_user.template.engine'),
            array('form' => $form->createView())
        );
    }

    /**
     * Get the errors of a form.
     *
     * @param FormInterface $form.
     *
     * @return array The errors.
     */
    public function getFormErrors(FormInterface $form)
    {
        $errors = array();

        $parent = $form->getParent();
        $name = 'main';
        if ($parent) {
            $name = $form->getName();
        }

        while ($parent && $parent->getParent()) {
            $name = sprintf('%s.%s', $parent->getName(), $name);
            $parent = $parent->getParent();
        }

        foreach ($form->getErrors() as $error) {
            if (!isset($errors[$name])) {
                $errors[$name] = array();
            }

            $errors[$name][] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            $errors = array_merge(
                $errors,
                $this->getFormErrors($child)
            );
        }

        return $errors;
    }
}