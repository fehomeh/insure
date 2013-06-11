<?php

namespace Insurance\ContentBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;

/**
 *
 * @author     sly
 */
class PolicyBlockService extends BaseBlockService
{

    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        // TODO: Implement validateBlock() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('content', 'textarea', array()),
            )
        ));
    }

    public function getDefaultSettings ()
    {

    }
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {

      return $this->renderResponse('InsuranceContentBundle:Helper:block_generate_policy.html.twig', array(
        //'feeds'     => $feeds,
        'block'     => $blockContext->getBlock(),
        'settings'  => $blockContext->getSettings(),
    ), $response);
    }
}
