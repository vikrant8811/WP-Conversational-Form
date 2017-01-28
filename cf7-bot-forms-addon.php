<?php
/*
Plugin Name: Conversational Form Add-on For Contact Form 7
Plugin URI: http://brinyinfoway.com/
Description: This WordPress plugin integrates Contact Form 7 forms into Conversational Form.
Author: Vikrant Dobariya
Version: 1.0
Author URI: http://brinyinfoway.com/

PREFIX: cf7bot (Conversational Form Add-on For Contact Form 7)

*/

// check to make sure contact form 7 is installed and active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {

	function cf7bot_root_url( $append = false ) {
		$base_url = plugin_dir_url( __FILE__ );
		return ($append ? $base_url . $append : $base_url);
	}
	function cf7bot_root_dir( $append = false ) {
		$base_dir = plugin_dir_path( __FILE__ );
		return ($append ? $base_dir . $append : $base_dir);
	}
	include_once( cf7bot_root_dir('inc/constants.php') );
	function cf7bot_enqueue( $hook ) {
    if ( !strpos( $hook, 'wpcf7' ) )
    	return;
    wp_enqueue_style( 'cf7bot-styles',
    	cf7bot_root_url('assets/css/styles.css'),
    	false,
    	cf7BOT_VERSION );
		wp_enqueue_script( 'cf7bot-scripts', cf7bot_root_url('assets/js/scripts.js'), array('jquery'), cf7BOT_VERSION );
        wp_enqueue_style( 'cf7bo-gform', cf7bot_root_url( '/assets/gform/conversational-form.min.css' ), array( 'webflow' ), '1.0' );
        wp_enqueue_script( 'cf7bo-gform', cf7bot_root_url( '/assets/gform/conversational-form.min.js' ), array( 'jquery' ), '1.0', false );
	}
	function cf7bot_enqueue_front( $hook ) {
        wp_enqueue_style( 'cf7bo-gform', cf7bot_root_url( '/assets/gform/conversational-form.min.css' ), array( 'webflow' ), '1.0' );
        wp_enqueue_script( 'cf7bo-gform', cf7bot_root_url( '/assets/gform/conversational-form.min.js' ), array( 'jquery' ), '1.0', false );
	}
	add_action( 'admin_enqueue_scripts', 'cf7bot_enqueue' );
    add_action( 'wpcf7_enqueue_scripts', 'cf7bot_enqueue_front' );
	function cf7bot_admin_panel ( $panels ) {
		$new_page = array(
			'bot-forms-addon' => array(
				'title' => __( 'Bot Form Integration', 'contact-form-7' ),
				'callback' => 'cf7bot_admin_panel_content'
			)
		);
		$panels = array_merge($panels, $new_page);
		return $panels;
	}
	add_filter( 'wpcf7_editor_panels', 'cf7bot_admin_panel' );
	function cf7bot_admin_panel_content( $cf7 ) {
		$post_id = sanitize_text_field($_GET['post']);
        $enabled = get_post_meta($post_id, "_cf7bot_enabled", true);
        $toggle = get_post_meta($post_id, "_cf7bot_toggle", true);
        $form_id = get_post_meta($post_id, "_cf7bot_form_id", true);
        $form_outer = get_post_meta($post_id, "_cf7bot_form_outer", true);
		$form_fields_str = get_post_meta($post_id, "_cf7bot_form_fields", true);
		$form_fields = $form_fields_str ? unserialize($form_fields_str) : false;
		$template = cf7bot_get_view_template('form-fields.tpl.php');
		if($form_fields) {
			$form_fields_html = '';
			$count = 1;
			foreach ($form_fields as $key => $value) {
				$search_replace = array(
					'{first_field}' => ' first_field',
					'{field_name}' => $key,
					'{field_value}' => $value,
					'{add_button}' => '<a href="#" class="button add_field">Add Another Field</a>',
					'{remove_button}' => '<a href="#" class="button remove_field">Remove Field</a>',
				);
				$search = array_keys($search_replace);
				$replace = array_values($search_replace);
				if($count >  1) $replace[0] = $replace[3] = '';				
				if($count == 1) $replace[4] = '';
				$form_fields_html .= str_replace($search, $replace, $template);
				$count++;
			}
		} else {
			$search_replace = array(
				'{first_field}' => ' first_field',
				'{field_name}' => '',
				'{field_value}' => '',
				'{add_button}' => '<a href="#" class="button add_field">Add Another Field</a>',
				'{remove_button}' => '',
			);
			$search = array_keys($search_replace);
			$replace = array_values($search_replace);
			$form_fields_html = str_replace($search, $replace, $template);
		}
		$search_replace = array(
            '{enabled}' => ($enabled == 1 ? ' checked' : ''),
            '{toggle}' => $toggle,
            '{form_id}' => $form_id,
            '{form_outer}' => $form_outer,
			'{form_fields_html}' => $form_fields_html,
		);
		$search = array_keys($search_replace);
		$replace = array_values($search_replace);
		$template = cf7bot_get_view_template('ui-tabs-panel.tpl.php');
		$admin_table_output = str_replace($search, $replace, $template);
		echo $admin_table_output;
	}
	function cf7bot_get_view_template( $template_name ) {
		$template_content = false;
		$template_path = cf7BOT_VIEWS_DIR . $template_name;
		if( file_exists($template_path) ) {
			$search_replace = array(
				"<?php if(!defined( 'ABSPATH')) exit; ?>" => '',
				"{plugin_url}" => cf7bot_root_url(),
				"{site_url}" => get_site_url(),
			);
			$search = array_keys($search_replace);
			$replace = array_values($search_replace);
			$template_content = str_replace($search, $replace, file_get_contents( $template_path ));
		}
		return $template_content;
	}
	function cf7bot_admin_save_form( $cf7 ) {
		$post_id = sanitize_text_field($_GET['post']);
		$form_fields = array();
		foreach ($_POST['cf7bot_hs_field'] as $key => $value) {
			if($_POST['cf7bot_cf7_field'][$key] == '' && $value == '') continue;
			$form_fields[$value] = wp_unslash($_POST['cf7bot_cf7_field'][$key]);
		}
        //print_r(serialize($form_fields)); exit;
        update_post_meta($post_id, '_cf7bot_enabled', $_POST['cf7bot_enabled']);
        update_post_meta($post_id, '_cf7bot_toggle', $_POST['cf7bot_toggle']);
        update_post_meta($post_id, '_cf7bot_form_id', $_POST['cf7bot_form_id']);
        update_post_meta($post_id, '_cf7bot_form_outer', $_POST['cf7bot_form_outer']);
		update_post_meta($post_id, '_cf7bot_form_fields', serialize($form_fields));

	}
	add_action('wpcf7_save_contact_form', 'cf7bot_admin_save_form');

	function wpcf7_form_response_output($output, $class, $content, $WPCF7_ContactForm){

        $post_id = $WPCF7_ContactForm->id();

        $enabled = get_post_meta($post_id, "_cf7bot_enabled", true);
        $toggle = get_post_meta($post_id, "_cf7bot_toggle", true);
        /*if($toggle == '')
            $toggle = '#toggle-conversation';*/
        $form_id = get_post_meta($post_id, "_cf7bot_form_id", true);
        if($form_id == '')
            $form_id = 'conversational';

        $form_outer = get_post_meta($post_id, "_cf7bot_form_outer", true);

        $form_fields_str = get_post_meta($post_id, "_cf7bot_form_fields", true);
        $form_fields = $form_fields_str ? unserialize($form_fields_str) : false;

        if($enabled && count($form_fields) > 0){
        ob_start();
        ?>
        <script>
            (function($){
                $(document).ready(function(){
                    $questions = <?php echo json_encode($form_fields); ?>;
                    $.each($questions,function(k,v){
                        var input = $('[name="'+k+'"]');
                        if(input.length > 0){
                            input.attr('cf-questions', v);
                            if(input.hasClass('wpcf7-validates-as-required') == true)
                                input.attr('required', '');
                            if(input.attr('type') == 'email')
                                input.attr('pattern', '^\\w+@[a-zA-Z_]+?\\.[a-zA-Z]{2,3}$');
                        }
                    })
                    setTimeout(function() {
                        <?php if($toggle != ''): ?>
                        $(document).on("click", "<?php echo $toggle; ?>", function(){
                            if(!window.ConversationalForm){
                                window.ConversationalForm = new cf.ConversationalForm({
                                    formEl: document.getElementById("<?php echo $form_id; ?>"),
                                    <?php if($form_outer == ''): ?>
                                    context: document.getElementById('test').parentElement,
                                    <?php else: ?>
                                    context: document.getElementById(<?php echo $form_outer ?>),
                                    <?php endif; ?>
                                    userImage: "<?php echo cf7bot_root_url('assets//images/human.png'); ?>"
                                });
                            }
                            $(this).addClass("disabled");
                            var form = $(".conversational-form");
                            if (form.hasClass("conversational-form--show") === true) {
                                $(this).removeClass("active");
                                form.removeClass("conversational-form--show");
                            } else {
                                $(this).addClass("active");
                                form.addClass("conversational-form--show");
                                window.ConversationalForm.userInput.setFocusOnInput()
                            }
                            $(this).removeClass("disabled");
                        });
                        <?php else: ?>
                        if(!window.ConversationalForm){
                            window.ConversationalForm = new cf.ConversationalForm({
                                formEl: document.getElementById("<?php echo $form_id; ?>"),
                                <?php if($form_outer == ''): ?>
                                context: document.getElementById('test').parentElement,
                                <?php else: ?>
                                context: document.getElementById(<?php echo $form_outer ?>),
                                <?php endif; ?>
                                userImage: "<?php echo cf7bot_root_url('assets//images/human.png'); ?>"
                            });
                        }
                        <?php endif; ?>
                    }, 200);
                })
            })(jQuery)
        </script>
        <?php
            $html = ob_get_contents();
            ob_clean();
            return $output.$html;
        }
        return $output;
    }
    add_filter('wpcf7_form_response_output', 'wpcf7_form_response_output',11,4);

}
