<?php
namespace IiifPresentation\v3\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

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
        $collection = $this->iiifPresentation3()->getItemsCollection($itemIds, $this->translate('Items Collection'));
        // Allow modules to modify the collection.
        $params = $this->iiifPresentation2()->triggerEvent(
            'iiif_presentation.3.item.collection',
            [
                'collection' => $collection,
                'item_ids' => $itemIds,
            ]
        );
        return $this->iiifPresentation3()->getResponse($params['collection']);
    }

    public function viewManifestAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-3/item/manifest', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function manifestAction()
    {
        $itemId = $this->params('item-id');
        $manifest = $this->iiifPresentation3()->getItemManifest($itemId);
        // Allow modules to modify the manifest.
        $params = $this->iiifPresentation2()->triggerEvent(
            'iiif_presentation.3.item.manifest',
            [
                'manifest' => $manifest,
                'item_id' => $itemId,
            ]
        );
        return $this->iiifPresentation3()->getResponse($params['manifest']);
    }
}
