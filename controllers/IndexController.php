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
        $reviewFlags = get_db->getTable('ItemReview')->findAll();        
  }
  
}