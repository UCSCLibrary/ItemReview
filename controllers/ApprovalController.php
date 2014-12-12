<?php
/**
 * Item Review
 *
 * This Omeka 2.0+ plugin allows administrators the option to require curatrial review for bulk imported items (or all items) before they can be made public.
 * 
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 *
 * @package ItemReview
 */

/**
 * The Item Review Profile controller class.
 */
class ItemReview_ApprovalController extends Omeka_Controller_AbstractActionController
{    

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
                $this->_approveItem($item);
        }
        $this->_helper->redirector->gotoUrl('items/browse');
    }

    public function approveAction() {
        $item_id = $this->_getParam('item');
        $this->_approveItem($item_id);
        die('SUCCESS');
    }

    private function _approveItem($item) {
        if(!is_object($item))
            $item = get_record_by_id('Item',$item);
        $item->public = 1;
        $item->test = true;
        $item->save();

        $reviewFlags = get_db()->getTable('ItemReview')->findBy(array('item_id' => $item->id));
        foreach($reviewFlags as $reviewFlag)  {
            $reviewFlag->delete();
        }
    }





}

?>

