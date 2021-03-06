<?php
/**
 * Rules [page]   
 *
 * @package CSV 2 POST
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class CSV2POST_Rules_View extends CSV2POST_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 2;
    
    protected $view_name = 'rules';
    
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
            array( 'rules-setexpecteddatatypes', __( 'Set Expected Data Types', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'setexpecteddatatypes' ), true, 'activate_plugins' ),
            array( 'rules-datasplitter', __( 'Data Splitter', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'datasplitter' ), true, 'activate_plugins' ),
            array( 'rules-roundnumberup', __( 'Round Number Up', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'roundnumberup' ), true, 'activate_plugins' ),
            //array( 'rules-roundnumber', __( 'Round Number', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'roundnumber' ), true, 'activate_plugins' ),
            array( 'rules-captalizefirstletter', __( 'Capitalize First Letter', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'captalizefirstletter' ), true, 'activate_plugins' ),
            array( 'rules-lowercaseall', __( 'All Lower Case', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'lowercaseall' ), true, 'activate_plugins' ),
            array( 'rules-uppercaseall', __( 'All Upper Case', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'uppercaseall' ), true, 'activate_plugins' ),
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
        
        // create class objects
        $this->CSV2POST = CSV2POST::load_class( 'CSV2POST', 'class-csv2post.php', 'classes' );
        $this->UI = CSV2POST::load_class( 'CSV2POST_UI', 'class-ui.php', 'classes' ); 
        $this->DB = CSV2POST::load_class( 'CSV2POST_DB', 'class-wpdb.php', 'classes' );
        $this->PHP = CSV2POST::load_class( 'CSV2POST_PHP', 'class-phplibrary.php', 'classes' );
        $this->FORMS = CSV2POST::load_class( 'CSV2POST_FORMS', 'class-forms.php', 'classes' );

        // add view introduction
        $this->add_text_box( 'viewintroduction', array( $this, 'viewintroduction' ), 'normal' );
                                
        // set current project values
        if( isset( $csv2post_settings['currentproject'] ) && $csv2post_settings['currentproject'] !== false ) {
            
            $this->project_object = $this->DB->get_project( $csv2post_settings['currentproject'] ); 
            if( !$this->project_object ) {
                $this->current_project_settings = false;
            } else {
                $this->current_project_settings = maybe_unserialize( $this->project_object->projectsettings ); 
            }

            parent::setup( $action, $data );     

            // using array register many meta boxes
            foreach( self::meta_box_array() as $key => $metabox ) {
                // the $metabox array includes required capability to view the meta box
                if( isset( $metabox[7] ) && current_user_can( $metabox[7] ) ) {
                    $this->add_meta_box( $metabox[0], $metabox[1], $metabox[2], $metabox[3], $metabox[4], $metabox[5] );   
                }               
            }        
            
        } else {
            $this->add_meta_box( 'rules-nocurrentproject', __( 'No Current Project', 'csv2post' ), array( $this->UI, 'metabox_nocurrentproject' ), 'normal','default',array( 'formid' => 'nocurrentproject' ) );      
        }
    }

    /**
    * Outputs the meta boxes
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 0.0.3
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
    * @since 0.0.2
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
        $main_title = __( 'Rules Introduction', 'csv2post' );
        $intro = __( 'Use these forms to create rules for your data. Change specific words or numbers that fall into a range to something more suited to your needs. These rules are usually applied during import and because the idea is to make the change as soon as possible. Then be left with our working set of data in the projects database table (the temporary table data is stored in before creating posts). One of the tools available include the Data Splitter which splits values already separated with a slash or other character. Some affiliate networks provide category data in a single column. Basic autoblogger plugins will use it as it is but CSV 2 POST has advanced features which require preparation first. I can easily add more forms to this view. We can decrease price values by a specific percentage, change stock counts, change SOLD to Sold Out and the possibilities go on. ', 'csv2post' );
        $title = __( 'More Information', 'csv2post' );
        $info = __( '<ol><li>Tutorials Coming Soon</li></ol>', 'csv2post' );
        $foot = __( 'Get your tutorial link added to this list. Video, blog, forum and PDF documents accepted.', 'csv2post' );
        $this->UI->intro_box_dismissible( 'rules-introduction', $main_title, $intro, $info_area = true, $title, $info, $foot );               
    }
         
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_setexpecteddatatypes( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'An optional form and in early use. The intention is to tell the plugin what should be in our columns and handle any discrepancies based on that information.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  

            <table class="form-table">
            <?php
            $this->UI->option_column_datatypes( $csv2post_settings['currentproject'], 'datatypescolumns', 'datatypescolumns' ); 
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_datasplitter( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Some affiliate networks provide multiple values of data in a single column, each value separated by a slash.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings,$wpdb;
        ?>  

            <table class="form-table">
            <?php
            // check if main table has splitter rule, if not check the next and so on
            // if splitter rule found use it to populate this form else show default and empty values
            $sourceid_array = $this->DB->get_project_sourcesid( $csv2post_settings['currentproject'] );
            foreach( $sourceid_array as $key => $sourceid){
                $rules_array = $this->DB->selectrow( $wpdb->c2psources, "sourceid = $sourceid", 'rules' );
                $rules_array = maybe_unserialize( $rules_array->rules);
                if(!is_null( $rules_array ) ){
                    break;
                }         
            }
            
            // separator menu
            $sep_array = array( ', ' => ', ', '|' => '|', ';' => ';', '/' => '/' );
            $sep = ', ';
            if( isset( $rules_array['splitter']['separator'] ) && in_array( $rules_array['splitter']['separator'], $sep_array ) ){$sep = $rules_array['splitter']['separator'];}                                                           
            $this->UI->option_menu( 'Separator', 'datasplitterseparator', 'datasplitterseparator', $sep_array, $sep);
            
            // menu of columns for selecting data to be split into multiple columns
            $currentsplittercolumn = array( 'nosplittertable', 'nosplittercolumn' );
            if( isset( $rules_array['splitter']['datasplitertable'] ) && isset( $rules_array['splitter']['datasplitercolumn'] ) ){
                $currentsplittercolumn = array( $rules_array['splitter']['datasplitertable'], $rules_array['splitter']['datasplitercolumn'] );    
            }
            $this->UI->option_projectcolumns_splittermenu( 'Column', $csv2post_settings['currentproject'], 'datasplitercolumn', 'datasplittercolumn', $currentsplittercolumn);
            
            // columns that data will be inserted too
            for( $i=1;$i<=5;$i++){
                $current_table = '';
                $current_column = '';
                
                // receiving tables
                if( isset( $rules_array['splitter']["receivingtable$i"] ) ){
                    $current_table = $rules_array['splitter']["receivingtable$i"];
                }
                $this->UI->option_text( __( 'Receiving Table' ) . ' ' . $i, "receivingtable$i", "receivingtable$i", $current_table, false );
                
                // receiving columns
                if( isset( $rules_array['splitter']["receivingcolumn$i"] ) ){
                    $current_column = $rules_array['splitter']["receivingcolumn$i"];
                }                
                $this->UI->option_text( __( 'Receiving Column' ) . ' ' . $i, "receivingcolumn$i", "receivingcolumn$i", $current_column, false );
            }
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }
   
    
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_roundnumberup( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Replace decimal numbers by rounding them up on one or more columns.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  

            <table class="form-table">
            <?php
            $this->UI->option_project_headers_checkboxes( $csv2post_settings['currentproject'], 'roundnumberupcolumns' );
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }
        
    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_roundnumber( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( '', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  

            <table class="form-table">
            <?php
            $this->UI->option_project_headers_checkboxes( $csv2post_settings['currentproject'], 'roundnumbercolumns' );
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }

    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_captalizefirstletter( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( '', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  

            <table class="form-table">
            <?php
            $this->UI->option_project_headers_checkboxes( $csv2post_settings['currentproject'], 'captalizefirstlettercolumns' );
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }

    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_lowercaseall( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( '', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  

            <table class="form-table">
            <?php
            $this->UI->option_project_headers_checkboxes( $csv2post_settings['currentproject'], 'lowercaseallcolumns' );
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }

    /**
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_rules_uppercaseall( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Transform all letters to uppercase in the selected colums.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  
            
            <table class="form-table">
            <?php
            $this->UI->option_project_headers_checkboxes( $csv2post_settings['currentproject'], 'uppercaseallcolumns' );
            ?>
            </table>
            
        <?php 
        $this->UI->postbox_content_footer();
    }        
         
}?>