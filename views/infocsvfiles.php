<?php
/**
 * List of CSV files, whatever type of source.  
 *
 * @package CSV 2 POST
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class CSV2POST_Infocsvfiles_View extends CSV2POST_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 1;
    
    protected $view_name = 'infocsvfiles';
    
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
            //array( $this->view_name . '-datasourcestable', __( 'Data Sources Table', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'datasourcestable' ), true, 'activate_plugins' ),
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
        
        // set current project values
        if( isset( $csv2post_settings['currentproject'] ) && $csv2post_settings['currentproject'] !== false ) {
            $this->project_object = $this->DB->get_project( $csv2post_settings['currentproject'] ); 
            if( !$this->project_object ) {
                $this->current_project_settings = false;
            } else {
                $this->current_project_settings = maybe_unserialize( $this->project_object->projectsettings ); 
            }
        }

        // add view introduction
        $this->add_text_box( 'viewintroduction', array( $this, 'viewintroduction' ), 'normal' );
                
        parent::setup( $action, $data );

        // create a data table ( use "head" to position before any meta boxes and outside of meta box related divs)
        $this->add_text_box( 'head', array( $this, 'datatables' ), 'normal' );
                 
        // using array register many meta boxes
        foreach( self::meta_box_array() as $key => $metabox ) {
            // the $metabox array includes required capability to view the meta box
            if( isset( $metabox[7] ) && current_user_can( $metabox[7] ) ) {
                $this->add_meta_box( $metabox[0], $metabox[1], $metabox[2], $metabox[3], $metabox[4], $metabox[5] );   
            }               
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
    * This views dismissable introduction.
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 0.0.1
    * @version 1.0
    */
    public function viewintroduction() {
        $main_title = __( 'CSV Files Introduction', 'csv2post' );
        $intro = __( 'The table below is important for users who are working with multiple .csv files OR require a lot of automation from CSV 2 POST. Use it to monitor individual files, ensure our configuration is working and ensure CSV 2 POST is working as intended. You can even use the table to confirm that files are being updated by their source.', 'csv2post' );
        $title = false;//__( 'More Information', 'csv2post' );
        $info = false;//__( '<ol><li>Tutorials Coming Soon</li></ol>', 'csv2post' );
        $foot = false;//__( 'Get your tutorial link added to this list. Video, blog, forum and PDF documents accepted.', 'csv2post' );
        $this->UI->intro_box_dismissible( 'csvfiles-introduction', $main_title, $intro, $info_area = true, $title, $info, $foot );               
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
    * post box function for testing
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_infocsvfiles_datasourcestable( $data, $box ) { 

    }     

    /**
    * Displays one or more tables of data at the top of the page before post boxes
    * 
    * @author Ryan R. Bayne
    * @package CSV 2 POST
    * @since 0.0.1
    * @version 1.0
    */
    public function datatables( $data, $box ) { 
        global $wpdb;
        $query_results = $this->DB->selectwherearray( $wpdb->c2psources, 'sourceid = sourceid', 'sourceid', '*' );    
               
        $WPTableObject = new CSV2POST_CSVFiles_Table();
        $WPTableObject->prepare_items_further( $query_results, 50 );
        ?>

        <form method="get">
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />
            <?php             
            // add search field
            $WPTableObject->search_box( 'search', 'theidhere' ); 
            
            // display the table
            $WPTableObject->display();
            ?>
        </form>
 
        <?php               
    }   
}

class CSV2POST_CSVFiles_Table extends WP_List_Table {

    function __construct() {
        global $status, $page;
             
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }

    function column_default( $item, $column_name ){
             
        $attributes = "class=\"$column_name column-$column_name\"";
                
        switch( $column_name){
            
            case 'sourceid':
                return $item['sourceid'];    
                break;            
            case 'projectid':
                if( $item['projectid'] == 0){return 'Unknown';}
                return $item['projectid'];    
                break;
            case 'sourcetype':
                return $item['sourcetype'];    
                break;
            case 'progress':
                return $item['progress'];
                break;            
            case 'timestamp':
                return $item['timestamp'];    
                break;   
            case 'path':
                return $item['path'];    
                break;           
            //case 'directory':
                //return $item['directory'];    
                //break;
            case 'idcolumn':
                return $item['idcolumn'];
                break;               
            case 'monitorfilechange':
                if( isset( $item['monitorfilechange'] ) && $item['monitorfilechange'] !== false ) { return __( 'On', 'csv2post' ); };
                return __( 'Off', 'csv2post' );
                break;
            case 'changecounter':
                return $item['changecounter'];
                break;
            case 'datatreatment':
                return $item['datatreatment'];
                break;
            case 'parentfileid':
                return $item['parentfileid'];
                break;
            case 'tablename':
                return $item['tablename'];
                break;
            case 'rules':
                if( isset( $item['rules'] ) && !empty( $item['rules'] ) ) { return __( 'Yes', 'csv2post' ); }
                return __( 'No', 'csv2post' );
                break;
            //case 'thesep':
                //return $item['thesep'];
                //break; 
                                                                      
            default:
                return 'No column function or default setup in switch statement';
        }
    }

    /*
    function column_title( $item){

    } */

    function get_columns() {
        $columns = array(
            'sourceid' => __( 'ID', 'csv2post' ),
            'projectid' => __( 'Project', 'csv2post' ),
            'sourcetype' => __( 'Type', 'csv2post' ),
            'progress' => __( 'Progress', 'csv2post' ),
            'timestamp' => __( 'Timestamp', 'csv2post' ),            
            'path' => __( 'Path', 'csv2post' ),
            //'directory' => __( 'Directory', 'csv2post' ),
            'idcolumn' => __( 'ID Column', 'csv2post' ),
            'monitorfilechange' => __( 'File Monitoring', 'csv2post' ),
            'changecounter' => __( 'Changed Counter', 'csv2post' ),
            'datatreatment' => __( 'Data Treatment', 'csv2post' ),
            'parentfileid' => __( 'Parent File', 'csv2post' ),
            'tablename' => __( 'Table', 'csv2post' ),
            'rules' => __( 'Rules', 'csv2post' ),
            //'thesep' => __( 'Separator', 'csv2post' )

        );
                
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            //'post_title'     => array( 'post_title', false ),     //true means it's already sorted
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(

        );
        return $actions;
    }

    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die( 'Items deleted (or they would be if we had items to delete)!' );
        }
        
    }

    function prepare_items_further( $data, $per_page = 5) {
        global $wpdb; //This is used only if making any database queries        
                               
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // in this example I'm going to remove records from the array that do not have the searched string (test string is: 2)
        if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
            $searched_string = wp_unslash( $_GET['s'] );
            foreach( $data as $key => $record_values  ) {
                $match_found = false;
                foreach( $record_values as $example_value ) {
                   if ( strpos( $example_value, $searched_string ) !== FALSE) { // Yoshi version
                        $match_found = true;
                        break;
                   }                
                }    
                
                // if no $match_found remove the current $record_values using the $key
                if( !$match_found ) {
                    unset( $data[ $key ] );    
                }
            }
        }
                
        $this->_column_headers = array( $columns, $hidden, $sortable);

        $this->process_bulk_action();

        $current_page = $this->get_pagenum();

        $total_items = count( $data);

        $data = array_slice( $data,(( $current_page-1)*$per_page), $per_page);

        $this->items = $data;

        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
}
?>