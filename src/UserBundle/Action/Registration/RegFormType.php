<?php

namespace Cerad\Bundle\UserBundle\Action\Registration;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegFormType extends AbstractType
{
    protected $userClass;
    
    public function getName() { return 'cerad_user__registration_form_type'; }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => $this->userClass));
    }    
    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username','text');
        $builder->add('name',    'text');
        $builder->add('email',   'text');
    }
}