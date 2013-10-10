<?php

namespace Da\OAuthServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('authSpace')
            ->add('raw')
            ->add('username')
            ->add('email')
            ->add('password')
            ->add('plainPassword')
            ->add('locked', null, array('required' => false))
            ->add('expired', null, array('required' => false))
            ->add('roles')
            ->add('credentialsExpired', null, array('required' => false))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Da\OAuthServerBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'da_oauthserverbundle_usertype';
    }
}
