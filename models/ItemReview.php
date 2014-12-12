<?php
/**
 * An item pending  entry
 * 
 * @package ItemReview
 *
 */
class ItemReview extends Omeka_Record_AbstractRecord
{
    /*
     *@var int The record ID
     */
    public $id; 

    /*
     *@var int The id of the Item record that requires review
     */
    public $item_id;

}

?>