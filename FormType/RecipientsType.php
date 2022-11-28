<?php

namespace FOS\MessageBundle\FormType;

use FOS\MessageBundle\DataTransformer\RecipientsDataTransformer;
use FOS\MessageBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of RecipientsType.
 *
 * @author Łukasz Pospiech <zocimek@gmail.com>
 */
class RecipientsType extends AbstractType
{
    /**
     * @var RecipientsDataTransformer
     */
    private $recipientsTransformer;

    /**
     * @param RecipientsDataTransformer $transformer
     */
    public function __construct(RecipientsDataTransformer $transformer)
    {
        $this->recipientsTransformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->recipientsTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'invalid_message' => 'The selected recipient does not exist',
        ));
    }

    public function getBlockPrefix(): string
    {
        return 'recipients_selector';
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
