<?php
/**
 * ItemReview
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The ItemReview index controller class.
 *
 * @package ItemReview
 */
class ItemReview_IndexController extends Omeka_Controller_AbstractActionController
{    
    public function indexAction() {

    }

    public function approveAction() {
        $item_id = $this->_getParam('item');
        $this->_approveItem($item_id);
        die('SUCCESS');
//approve the item
    }

    public function approveAllAction($collection=null) {
        if(is_object($collection))
            $collection = $collection->id;
        $activeFlags = array();
        $reviewFlags = get_db()->getTable('ItemReview')->findAll();
        foreach($reviewFlags as $reviewFlag) {
            $item = get_record_by_id('Item',$reviewFlag->item_id);
            if(empty($item)) {
                $reviewFlag->delete();
                continue;
            }
            if($collection == null || $collection == $item->collection_id)
                $this->approveItem($item);
        }
        $this->_helper->redirector->gotoUrl('items/browse');
    }

    public function indexAction() {
        $reviewFlags = get_db->getTable('ItemReview')->findAll();
        
    }

    private function _approveItem($item) {
        if(is_numeric($item))
            $item = get_record_by_id('Item',$item);
        
        $reviewFlag = get_db()->getTable('ItemReview')->findBy(array('item_id'),$item->id);
        $reviewFlag->delete();
        $item->public = true;
        $item->save();
    }

}