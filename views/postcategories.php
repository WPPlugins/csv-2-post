<?php
/**
 * Category Columns [page]   
 *
 * Columns with category terms are selected on this page
 * 
 * @package CSV 2 POST
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class CSV2POST_Postcategories_View extends CSV2POST_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 1;
    
    protected $view_name = 'postcategories';
    
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
            array( $this->view_name . '-categorydata', __( 'Category Data', 'csv2post' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'categorydata' ), true, 'activate_plugins' ),
            array( $this->view_name . '-categorydescriptions', __( 'Category Descriptions', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'categorydescriptions' ), true, 'activate_plugins' ),
            array( $this->view_name . '-categorycreation', __( 'Category Creation', 'csv2post' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'categorycreation' ), true, 'activate_plugins' ),
            array( $this->view_name . '-setpostscategories', __( 'Set Posts Categories', 'csv2post' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'setpostscategories' ), true, 'activate_plugins' ),
            //array( $this->view_name . '-resetpostscategories', __( 'Re-Set Posts Categories', 'csv2post' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'resetpostscategories' ), true, 'activate_plugins' ),
        
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
            
            if(!empty( $this->current_project_settings['categories'] ) ){
                $this->add_meta_box( 'columns-presetlevelonecategory', __( 'Pre-Set Level One Category', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'presetlevelonecategory' ) );      
                $this->add_meta_box( 'columns-categorypairing', __( 'Category Mapping/Pairing', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'categorypairing' ) );      
            }
            
        } else {
            $this->add_meta_box( 'columns-nocurrentproject', __( 'No Current Project', 'csv2post' ), array( $this->UI, 'metabox_nocurrentproject' ), 'normal','default',array( 'formid' => 'nocurrentproject' ) );      
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
    * @version 1.0
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
    * @version 1.1
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
        $main_title = __( 'Category Columns Introduction', 'csv2post' );
        
        $intro = __( 'Mass create categories. There are many approaches
        possible. Some .csv files have category data in a single column
        and others have it split into multiple columns. Whatever your
        requirement WebTechGlobal can help.', 'csv2post' );
        
        $title = false;//__( 'More Information', 'csv2post' );
        
        $info = false;//__( '<ol><li>Tutorials Coming Soon</li></ol>', 'csv2post' );
        
        $foot = false;//__( 'Get your tutorial link added to this list. Video, blog, forum and PDF documents accepted.', 'csv2post' );
        
        $this->UI->intro_box_dismissible( 'categorycolumns-introduction', $main_title, $intro, $info_area = true, $title, $info, $foot );               
    }                                                                                 
        
    /**
    * post box function for category column selection
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.0
    */
    public function postbox_postcategories_categorydata( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Select your columns of categories (term data) in order that your categories need to be in as a hierarchy.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        
        global $csv2post_settings;
        ?>  

            <table class="form-table">
            <?php
            for( $i=0;$i<=4;$i++){
                $default_table = false;
                $default_column = false;
                if( isset( $this->current_project_settings['categories']['data'][$i] ) ){
                    $default_table = $this->current_project_settings['categories']['data'][$i]['table'];
                    $default_column = $this->current_project_settings['categories']['data'][$i]['column'];                
                }
                $level_label = $i + 1;
                $this->UI->option_projectcolumns_categoriesmenu( __( "Level $level_label"), $csv2post_settings['currentproject'], "categorylevel$i", "categorylevel$i", $default_table, $default_column, 'notselected', 'Not Selected' );
            }
            
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }    
    
    /**
    * Category description template setup.
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.0
    */
    public function postbox_postcategories_categorydescriptions( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Paste one or more column replacement tokens to build a category descriptions template.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );     
        
        global $csv2post_settings;

        if(empty( $this->current_project_settings['categories']['data'] ) ){
            
            _e( 'Please select your level 1 category data on the Category Data form.', 'csv2post' );
                
        }else{ 
        ?>

            <table class="form-table">
            <?php
            foreach( $this->current_project_settings['categories']['data'] as $key => $catarray ){

                // or create a template
                $current_value = '';

                if( isset( $this->current_project_settings['categories']['meta'][$key] ) ){
                    $current_value = $this->current_project_settings['categories']['meta'][$key]['description'];
                }
                 
                $level_label = $key + 1;
                $this->UI->option_textarea( "Level $level_label Description", 'level'.$key.'description', 'level'.$key.'description',10,30, $current_value); 
            }
            ?>
            </table>
        
        <?php 
            $this->UI->postbox_content_footer();
        }
    }    
    
    /**
    * Form for selecting a pre-set level one category.
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.0
    */
    public function postbox_postcategories_presetlevelonecategory( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Select a category to be the parent over all of the categories you plan to create. Only use if all of our imported records belong under that main category.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
        ?>  

            <table class="form-table">
            <?php 
            $presetid = '';
            
            if( isset( $this->current_project_settings['categories']['presetcategoryid'] ) ){
                $presetid = $this->current_project_settings['categories']['presetcategoryid'];
            }
            
            $this->UI->option_text_simple( 'Category ID', 'presetcategoryid', $presetid, true);
            ?>
            </table>
        
        <?php 
        $this->UI->postbox_content_footer();
    }
 
    /**
    * Category pairing form
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.0
    */
    public function postbox_postcategories_categorypairing( $data, $box ) {    
        if(!isset( $this->current_project_settings['categories']['data'] ) ){
            $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], 'You have not selected your category columns.', false );    
        }else{
            $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], false, false);
            $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] );
            
            global $wpdb;
            ?>

                <table class="form-table">
                <?php
                foreach( $this->current_project_settings['categories']['data'] as $key => $catarray ){

                    $column = $catarray['column'];
                    $table = $catarray['table'];
                    $current_category_id = 'nocurrentcategoryid';
                                        
                    $distinct_result = $wpdb->get_results( 
                        
                        "SELECT DISTINCT $column
                        FROM $table"
                        
                    ,ARRAY_A);
                    
                    foreach( $distinct_result as $key => $value ){
                        $distinctval_cleaned = $this->PHP->clean_string( $value[$column] );
                        $nameandid = 'distinct'.$table.$column.$distinctval_cleaned;
                        ?>
                        
                        <tr valign="top">
                            <th scope="row">
                                <input type="text" name="<?php echo $column .'#'. $table .'#' . $distinctval_cleaned;?>" id="<?php echo $nameandid;?>" value="<?php echo $value[$column];?>" title="<?php echo $column;?>" class="csv2post_inputtext" readonly> 
                            </th>
                            <td>
                                <select name="existing<?php echo $distinctval_cleaned;?>" id="existing<?php echo $distinctval_cleaned;?>">
                                    
                                    <option value="notselected">Not Required</option> 
                                    <?php $cats = get_categories( 'hide_empty=0&echo=0&show_option_none=&style=none&title_li=' );?>
                                    <?php         
                                    foreach( $cats as $c ){ 
    
                                        $selected = '';
                                        if( isset( $this->current_project_settings['categories']['mapping'][$table][$column][ $value[$column] ] ) ){  
                                            $current_category_id = $this->current_project_settings['categories']['mapping'][$table][$column][ $value[$column] ];        
                                            if( $current_category_id === $c->term_id ) {
                                                $selected = ' selected="selected"';
                                            }                                        
                                        }
                  
                                        echo '<option value="'.$c->term_id.'"'.$selected.'>'. $c->name . ' - ' . $c->term_id . '</option>'; 
                                    }?>
                                                                                                                                                                                 
                                </select> 
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </table>

         <?php }?> 
        
        <?php 
        $this->UI->postbox_content_footer();
    } 
     
    /**
    * post box function for category creation
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_postcategories_categorycreation( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Once you have selected your category columns you can initiate category creation using this form.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] ); 
        $this->UI->postbox_content_footer();
    }      
    
    /**
    * post box function for category updating
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_postcategories_setpostscategories( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Does not create categories. Submitting this form will put posts by CSV 2 POST into categories based on your category column settings.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] ); 
        $this->UI->postbox_content_footer();
    }
        
    /**
    * post box function for resetting post categories by removing the relationship
    * and putting all posts into Uncategorized
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_postcategories_resetpostscategories( $data, $box ) {    
        $this->UI->postbox_content_header( $box['title'], $box['args']['formid'], __( 'Another user requested feature. This one removes all of the current projects posts from their categories. They will end up in your WP blogs default category.', 'csv2post' ), false );        
        $this->FORMS->form_start( $box['args']['formid'], $box['args']['formid'], $box['title'] ); 
        $this->UI->postbox_content_footer();
    }
 
}?>