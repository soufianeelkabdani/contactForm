<?php
/**
 * Plugin Name: Contact Form 
 * Plugin URI: https://soufiane.com/
 * Description: A simple contact form made by Soufiane Elkebdani
 * Author: Soufiane Elkebdani
 * Author URI: https://soufiane.ma
 * Text Domain: contact-form
 * Domain Path: /languages/
 * Version: 1
 */

function create_contact_form_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        FirstName varchar(100) NOT NULL,
        LastName varchar(100) NOT NULL,
        Email varchar(255) NOT NULL,
        Subject varchar(1000) NOT NULL,
        Message text NOT NULL,
        sentDate timestamp NOT NULL DEFAULT current_timestamp()
    ) $charset_collate;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}
register_activation_hook( __FILE__, 'create_contact_form_table' );

function delete_contact_form_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_deactivation_hook( __FILE__, 'delete_contact_form_table' );

function contact_form_admin_menu() {
    add_menu_page(
        'Contact Form',        // page title
        'Contact Form',        // menu title
        'manage_options',      // capability
        'contact-form',        // menu slug
        'render_contact_form', // callback function to render the page content
        'dashicons-email',     // icon URL
        2                      // position
    );
}
add_action( 'admin_menu', 'contact_form_admin_menu' );

function render_contact_form() {
    include( plugin_dir_path( __FILE__ ) . 'showData.php' );
}

function bootstrap_cdn_scripts() {
    wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );
    wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js' );
}
add_action( 'wp_enqueue_scripts', 'bootstrap_cdn_scripts' );

function html_form() {
    echo '
    <form class="row g-3" method="POST">
        <div class="col-md-6">
            <label for="FirstName" class="form-label">First Name</label>
            <input type="text" class="form-control" name="FirstName" placeholder="Enter your First Name">
        </div>
        <div class="col-md-6">
            <label for="LastName" class="form-label">Last Name</label>
            <input type="text" class="form-control" name="LastName" placeholder="Enter your Last Name">
        </div>
        <div class="col-12">
            <label for="Email" class="form-label">Email</label>
            <input type="email" class="form-control" name="Email" placeholder="Enter your email">
        </div>
        <div class="col-12">
            <label for="Subject" class="form-label">Subject</label>
            <input type="text" class="form-control" name="Subject" placeholder="Subject">
        </div>
        <div class="col-md-12">
        <label for="Message" class="form-label">Message</label>
        <textarea type="text" class="form-control" name="Message" placeholder="Message"></textarea>
      </div>
      <div class="col-12 gap-2 d-grid">
        <button type="submit" name="formContact" class="btn btn-primary">Send</button>
      </div>
      </form>';
};
function store_mail() {
    if (!isset($_POST['formContact'])) {
      return;
    }
  
    $required_fields = array('FirstName', 'LastName', 'Email', 'Subject', 'Message');
    $missing_fields = array();
  
    foreach ($required_fields as $field) {
      if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
      }
    }
  
    if (!empty($missing_fields)) {
      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
              <p><strong>Missing input fields:</strong> Please fill in the following required fields: ' . implode(', ', $missing_fields) . '.</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
      return;
    }
  
    $first_name = sanitize_text_field($_POST["FirstName"]);
    $last_name  = sanitize_text_field($_POST["LastName"]);
    $email      = sanitize_email($_POST["Email"]);
    $subject    = sanitize_text_field($_POST["Subject"]);
    $message    = esc_textarea($_POST["Message"]);
  
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_form';
    $data = array(
      'FirstName' => $first_name,
      'LastName' => $last_name,
      'Email' => $email,
      'Subject' => $subject,
      'Message' => $message
    );
  
    $wpdb->insert($table_name, $data);
  
    if ($wpdb->insert_id) {
      echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
              <p><strong>Message sent!</strong> Your message has been received. If you have any other questions, don\'t hesitate to contact us again. Thank you.</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    } else {
      echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
              <p><strong>Message failed!</strong> Your message has not been received. Please try again.</p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
  }
  
  
  function shortcode_functions() {
    ob_start();
    store_mail();
    html_form();
    return ob_get_clean();
  }
  
  add_shortcode('Contact_Form', 'shortcode_functions');
  