<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 *
 */

namespace Da\OAuthServerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SerializedArrayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data) {
                $data = array();
            }

            if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
                throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
            }

            // The data mapper only adds, but does not remove items, so do this here
            $toDelete = array();

            foreach ($data as $name => $child) {
                if (null === $child ) {
                    $toDelete[] = $name;
                }
            }

            foreach ($toDelete as $name) {
                unset($data[$name]);
            }

            $event->setData(array_values($data));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'name'    => '',
            'options' => array(
                'label' => ' ',
                'required' => true,
                'attr' => array(
                    'class' => 'da_oauthserver__serialized_array_item'
                )
            ),
            'allow_add'          => true,
            'allow_delete'       => true,
            'by_reference'       => false,
            'cascade_validation' => true,
            'attr'               => array(
                'class' => 'da_oauthserver__serialized_array'
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'serialized_array';
    }
}
