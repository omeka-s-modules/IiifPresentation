<?php
namespace IiifPresentation\v2\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class ItemSetController extends AbstractActionController
{
    public function viewCollectionAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-2/item-set/collection', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionAction()
    {
        $itemSetId = $this->params('item-set-id');
        $collection = $this->iiifPresentation2()->getItemSetCollection($itemSetId);
        return $this->iiifPresentation2()->getResponse($collection);
    }

    public function viewCollectionsAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-2/item-set/collections', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionsAction()
    {
        $itemSetIds = explode(',', $this->params('item-set-ids'));
        $collection = $this->iiifPresentation2()->getItemSetsCollection($itemSetIds);
        return $this->iiifPresentation2()->getResponse($collection);
    }
}
