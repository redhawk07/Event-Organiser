<?php
 /*
* add's custom taxonomies (categories and tags) and then custom post type 'event'.
*/ 

//Register the custom taxonomy Event-category
add_action( 'init', 'eventorganiser_create_event_taxonomies', 10 );
function eventorganiser_create_event_taxonomies() {

$eventorganiser_option_array = get_option('eventorganiser_options'); 

$cat_slug = (empty($eventorganiser_option_array['url_cat']) ? 'events/category' : trim($eventorganiser_option_array['url_cat'], "/"));
$tag_slug = (empty($eventorganiser_option_array['url_tag']) ? 'events/category' : trim($eventorganiser_option_array['url_tag'], "/"));

  // Add new taxonomy, make it hierarchical (like categories)
  $category_labels = array(
    'name' => __('Event Categories', 'eventorganiser'),
    'singular_name' => _x( 'Category', 'taxonomy singular name'),
    'search_items' =>  __( 'Search Categories' ),
    'all_items' => __( 'All Categories' ),
    'parent_item' => __( 'Parent Category' ),
    'parent_item_colon' => __( 'Parent Category' ).':',
    'edit_item' => __( 'Edit Category' ), 
    'update_item' => __( 'Update Category' ),
    'add_new_item' => __( 'Add New Category' ),
    'new_item_name' => __( 'New Category Name' ),
	'not_found' =>  __('No categories found'),
    'menu_name' => __( 'Categories' ),
  ); 	

register_taxonomy('event-category',array('event'), array(
	'hierarchical' => true,
	'labels' => $category_labels,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
	'query_var' => true,
	'capabilities'=>array(
		'manage_terms' => 'manage_event_categories',
		'edit_terms' => 'manage_event_categories',
		'delete_terms' => 'manage_event_categories',
		'assign_terms' =>'edit_events'),
	'public'=> true,
	'rewrite' => array( 'slug' =>$cat_slug, 'with_front' => false )
  ));

if(isset($eventorganiser_option_array['eventtag']) && $eventorganiser_option_array['eventtag']==1):
  // Add new taxonomy, make it non-hierarchical (like tags)
  $tag_labels = array(
     'name' => __('Event Tags','eventorganiser'),
    'singular_name' => _x( 'Tag', 'taxonomy singular name'),
    'search_items' =>  __( 'Search Tags'),
    'all_items' => __( 'All Tags'),
    'popular_items' => __( 'Popular Tags'),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Tag'),
    'update_item' => __( 'Update Tag'),
    'add_new_item' => __( 'Add New Tag'),
    'new_item_name' => __( 'New Tag Name'),
	'not_found' =>  __('No tags found'),
    'choose_from_most_used' => __( 'Choose from the most used tags' ),
    'menu_name' => __( 'Tags' ),
    'add_or_remove_items' => __( 'Add or remove tags' ),
    'separate_items_with_commas' => __( 'Separate tags with commas' )
  ); 	

register_taxonomy('event-tag',array('event'), array(
    'hierarchical' => false,
	'labels' => $tag_labels,
	'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
	'query_var' => true,
	'capabilities'=>array(
		'manage_terms' => 'manage_event_categories',
		'edit_terms' => 'manage_event_categories',
		'delete_terms' => 'manage_event_categories',
		'assign_terms' =>'edit_events'),
	'public'=> true,
	'rewrite' => array( 'slug' => $tag_slug, 'with_front' => false )
  ));
endif;
}


//Register the custom post type Event
add_action('init', 'eventorganiser_cpt_register');
function eventorganiser_cpt_register() {
$eventorganiser_option_array = get_option('eventorganiser_options'); 
  	$labels = array(
		'name' => __('Events','eventorganiser'),
		'singular_name' => __('Event','eventorganiser'),
		'add_new' => _x('Add New','post'),
		'add_new_item' => __('Add New Event','eventorganiser'),
		'edit_item' =>  __('Edit Event','eventorganiser'),
		'new_item' => __('New Event','eventorganiser'),
		'all_items' =>__('All events','eventorganiser'),
		'view_item' =>__('View Event','eventorganiser'),
		'search_items' =>__('Search events','eventorganiser'),
		'not_found' =>  __('No events found','eventorganiser'),
		'not_found_in_trash' =>  __('No events found in Trash','eventorganiser'),
		'parent_item_colon' => '',
		'menu_name' => __('Events','eventorganiser'),
  );

$exclude_from_search = ($eventorganiser_option_array['excludefromsearch']==0) ? false : true;
$event_slug = (empty($eventorganiser_option_array['url_event']) ? 'events/event' : $eventorganiser_option_array['url_event']);

$args = array(
	'labels' => $labels,
	'public' => true,
	'publicly_queryable' => true,
	'exclude_from_search'=>$exclude_from_search,
	'show_ui' => true, 
	'show_in_menu' => true, 
	'query_var' => true,
	'capability_type' => 'event',
	'rewrite' => array(
		'slug'=> $event_slug,
		'with_front'=> false,
		'feeds'=> true,
		'pages'=> true
	),		
	'capabilities' => array(
		'publish_posts' => 'publish_events',
		'edit_posts' => 'edit_events',
		'edit_others_posts' => 'edit_others_events',
		'delete_posts' => 'delete_events',
		'delete_others_posts' => 'delete_others_events',
		'read_private_posts' => 'read_private_events',
		'edit_post' => 'edit_event',
		'delete_post' => 'delete_event',
		'read_post' => 'read_event',
	),
	'has_archive' => true, 
	'hierarchical' => false,
	'menu_icon' => EVENT_ORGANISER_URL.'css/images/eoicon-16.png',
	'menu_position' => 5,
	'supports' => $eventorganiser_option_array['supports']
  ); 
  register_post_type('event',$args);
}

//add filter to ensure the text event, or event, is displayed when user updates a event 
add_filter('post_updated_messages', 'eventorganiser_messages');
function eventorganiser_messages( $messages ) {
	global $post, $post_ID;

	$messages['event'] = array(
    		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Event updated. <a href="%s">View event</a>'), esc_url( get_permalink($post_ID) ) ),
		2 => __('Custom field updated.'),
		3 => __('Custom field deleted.'),
		4 => __('Event updated.','eventorganiser'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s','eventorganiser'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Event published. <a href="%s">View event</a>','eventorganiser'), esc_url( get_permalink($post_ID) ) ),
		7 => __('Event saved.'),
		8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>','eventorganiser'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>','eventorganiser'),
		 // translators: Publish box date format, see http://php.net/date
      		date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>','eventorganiser'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  	);
	return $messages;
}



//Meta capabilities for post type event
add_filter( 'map_meta_cap', 'eventorganiser_event_meta_cap', 10, 4 );
function eventorganiser_event_meta_cap( $caps, $cap, $user_id, $args ) {

	/* If editing, deleting, or reading a event, get the post and post type object. */
	if ( 'edit_event' == $cap || 'delete_event' == $cap || 'read_event' == $cap ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post->post_type );	

		/* Set an empty array for the caps. */
		$caps = array();
		if($post_type!='event');
			return $caps;
	}

	/* If editing a event, assign the required capability. */
	if ( 'edit_event' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[] = $post_type->cap->edit_posts;
		else
			$caps[] = $post_type->cap->edit_others_posts;
	}

	/* If deleting a event, assign the required capability. */
	elseif ( 'delete_event' == $cap ) {
		if (isset($post->post_author ) && $user_id == $post->post_author)
			$caps[] = $post_type->cap->delete_posts;
		else
			$caps[] = $post_type->cap->delete_others_posts;
	}

	/* If reading a private event, assign the required capability. */
	elseif ( 'read_event' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[] = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[] = 'read';
		else
			$caps[] = $post_type->cap->read_private_posts;
	}

	/* Return the capabilities required by the user. */
	return $caps;
}


// Rewrite rules for venues page
add_action('generate_rewrite_rules', 'eventorganiser_create_rewrite_rules');
function eventorganiser_create_rewrite_rules() {
	global $wp_rewrite;
 
	// add rewrite tokens
	$keytag = '%venue%';
	$wp_rewrite->add_rewrite_tag($keytag, '(.+?)', 'post_type=event&venue=');

	$eventorganiser_option_array = get_option('eventorganiser_options'); 
	$venue_slug = (empty($eventorganiser_option_array['url_venue']) ? 'events/venue' : trim($eventorganiser_option_array['url_venue'], "/"));
	
	$keywords_structure = $wp_rewrite->root . $venue_slug."/$keytag/";
	$keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure);
 
	$wp_rewrite->rules = $keywords_rewrite + $wp_rewrite->rules;
	return $wp_rewrite->rules;
}


// This adds the Event Organiser icon to the page head
add_action('admin_head', 'eventorganiser_plugin_header_image');
function eventorganiser_plugin_header_image() {
        global $post_type;

	if ((isset($_GET['post_type']) && $_GET['post_type'] == 'event') || ($post_type == 'event')) : ?>
	<style>
	#icon-edit { background:transparent url('<?php echo EVENT_ORGANISER_URL.'/css/images/eoicon-32.png';?>') no-repeat; }		
        </style>
	<?php endif; 
}

// Filter wp_nav_menu() to add event link if selected in options
 add_filter( 'wp_list_pages', 'eventorganiser_menu_link' );
add_filter( 'wp_nav_menu_items', 'eventorganiser_menu_link' );
function eventorganiser_menu_link($items) {
	global $wp_query;
	$eo_settings_array= get_option('eventorganiser_options');
	if(!$eo_settings_array['addtomenu'])
		return $items;

	$title = (isset($eo_settings_array['navtitle']) ? $eo_settings_array['navtitle'] : 'Events');
	$class ='menu-item menu-item-type-event';
	if(isset($wp_query->query_vars['post_type'])&&$wp_query->query_vars['post_type']=='event') $class = 'current_page_item';
		$eventlink = '<li class="'.$class.'"><a href="'.EO_Event::link_structure().'">'.$title.'</a></li>';
		$items = $items . $eventlink;
	return $items;
}

/*
 * Add contextual help
*/
add_action( 'contextual_help', 'eventorganiser_cpt_help_text', 10, 3 );
function eventorganiser_cpt_help_text($contextual_help, $screen_id, $screen) { 
	//The add_help_tab function for screen was introduced in WordPress 3.3
	if(method_exists($screen, 'add_help_tab')):
	switch($screen->id):
		//Add help for event editing / creating page
		case ('event'):
			    $screen->add_help_tab( array(
			        'id'      => 'creating-events', 
			        'title'   => __('Creating events','eventorganiser'),
        			'content' => '<p>' . __('Creating events:','eventorganiser') . '</p>'.
			'<ul>' .
				'<li>' . __('The start date is the date the event starts. If the event is a reoccuring event, this is the start date of the first occurrence.','eventorganiser') . '</li>' .
				'<li>' . __('The end date is the date the event finishes. If the event is a reoccuring event, this is the end date of the first occurrence.','eventorganiser') . '</li>' .
				'<li>' . __('All dates and times must be entered in the specified format. This format can changed in the settings page.','eventorganiser') . '</li>' .
			'</ul>'
				));
			    $screen->add_help_tab( array(
			        'id'      => 'repeating-events',
			        'title'   => __('Repeating events','eventorganiser'),
        			'content' => '<p>' . __('To repeat an event according to some regular pattern, use the reocurrence dropdown menu to select how the event is to repeat. Further options then appear, ','eventorganiser') . '</p>' .
			'<ul>' .
				'<li>' . __('Specify how regularly the event should repeat (default 1)','eventorganiser') . '</li>' .
				'<li>' . __('Choose the reoccurrence end date. No further occurrences are added after this date, but an occurrence that starts before may finish after this date.','eventorganiser') . '</li>' .
				'<li>' . __('If monthly reoccurrence is selected, select whether this should repeat on that date of the month (e.g. on the 24th) or on the day of the month (e.g. on the third Tuesday) ','eventorganiser') . '</li>' .
				'<li>' . __('If weekly reoccurrence is selected, select which days of the week the event should be repeated. If no days are selected, the day of the start date is used','eventorganiser') . '</li>' .
			'</ul>'
				));
			    $screen->add_help_tab( array(
			        'id'      => 'selecting-venues', 
			        'title'   => __('Selecting a venue','eventorganiser'),
        			'content' => '<p>' . __('Selecting a venue','eventorganiser') . '</p>' .
					'<ul>' .
						'<li>' . __('Use the venues input field to search for existing venues','eventorganiser') . '</li>' .
						'<li>' . __('Only pre-existing venues can be selected. To add a venue, go to the venues page.','eventorganiser') . '</li>' .
					'</ul>'
				));
			break;

		//Add help for event admin table page
		case ('edit-event'):

			$screen->add_help_tab( array(
				'id'=>'overview',
			        'title'   => __('Overview'),
				'content'=>'<p>' . __('This is the list of all saved events. Note that <strong> reoccurring events appear as a single row </strong> in the table and the start and end date refers to the first occurrence of that event.','eventorganiser') . '</p>' ));
			break;

		//Add help for venue admin table page
		case ('event_page_venues'):
			$contextual_help = 
			'<p>' . __("Hovering over a row in the venues list will display action links that allow you to manage that venue. You can perform the following actions:",'eventorganiser') . '</p>' .
			'<ul>' .
				'<li>' . __('Edit takes you to the editing screen for that venue. You can also reach that screen by clicking on the venue title.','eventorganiser') . '</li>' .
				'<li>' . __('Delete will permanently remove the venue','eventorganiser') . '</li>' .
				'<li>' . __("View will take you to the venue's page",'eventorganiser') . '</li>' .
			'</ul>';
			break;

		//Add help for calendar view
		case ('event_page_calendar'):
			$screen->add_help_tab( array(
				'id'=>'overview',
				'title'=>__('Overview'),
				'content'=>'<p>' . __("This page shows all (occurrances of) events. You can view the summary of an event by clicking on it. If you have the necessary permissions, a link to the event's edit page will appear also.",'eventorganiser'). '</p>' .
			'<p>' . __("By clicking the relevant tab, you can view events in Month, Week or Day mode. You can also filter the events by events by category and venue. The 'go to date' button allows you to quickly jump to a specific date.",'eventorganiser'). '</p>' 
			));
			$screen->add_help_tab( array(
				'id'=>'add-event',
				'title'=>__('Add Event','eventorganiser'),
				'content'=>'<p>' . __("You can create an event on this Calendar, by clicking on day or dragging over multiple days (in Month view) or multiple times (in Week and Day view). You can give the event a title, specify a venue and provide a descripton. The event can be immediately published or saved as a draft. In any case, the event is created and you are forwarded to that event's edit page.",'eventorganiser') . '</p>' ));
			break;
	endswitch;

	//Add a link to Event Organiser documentation on every page
	$screen->set_help_sidebar( '<p> <strong>'. __('For more information','eventorganiser').'</strong> </p><p>'.sprintf(__('See the <a %s> documentation</a>','eventorganiser'),'target="_blank" href="http://www.harriswebsolutions.co.uk/event-organiser/documentation/"').'</p>' );
	endif;

	return $contextual_help;
}

/*
* The following adds the ability to associate a colour with an event-category.
* Currently stores data in the options table/
* If Taxonomy meta table becomes core, then these options will be migrated there.
*/

//Enqueue the javascript necessary for colour-picker.
add_action( 'admin_menu', 'eventorganiser_colour_scripts' );
function eventorganiser_colour_scripts() {
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );
    wp_enqueue_script( 'jQuery' );
}

// Save the taxonomy meta on creation or edit
add_action('created_event-category', 'eventorganiser_save_event_cat_meta', 10, 2);
add_action( 'edited_event-category', 'eventorganiser_save_event_cat_meta', 10, 2);
function eventorganiser_save_event_cat_meta( $term_id ) {
	if ( isset( $_POST['eo_term_meta'] ) ):
		$term_meta = get_option( "eo-event-category_$term_id");
		$cat_keys = array_keys($_POST['eo_term_meta']);

		foreach ($cat_keys as $key):
			if (isset($_POST['eo_term_meta'][$key]))
				$term_meta[$key] = $_POST['eo_term_meta'][$key];
		endforeach;

	        //save the option array
	        update_option( "eo-event-category_$term_id", $term_meta );
	endif;
}

add_action('delete_event-category','eventorganiser_tax_term_deleted',10,2);
function eventorganiser_tax_term_deleted($term_id, $tt_id){
	//Delete taxonomies meta
	delete_option('eo-event-category_'.$term_id);
}


/*
* Add the colour picker forms to main taxonomy page: (This one needs stuff wrapped in Divs)
* uses eventorganiser_tax_meta_form to display the guts of the form.
* @uses eventorganiser_tax_meta_form 
*/
add_action('event-category_add_form_fields', 'eventorganiser_add_tax_meta',10,1);
function eventorganiser_add_tax_meta($taxonomy){
	?>

	<div class="form-field"><?php eventorganiser_tax_meta_form('');?></div>
	<p> &nbsp; </br>&nbsp; </p>
<?php
}


/*
*Add the colour picker forms to taxonomy-edit page: (This one needs stuff wrapped in rows)
* uses eventorganiser_tax_meta_form to display the guts of the form.
* @uses eventorganiser_tax_meta_form
*/
add_action( 'event-category_edit_form_fields', 'eventorganiser_edit_tax_meta', 10, 2);
function eventorganiser_edit_tax_meta($term,$taxonomy){
	//Check for existing data
	$term_meta = get_option( "eo-event-category_$term->term_id");
	$colour = (!empty($term_meta) && isset($term_meta['colour']) ? $term_meta['colour'] : '');
	?>
	<tr class="form-field"><?php eventorganiser_tax_meta_form($colour);?></tr>
<?php
}

/*
* Displays the guts of the taxonomy-meta form.
*/
function eventorganiser_tax_meta_form($colour){
	?>
		<th>
			<label for="tag-description"><?php _e('Color','eventorganiser')?></label>
		</th>
		<td> 
			<input type="text" style="width:100px" name="eo_term_meta[colour]" class="color colour-input" id="color" value="<?php echo $colour; ?>" />
			<a id="link-color-example" class="color  hide-if-no-js" style="border: 1px solid #DFDFDF;border-radius: 4px 4px 4px 4px;margin: 0 7px 0 3px;padding: 4px 14px;"></a>
   			 <div style="z-index: 100; background: none repeat scroll 0% 0% rgb(238, 238, 238); border: 1px solid rgb(204, 204, 204); position: absolute;display: none;" id="colorpicker"></div>
			<p><?php _e('Assign the category a colour.','eventorganiser')?></p>
		</td>
	<script>
var farbtastic;(function($){var pickColor=function(a){farbtastic.setColor(a);$('.colour-input').val(a);$('a.color').css('background-color',a)};$(document).ready(function(){farbtastic=$.farbtastic('#colorpicker',pickColor);pickColor($('.colour-input').val());$('.color').click(function(e){e.preventDefault();console.log($('#colorpicker').is(":visible"));if($('#colorpicker').is(":visible")){$('#colorpicker').hide()}else{$('#colorpicker').show()}});$('.colour-input').keyup(function(){var a=$('.colour-input').val(),b=a;a=a.replace(/[^a-fA-F0-9]/,'');if('#'+a!==b)$('.colour-input').val(a);if(a.length===3||a.length===6)pickColor('#'+a)});$(document).mousedown(function(){$('#colorpicker').hide()})})})(jQuery);
	</script>	
<?php
}
?>
