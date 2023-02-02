<?php

/*
 * Plugin Name: ACF Block Generator
 * Description: Quickly create ACF gutenberg blocks. Settings > ACF Block Generator.
 * Version:     1.0
 * Author:      Adam Wilson
 * Author URI:  https://adamwilson.co.uk
 */


 add_action( 'admin_menu', 'create_block_add_admin_menu' );
 add_action( 'admin_init', 'create_block_settings_init' );
 
 
 function create_block_add_admin_menu() { 
 
     add_options_page( 'ACF Block Generator', 'ACF Block Generator', 'manage_options', 'create_block', 'create_block_options_page' );

 }
 
 
 function create_block_settings_init(  ) { 
 
    register_setting( 'pluginPage', 'create_block_settings' );
 
    add_settings_section(
         'create_block_pluginPage_section', 
         __( '', 'create_block' ), 
         'create_block_settings_section_callback', 
         'pluginPage'
    );
 
    add_settings_field( 
         'block_name', 
         __( 'Block Name*', 'create_block' ), 
         'block_name_render', 
         'pluginPage', 
         'create_block_pluginPage_section' 
    );

    add_settings_field( 
        'block_icon', 
        __( 'Block Icon', 'create_block' ), 
        'block_icon_render', 
        'pluginPage', 
        'create_block_pluginPage_section' 
    );

    add_settings_field( 
        'block_keywords', 
        __( 'Block Keywords', 'create_block' ), 
        'block_keywords_render', 
        'pluginPage', 
        'create_block_pluginPage_section' 
    );

    add_settings_field( 
        'block_category', 
        __( 'Block Category', 'create_block' ), 
        'block_category_render', 
        'pluginPage', 
        'create_block_pluginPage_section' 
    );
 
 }
 
 
 function block_name_render(  ) { 
 
     $options = get_option( 'create_block_settings' );
     ?>
     <input type='text' placeholder='Example Block...' name='create_block_settings[block_name]'>
     <?php
 
 }

 function block_icon_render(  ) { ?>
    <input type='text' placeholder='editor-contract...' name='create_block_settings[block_icon]'>
    <p>From <a href="https://developer.wordpress.org/resource/dashicons/#podio" target="_blank">Dashicons</a></p>
    <?php
}
  
 function block_category_render(  ) { ?>
    <select name='create_block_settings[block_category]'>
        <option value="common">Common</option>
        <option value="formatting">Formatting</option>
        <option value="layout">Layout</option>
        <option value="widgets">Widgets</option>
        <option value="embed">Embed</option>
    </select>
    <?php
}

function block_keywords_render(  ) { ?>
    <input type='text' placeholder='Keyword 1, Keyword 2' name='create_block_settings[block_keywords]'>
    <p>Comma seperated</p>
    <?php
}
 
 
 function create_block_settings_section_callback(  ) { 
    echo __( '<p>Quickly generate an ACF block. Enter your block name like "Example Block".</p>', 'create_block' );
 }

add_action( 'wp_ajax_create_block', 'create_block' );
add_action( 'wp_ajax_nopriv_create_block', 'create_block' );

 function create_block() { 
    $name = $_POST['name'];
    $category = $_POST['category'];
    $icon = $_POST['icon'];
    $keywords = $_POST['keywords'];
    $keys = explode (', ', $keywords);   
    $keys = array_map(function($x){ return '"'.$x.'"'; }, $keys); 

    $slug = sanitize_title($_POST['name']);
    $file_name = '/'.$slug;

    $directory = get_template_directory() .'/blocks'.$file_name;
    wp_mkdir_p($directory);

    $php_file = fopen($directory.$file_name.'.php',"w");
    $sass_file = fopen($directory.$file_name.'.scss',"w");
    $block_file = fopen($directory.'/block.json',"w");

    $content = '{
        "name": "acf/'.$slug.'",
        "title": "'.$name.'",
        "description": "A simple '.strtolower($name).'",
        "category": "'.$category.'",
        "icon": "'.$icon.'",
        "keywords": ['.implode(', ', $keys).'],
        "acf": {
            "mode": "preview",
            "renderTemplate": "'.$slug.'.php"
        },
        "align": "full"
    }';
    fwrite($block_file,$content);
    fclose($block_file);

    wp_die();
 }
 
 
 function create_block_options_page(  ) {  ?>
        <script>
            jQuery(document).ready(function($) {
                $('#submit-form').click(function(e) {
                    e.preventDefault();
                    var name = $('input[name="create_block_settings[block_name]"]').val();
                    var category = $('select[name="create_block_settings[block_category]"]').val();
                    var icon = $('input[name="create_block_settings[block_icon]"]').val();
                    var keywords = $('input[name="create_block_settings[block_keywords]"]').val();

                    if(name != '') {
                        $.ajax({
                            type: 'POST',
                            dataType : "html",
                            url :  '/wp-admin/admin-ajax.php',
                            data: {
                                action: 'create_block',
                                name: name,
                                category: category,
                                icon: icon,
                                keywords: keywords,
                            },
                            success: function (data) {
                                console.log(data);
                                $('.message').html(name+' block created.');
                            },
                            error: function(MLHttpRequest, textStatus, errorThrown){
                                console.log(errorThrown);
                            },
                        });
                    } else {
                        $('.message').html('Please enter a block name.');
                    }
                });
            });
        </script>

         <form action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="create_block">

             <h1>ACF Block Generator</h1>
 
             <?php
             settings_fields( 'pluginPage' );
             do_settings_sections( 'pluginPage' );
             submit_button( __( 'Create Block', 'textdomain' ), 'primary', 'submit-form', true );
             ?>
    
            <div class="message"></div>
         </form>
         <?php
 
 }
 

?>