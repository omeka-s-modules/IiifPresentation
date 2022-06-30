<?php
namespace IiifPresentation\Controller\v3;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ItemController extends AbstractActionController
{
    public function viewCollectionAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-3/item/collection', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionAction()
    {
        $itemIds = explode(',', $this->params('item-ids'));
        $collection = $this->iiifPresentation3()->getCollection($itemIds);
        return $this->iiifPresentation3()->getResponse($collection);
    }

    public function viewManifestAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-3/item/manifest', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function manifestAction()
    {
        $itemId =  $this->params('item-id');
        $manifest = $this->iiifPresentation3()->getManifest($itemId);
        return $this->iiifPresentation3()->getResponse($manifest);
    }
}