<?php
namespace IiifPresentation\Controller\v3;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ItemSetController extends AbstractActionController
{
    public function viewCollectionAction()
    {
        $url = $this->url()->fromRoute('iiif-presentation-3/item-set/collection', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $url]]);
    }

    public function collectionAction()
    {
        $itemSetId =  $this->params('item-set-id');
        $collection = $this->iiifPresentation3()->getItemSetCollection($itemSetId);
        return $this->iiifPresentation3()->getResponse($collection);
    }
}
