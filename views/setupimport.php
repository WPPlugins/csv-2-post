<?php
/**
 * Data Import [page]   
 *
 * @package CSV 2 POST
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class CSV2POST_Setupimport_View extends CSV2POST_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 1;
    
    protected $view_name = 'setupimport';
    
    public $purpose = 'normal';// normal, dashboard

    /**
    * Array of meta boxes, looped through to register them on views and as dashboard widgets
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 8.1.33
    * @version 1.1
    */
    public function meta_box_array() {
        // array of meta boxes + used to register dashboard widgets (id, title, callback, context, priority, callback arguments (array), dashboard widget (boolean) )   
        return $this->meta_boxes_array = array(
            // array( id, title, callback (usually parent, approach created by Ryan Bayne), context (position), priority, call back arguments array, add to dashboard (boolean), required capability
            array( $this->view_name . '-importsources', __( 'Import Data for Current Active Project', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'importsources' ), true, 'activate_plugins' ),
            array( $this->view_name . '-deleteduplicaterowsandposts', __( 'Delete Duplicate Rows and Posts', 'csv2post' ), array( $this, 'postbox_setupimport_deleteduplicaterowsandposts' ), 'side','default',array( 'formid' => 'deleteduplicaterowsandposts' ), true, 'activate_plugins' ),
        );    
    }
            
    /**
     * Set up the view with data and do things that are specific for this view
     *
     * @since 8.1.3
     *
     * @param string $action Action for this view
     * @param array $data Data for this view
     */
    public function setup( $action, array $data ) {
        global $csv2post_settings;
        
        // create constant for view name
        if(!defined( "CSV2POST_VIEWNAME") ){define( "CSV2POST_VIEWNAME", $this->view_name );}
        
        // add view introduction
        $this->add_text_box( 'viewintroduction', array( $this, 'viewintroduction' ), 'normal' );
                                
        // load the current project row and settings from that row
        if( isset( $csv2post_settings['currentproject'] ) && $csv2post_settings['currentproject'] !== false ) 
        {
            $this->project_object = $this->DB->get_project( $csv2post_settings['currentproject'] ); 
            if( !$this->project_object ) 
            {
                $this->current_project_settings = false;
            } 
            else 
            {
                $this->current_project_settings = maybe_unserialize( $this->project_object->projectsettings ); 
            }

            parent::setup( $action, $data );
            
            // using array register many meta boxes
            foreach( self::meta_box_array() as $key => $metabox ) 
            {
                // the $metabox array includes required capability to view the meta box
                if( isset( $metabox[7] ) && current_user_can( $metabox[7] ) ) 
                {
                    $this->add_meta_box( $metabox[0], $metabox[1], $metabox[2], $metabox[3], $metabox[4], $metabox[5] );   
                }               
            }        
        } 
        else 
        {
            $this->add_meta_box( $this->view_name . '-nocurrentproject', __( 'No Current Project', 'csv2post' ), array( $this->UI, 'metabox_nocurrentproject' ), 'normal','default',array( 'formid' => 'nocurrentproject' ) );      
        }    
    }

    /**
    * Outputs the meta boxes
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 8.1.33
    * @version 1.1
    */
    public function metaboxes() {
        parent::register_metaboxes( self::meta_box_array() );     
    }

    /**
    * This function is called when on WP core dashboard and it adds widgets to the dashboard using
    * the meta box functions in this class. 
    * 
    * @uses dashboard_widgets() in parent class CSV2POST_View which loops through meta boxes and registeres widgets
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 8.1.33
    * @version 1.1
    */
    public function dashboard() { 
        parent::dashboard_widgets( self::meta_box_array() );  
    }
     
    /**
    * All add_meta_box() callback to this function to keep the add_meta_box() call simple.
    * 
    * This function also offers a place to apply more security or arguments.
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 8.1.32
    * @version 1.0.1
    */
    function parent( $data, $box ) {
        eval( 'self::postbox_' . $this->view_name . '_' . $box['args']['formid'] . '( $data, $box );' );
    }

    /**
    * This views dismissable introduction.
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 0.0.1
    * @version 1.0
    */
    public function viewintroduction() {
        $main_title = __( 'Data Import Explained', 'csv2post' );
        
        $intro = __( 'Manual and automatic data import is available. For 
        testing or just to get a projects started I recommend manual import
        so that you witness everything working as required. Data will be imported
        to a custom table this plugin creates automatically. That table allows more
        advanced data management than most WordPress importers and autobloggers.', 'csv2post' );
        
        $title = false;//__( 'More Information', 'csv2post' );
        $info = false;//__( '<ol><li>Tutorials Coming Soon</li></ol>', 'csv2post' );
        $foot = false;//__( 'Get your tutorial link added to this list. Video, blog, forum and PDF documents accepted.', 'csv2post' );
        $this->UI->intro_box_dismissible( 'dataimport-introduction', $main_title, $intro, $info_area = true, $title, $info, $foot );               
    }
        
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_setupimport_importsources( $data, $box ) {                      
        global $csv2post_settings, $wpdb;      

        $sourceid_array = $this->DB->get_project_sourcesid( $csv2post_settings['currentproject'] );
        
        foreach( $sourceid_array as $key => $source_id )
        {
            // get the source row
            $row = $this->DB->selectrow( $wpdb->c2psources, 'sourceid = "' . $source_id . '"', 'path,tablename,thesep' );?>

            <?php $postbox_title = __( 'Import data into', 'csv2pot' ) . ' ' . $row->tablename;?>
            <?php $form_id = 'importdata'.$key;?>
            <a id="anchor_<?php echo $form_id;?>"></a>
            <h4><?php echo $postbox_title; ?></h4>

            <p class="csv2post_boxes_introtext">This action will attempt to import data from all files. If your working with a bath of files, it could really push CSV 2 POST and your WordPress. Your server may not allow the amount of processing involved. Feel free to visit the WebTechGlobal forum to discuss how best to manage your project.</p>
            
            <?php
            // register this form
            $this->FORMS->register_form( $form_id );    
            ?>
            
            <form method="post" name="<?php echo $form_id;?>" action="<?php echo get_admin_url() . 'admin.php?page=' . $_GET['page']; ?>">
                <?php $this->UI->hidden_form_values( $form_id, $postbox_title);?>

                <input type="hidden" name="tablename" value="<?php echo $row->tablename;?>">
                <input type="hidden" name="sourceid" value="<?php echo $source_id;?>">             
                
                <table class="form-table">
                <?php    
                ########################################################
                #                                                      #
                #                      PROJECT ID                      #
                #                                                      #
                ########################################################                
                $this->UI->option_subline( 
                	$csv2post_settings['currentproject'], 
                	'Project ID' 
                );
                
                ########################################################
                #                                                      #
                #                      SOURCE ID                       #
                #                                                      #
                ########################################################                
                $this->UI->option_subline( 
                	$source_id, 
                	'Source ID' 
                );
                
                ########################################################
                #                                                      #
                #                     IMPORTED ROWS                    #
                #                                                      #
                ########################################################                
                $this->UI->option_subline( 
                	$wpdb->get_var( "SELECT COUNT(*) FROM $row->tablename" ), 
                	'Imported' 
                );
                
                ########################################################
                #                                                      #
                #                      USED ROWS                       #
                #                                                      #
                ########################################################                
                $this->UI->option_subline( 
                	$wpdb->get_var( "SELECT COUNT(*) FROM $row->tablename WHERE c2p_postid != 0"), 
                	'Used' 
                );
                
                ########################################################
                #                                                      #
                #                     OUTDATED ROWS                    #
                #                                                      #
                ########################################################                
                // to determine how many rows are outdated we need to get the wp_c2psources changecounter value which tells us the total 
                // number of times the source has been updated, records with a lower changecounter have not been updated yet
                $changecount = $wpdb->get_var( "SELECT changecounter FROM $wpdb->c2psources");
                
                // now query all imported rows that have a lower value than $changecount
                $outdated = $wpdb->get_var( "SELECT COUNT(*) FROM $row->tablename WHERE c2p_changecounter < $changecount");
                $this->UI->option_subline( $outdated, 'Outdated' );
                
                ########################################################
                #                                                      #
                #                     EXPIRED ROWS                     #
                #                                                      #
                ########################################################                
                // TODO 3 -o Owner -c Information: Display the true value for Expired and Void rows.
                //$this->UI->option_subline( 0, 'Expired' );// rows older than user defined expiry date or defined column of expiry dates
                
                ########################################################
                #                                                      #
                #                     VOID ROWS                        #
                #                                                      #
                ########################################################                
                //$this->UI->option_subline( 0, 'Void' );// rows made void due to rules or fault or even public reporting a bad post                                                                                          
                
                ########################################################
                #                                                      #
                #                    DUPLICATE KEYS                    #
                #                                                      #
                ########################################################
                // display the total number of duplicate rows based on unique key column only
                $this->duplicate_keys = array();
                $this->idcolumn = false;
                
                if( isset( $this->current_project_settings['idcolumn'] ) && !empty($this->current_project_settings['idcolumn']) )
                { 
                    $this->idcolumn = $this->current_project_settings['idcolumn'];    

                    // get an array of the keys which have duplicates (not every duplicate just an array of keys that have 2 or more)
                    $this->duplicate_keys = $this->DB->get_duplicate_keys( $row->tablename, $this->idcolumn );
                    $this->duplicate_keys = $this->DB->get_duplicate_keys( $row->tablename, $this->idcolumn );
                }
                
                $this->UI->option_subline( count( $this->duplicate_keys ), 'Duplicate Keys' );
                ?>
                </table>
                
                <input class="button" type="submit" value="Submit" />
                
            </form>                    

        <?php 
        }
    } 

    /**
    * post box function
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_setupimport_deleteduplicaterowsandposts( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'If you have a unique ID column (keys) CSV 2 POST will detect duplicates for you.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );

        if( $this->duplicate_keys )
        {
            echo '<p>' . __( 'You have duplicate key values. You selected column <strong>' . $this->idcolumn . '</strong> as your unique
            value column. Duplicates are not recommended in that column for many operations to work properly. However the plugin does 
            not restrict them without the user requesting it.', 'csv2post' ) . '</p>';
            $this->UI->postbox_content_footer();
        }
        else
        {
            echo '<p>' . __( 'You do not have any duplicate keys.','csv2post' ) . '</p>';
        }
    }
}?>