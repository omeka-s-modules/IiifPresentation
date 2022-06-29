<?php
namespace IiifPresentation\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ItemController extends AbstractActionController
{
    public function viewCollectionAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation/item/collection', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionAction()
    {
        $version = $this->params('version');
        $itemIds = explode(',', $this->params('item-ids'));
        $collection = $this->iiifPresentation()->getCollection($version, $itemIds);
        return $this->iiifPresentation()->getResponse($version, $collection);
    }

    public function viewManifestAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation/item/manifest', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function manifestAction()
    {
        $version = $this->params('version');
        $itemId =  $this->params('item-id');
        $manifest = $this->iiifPresentation()->getManifest($version, $itemId);
        return $this->iiifPresentation()->getResponse($version, $manifest);
    }
}
