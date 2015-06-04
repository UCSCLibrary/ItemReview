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
 * Item Review plugin class
 */
class ItemReviewPlugin extends Omeka_Plugin_AbstractPlugin
{

    protected $_flagged=false;
    protected $_approved=false;

    /**
     * @var array Options for the plugin.
     */
    protected $_options = array(
        'reviewerRoles'=>null,
        'submitterRoles'=>null,
        'notifyReviewers'=>false
    );

    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'admin_head',
        'config_form',
        'config',
        'before_save_item',
        'after_save_item',
        'admin_items_show',
        'admin_items_browse',
        'admin_items_panel_buttons',
        'admin_items_browse_simple_each',
        'items_browse_sql'
    );
  
    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'items_browse_params'
    );

    public function hookAdminItemsBrowse() {
      if($this->_isReviewer() && has_loop_records()){
            if(isset($_REQUEST['show-pending-review']) && $_REQUEST['show-pending-review'])
                echo('<a id="show-pending-all-a" href="'.preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']).'"><button id="show-pending-all-button">Show All</button></a>');
            else
                echo('<a id="show-pending-only-a" href="?show-pending-reviews=true"><button id="show-pending-only-button">Show items pending review only</button></a>');
        }
    }

    public function hookItemsBrowseSql($args) {
        $params = $args['params'];
        $select = $args['select'];

        if(isset($params['pending-only']) && $params['pending-only']) {
            $select->joinInner(array('item_reviews' => get_db()->ItemReview),'items.id = item_reviews.item_id',array());
//            die($select->__toString());
        }
    }

    public function filterItemsBrowseParams($params) {
        if(isset($_REQUEST['show-pending-review']) && $_REQUEST['show-pending-review'])
            $params['pending-only']=true;
        return $params;
    }

    /**
     * Load the plugin javascript & css when admin section loads
     *
     *@return void
     */
    public function hookAdminHead()
    {
        queue_js_file('ItemReview');
        queue_css_file('ItemReview');
    }

    protected function _isReviewer($user = null){
        $user = empty($user) ? current_user() : $user;
        $reviewerRoles = unserialize(get_option('reviewerRoles'));
        return in_array($user->role,$reviewerRoles);
    }

    protected function _isSubmitter($user = null){
        $user = empty($user) ? current_user() : $user;
        $submitterRoles = unserialize(get_option('submitterRoles'));
        return in_array($user->role,$submitterRoles);
    }

    public function hookBeforeSaveItem($args) {
        if($this->_approved)
            return;
        if(!$this->_isSubmitter())
            return;
        $item = $args['record'];
        if (!$item->public)
            return;
        if($args['insert'] != 1) {
            $oldItem = get_record_by_id('Item',$item->id);
            if(!$oldItem->public) {
                //old private item going public, flag for review
                $this->_flagged = true;
            }
        } else  {
            //new public item flag for review
            $this->_flagged = true;
        }
    }

    public function hookAfterSaveItem($args) {
        $item = $args['record'];
        if($item->test)
            return;
        $item = get_record_by_id('Item',$item->id);
        if($this->_flagged) {
            $this->_flagged = false;

            $flag = new ItemReview();
            $flag->item_id = $item->id;
            $flag->save();

            $item->public = false;
            $item->save();
        } else if ($this->_isReviewer() && isset($_REQUEST['submit']) && $_REQUEST['submit'] =="Approve for Publication") {
            $this->_approved = true;
            $this->_approveItem($item);
            
        }
        
    }

    private function _approveItem($item) {
        if(!is_object($item))
            $item = get_record_by_id('Item',$item);
        
        $reviewFlags = get_db()->getTable('ItemReview')->findBy(array('item_id'=>$item->id));
        foreach($reviewFlags as $reviewFlag) {
            $reviewFlag->delete();
        }
        $item->public = 1;
        unset($_REQUEST['submit']);//prevent possible infinite loops
        $item->save(); 
    }

    /**
     * When the plugin installs, define options and such 
     * 
     * @return void
     */
    public function hookInstall()
    {
        try{
            $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->_db->ItemReview}` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `item_id` int(10) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $this->_db->query($sql);
        }catch(Exception $e) {
            throw $e; 
        }

        $this->_installOptions();
        set_option('reviewerRoles',serialize(array()));
        set_option('submitterRoles',serialize(array()));
    }

    public function hookConfigForm() {
        require dirname(__FILE__) . '/forms/config_form.php';
    }

    public function hookAdminItemsShow($args) {
        $item = $args['item'];
        $table = $this->_db->getTable('ItemReview');
        if($reviews = $table->findBy(array('item_id'=>(int)$item->id))) {
            ?>
            <p class="item-review-panel-message">This item has been flagged for administrative review. It will be made public when it has been reviewed.</p>
<?php
        }
    }


    public function hookAdminItemsBrowseSimpleEach($args) {
        $item = $args['item'];
        $itemID = $item->id;
        $table = $this->_db->getTable('ItemReview');
        if($reviews = $table->findBy(array('item_id'=>(int)$item->id))) {
            echo('<p class="item-review-browse-message">Pending Review</p>');
            if($this->_isReviewer()) {
                echo('<button title="'.absolute_url('item-review/approval/approve/item/').$itemID.'" class="review-approve" id="'.$item->id.'">Approve</button>');
                if(version_compare(OMEKA_VERSION,'2.2.1') >= 0){
                    $csrf = new Omeka_Form_Element_SessionCsrfToken('csrf_token');
                    echo('<script>var csrf_token="'.$csrf->getToken().'"</script>');
                } else {
                    echo('<script>var csrf_token=""</script>');
                }
            }
        }
    }
    public function hookAdminItemsPanelButtons($args) {
        $item = $args['record'];
        $table = $this->_db->getTable('ItemReview');
        if($reviews = $table->findBy(array('item_id'=>(int)$item->id))) {
            echo('<p class="item-review-panel-message">This item has been flagged for administrative review. It will be made public when it has been reviewed.</p>');
            if($this->_isReviewer()) {
                echo('<input id="review-approve" class="submit big green button" type="submit" value="Approve for Publication" name="submit">');
            }
        }
    }

    public function hookConfig() {
        set_option('notifyReviewers',$_REQUEST['notify-reviewers']);
        set_option('reviewerRoles',serialize($_REQUEST['reviewer-roles']));
        set_option('submitterRoles',serialize($_REQUEST['submitter-roles']));
    }

    /**
     * When the plugin uninstalls, delete the database tables 
     *which store the logs
     * 
     * @return void
     */
    public function hookUninstall()
    {

      try{
	$db = get_db();
	$sql = "DROP TABLE IF EXISTS `$db->ItemReview` ";
	$db->query($sql);
      }catch(Exception $e) {
	throw $e;	
      }
        $this->_uninstallOptions();
    }
}
?>