<?php
namespace Insurance\ContentBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InsuranceAdminController extends Controller {
	public function batchActionDelete(ProxyQueryInterface $queryProxy)
	{
		if (false === $this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }
        $modelManager = $this->admin->getModelManager();
		$em = $queryProxy->getEntityManager();
		$fakeCaptured = false;
		foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
			try {
				$policy = $em->getRepository('InsuranceContentBundle:Policy')->findOneById($object[0]->getPolicy()->getId());
				$policy->setStatus(0);
				$em->persist($policy);
				$em->flush();
				$policy = null;
			} catch (\Exception $e) {
				$this->addFlash('sonata_flash_error', 'flash_batch_delete_error');
				$fakeCaptured = true;
			}
		}
		if (!$fakeCaptured) {
			try {
				$modelManager->batchDelete($this->admin->getClass(), $queryProxy);
				$this->addFlash('sonata_flash_success', 'flash_batch_delete_success');
			} catch ( ModelManagerException $e ) {
				$this->addFlash('sonata_flash_error', 'flash_batch_delete_error');
			}
		}
		return new RedirectResponse($this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters())));
	}
}
?>