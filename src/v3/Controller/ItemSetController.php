<?php
namespace IiifPresentation\v3\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class ItemSetController extends AbstractActionController
{
    public function viewCollectionAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-3/item-set/collection', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionAction()
    {
        $itemSetId = $this->params('item-set-id');
        $collection = $this->iiifPresentation3()->getItemSetCollection($itemSetId);
        // Allow modules to modify the collection.
        $params = $this->iiifPresentation2()->triggerEvent(
            'iiif_presentation.3.item_set.collection',
            [
                'collection' => $collection,
                'item_set_id' => $itemSetId,
            ]
        );
        return $this->iiifPresentation3()->getResponse($params['collection']);
    }

    public function viewCollectionsAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-3/item-set/collections', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionsAction()
    {
        $itemSetIds = explode(',', $this->params('item-set-ids'));
        $collection = $this->iiifPresentation3()->getItemSetsCollection($itemSetIds);
        // Allow modules to modify the collection.
        $params = $this->iiifPresentation2()->triggerEvent(
            'iiif_presentation.3.item_set.collections',
            [
                'collection' => $collection,
                'item_set_ids' => $itemSetIds,
            ]
        );
        return $this->iiifPresentation3()->getResponse($params['collection']);
    }
}
