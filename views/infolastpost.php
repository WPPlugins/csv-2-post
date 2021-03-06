<?php
/**
 * Last Post Created for the current project (not all projects)
 *
 * @package CSV 2 POST
 * @subpackage Views
 * @author Ryan Bayne   
 * @since 8.1.3
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class CSV2POST_Infolastpost_View extends CSV2POST_View {

    /**
     * Number of screen columns for post boxes on this screen
     *
     * @since 8.1.3
     *
     * @var int
     */
    protected $screen_columns = 1;
    
    protected $view_name = 'infolastpost';
    
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
            array( $this->view_name . '-generalpostsettings', __( 'General', 'csv2post' ), array( $this, 'parent' ), 'side','default',array( 'formid' => 'generalpostsettings' ), true, 'activate_plugins' ),
            array( $this->view_name . '-lastpostcontent', __( 'Content', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'lastpostcontent' ), true, 'activate_plugins' ),
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
                          
            // query the last post
            $result = $this->DB->selectorderby( $this->DB->get_project_main_table( $csv2post_settings['currentproject'] ), 'c2p_postid != 0', 'c2p_applied', 'c2p_postid',1);
            if($result){
                
                $this->lastpost = get_post( $result[0]->c2p_postid);    
            
            }else{
                $this->add_meta_box( 'lastpost-nopostscreated', __( 'No Posts Created', 'csv2post' ), array( $this, 'parent' ), 'normal','default',array( 'formid' => 'nopostscreated' ) );      
            }

        } else {
            $this->add_meta_box( 'lastpost-nocurrentproject', __( 'No Current Project', 'csv2post' ), array( $this->UI, 'metabox_nocurrentproject' ), 'normal','default',array( 'formid' => 'nocurrentproject' ) );      
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
        $main_title = __( 'Last Post Introduction', 'csv2post' );
        
        $intro = __( 'View the latest post created by the current active
        campaign. This can be quicker
        than checking on the front-end of your site especially if posts
        are also being made manually or multiple projects are running
        automatically.', 'csv2post' );
        
        $title = false;//__( 'More Information', 'csv2post' );
        
        $info = false;//__( '<ol><li>Tutorials Coming Soon</li></ol>', 'csv2post' );
        
        $foot = false;//__( 'Get your tutorial link added to this list. Video, blog, forum and PDF documents accepted.', 'csv2post' );
        
        $this->UI->intro_box_dismissible( 'lastpost-introduction', $main_title, $intro, $info_area = true, $title, $info, $foot );               
    }
       
    /**
    * post box function
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_infolastpost_generalpostsettings( $data, $box ) {    
        echo '<ul>';
        echo '<li>Post ID: '        . $this->lastpost->ID . '</li>';
        echo '<li>Post Author: '    . $this->lastpost->post_author . '</li>';
        echo '<li>Post Date: '      . $this->lastpost->post_date . '</li>';
        echo '<li>Post Date GMT: '  . $this->lastpost->post_date_gmt . '</li>';
        echo '<li>Post Title: '     . $this->lastpost->post_title . '</li>';
        echo '<li>Post Excerpt: '   . $this->lastpost->post_excerpt . '</li>';
        echo '<li>Post Status: '    . $this->lastpost->post_status . '</li>';
        echo '<li>Comment Status: ' . $this->lastpost->comment_status . '</li>';
        echo '<li>Ping Status: '    . $this->lastpost->ping_status . '</li>';
        echo '<li>Post Password: '  . $this->lastpost->post_password . '</li>';
        echo '<li>Post Name: '      . $this->lastpost->post_name . '</li>';
        echo '<li>Post Parent: '    . $this->lastpost->post_parent . '</li>';
        echo '<li>Post Type: '      . $this->lastpost->post_type . '</li>';
        echo '<li>Comment Count: '  . $this->lastpost->comment_count . '</li>';
        echo '</ul>'; 
    }
  
    /**
    * post box function
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_infolastpost_lastpostcontent( $data, $box ) {    
        echo $this->lastpost->post_content;
    }     
    
    /**
    * post box function
    * 
    * @author Ryan Bayne
    * @package CSV 2 POST
    * @since 8.1.3
    * @version 1.1
    */
    public function postbox_infolastpost_nopostscreated( $data, $box ) {    
        echo '<p>' . __( 'Your current project has not been used to create any posts. When you create your first post using the current project. This box will be hidden and others will appear.', 'csv2post' ) . '</p>';
    }         
    
}?>