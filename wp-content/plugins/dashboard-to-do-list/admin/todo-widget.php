<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !function_exists( 'ardtdw_widgetsetup' ) ) {
function ardtdw_widgetsetup() {
	wp_add_dashboard_widget('ardtdw', 'Website To-Do List', 'ardtdw_widget');
}
add_action('wp_dashboard_setup', 'ardtdw_widgetsetup');
}

if ( !function_exists( 'ardtdw_widgetupdate' ) ) {
function ardtdw_widgetupdate(){
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		if( isset($_POST['ardtdw-save']) || wp_verify_nonce( $_POST['ardtdw_confirm'], 'ardtdw_update_list' ) ) {
			if(isset ($_POST['ardtdw-textarea'])) {
				update_option(
					'ardtdw-textarea',
					wp_kses($_POST['ardtdw-textarea'],
					array(
				    'a' => array(
				        'href' => array(),
				        'target' => array(),
				        'title' => array()
				    ),
				    'em' => array(),
				    'strong' => array(),
				    'b' => array(),
				    'u' => array(),
					)
				),
				'',
				'yes'
			);
			}

			if ( isset( $_POST['ardtdw-checkbox'] ) ) {
				$ardtdw_checkbox = $_POST['ardtdw-checkbox'];
			} else {
				$ardtdw_checkbox = '';
			}

			if ( isset( $_POST['ardtdw-position'] ) ) {
				$ardtdw_position = $_POST['ardtdw-position'];
			} else {
				$ardtdw_position = '';
			}

			if ($ardtdw_checkbox) {
				if (empty($_POST['ardtdw-textarea'])) { ?>
					<div class="ardtdw-message ardtdw-error">
					<p><?php _e( 'You must have at least one to-do in your list to display it on the website!','dashboard-to-do-list'); ?></p>
				</div>
				<?php
				$ardtdw_checkbox = '';
			} else { ?>
				<div class="ardtdw-message ardtdw-updated">
					<p><?php _e( 'To-Do list updated. List now shows on the website.','dashboard-to-do-list'); ?></p>
				</div>
				<?php
			}
		} else { ?>
			<div class="ardtdw-message ardtdw-updated">
				<p><?php _e( 'To-Do list updated.','dashboard-to-do-list'); ?></p>
			</div>
			<?php
		}

			update_option('ardtdw-checkbox', absint($ardtdw_checkbox));
			update_option('ardtdw-position', $ardtdw_position);

		}
	}
}
}

if ( !function_exists( 'ardtdw_widget' ) ) {
function ardtdw_widget() {
	ardtdw_widgetupdate();
	$ardtdw_callbackURL = get_site_url();
	$ardtdw_TextArea = stripslashes(get_option('ardtdw-textarea'));
	$ardtdw_CheckBox = get_option('ardtdw-checkbox');
	$ardtdw_Position = get_option('ardtdw-position');
	$ardtdw_Position = (empty($ardtdw_Position) || $ardtdw_Position == '0' || $ardtdw_Position == '' || $ardtdw_Position == 'undefined' ) ? 'right' : get_option('ardtdw-position');
	?>
	<form action='<?php echo $ardtdw_callbackURL ?>/wp-admin/index.php' method='post'>

		<textarea name='ardtdw-textarea' id='ardtdw-textarea' rows='10'><?php echo esc_html($ardtdw_TextArea) ?></textarea><p/>
		<p class='field-comment'><?php _e( 'One to-do per line. Accepts the following HTML tags: a (href, title, target), em, strong, b, u.','dashboard-to-do-list'); ?></p>
		<p><label for='ardtdw-checkbox'><input name='ardtdw-checkbox' type='checkbox' id='ardtdw-checkbox' value='1' <?php checked( esc_html($ardtdw_CheckBox), true) ?> /><?php _e( 'Show list on website','dashboard-to-do-list'); ?></label></p>
		<p>
			<strong><?php _e( 'List Position:','dashboard-to-do-list'); ?></strong></br>

			<label><input type="radio" name="ardtdw-position" value="left" <?php echo ($ardtdw_Position == 'left') ? 'checked' : ''; ?>> <?php _e( 'Left aligned', 'dashboard-to-do-list' ); ?></label>
			&nbsp;&nbsp;
			<label><input type="radio" name="ardtdw-position" value="right" <?php echo ($ardtdw_Position == 'right') ? 'checked' : ''; ?>> <?php _e( 'Right aligned', 'dashboard-to-do-list' ); ?></label>
		</p>
		<input type='submit' value='<?php _e( 'Save','dashboard-to-do-list'); ?>' class='button-primary' name='ardtdw-save'>
		<?php wp_nonce_field( 'ardtdw_update_list', 'ardtdw_confirm' ); ?>
	</form>
	<?php
}
}

if (get_option('ardtdw-checkbox') && get_option('ardtdw-textarea')) {
	if ( !function_exists( 'ardtdw_widgethtml' ) ) {
	function ardtdw_widgethtml() {
		if( current_user_can('administrator') ) {
		ardtdw_widget_html();
	}
	}
	add_action('wp_footer', 'ardtdw_widgethtml');
}
}


register_activation_hook(__FILE__, 'crudOperationsTable');

function crudOperationsTable() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'userstable';
  $sql = "CREATE TABLE `$table_name` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(220) DEFAULT NULL,
  `email` varchar(220) DEFAULT NULL,
  PRIMARY KEY(user_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

add_action('admin_menu', 'addAdminPageContent');

function addAdminPageContent() {
  add_menu_page('CRUD', 'CRUD', 'manage_options' ,__FILE__, 'crudAdminPage', 'dashicons-wordpress');
}
function crudAdminPage() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'userstable';

  if (isset($_POST['newsubmit'])) {
    $name = $_POST['newname'];
    $email = $_POST['newemail'];
    $wpdb->query("INSERT INTO $table_name(name,email) VALUES('$name','$email')");
    echo "<script>location.replace('admin.php?page=crud.php');</script>";
  }

  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $name = $_POST['uptname'];
    $email = $_POST['uptemail'];
    $wpdb->query("UPDATE $table_name SET name='$name',email='$email' WHERE user_id='$id'");
    echo "<script>location.replace('admin.php?page=crud.php');</script>";
  }

  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $wpdb->query("DELETE FROM $table_name WHERE user_id='$del_id'");
    echo "<script>location.replace('admin.php?page=crud.php');</script>";
  }
  
  ?>
  <div class="wrap">
    <h2>CRUD Operations</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="25%">User ID</th>
          <th width="25%">Name</th>
          <th width="25%">Email Address</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
            <td><input type="text" value="AUTO_GENERATED" disabled></td>
            <td><input type="text" id="newname" name="newname"></td>
            <td><input type="text" id="newemail" name="newemail"></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $result = $wpdb->get_results("SELECT * FROM $table_name");
          foreach ($result as $print) {
            echo "
              <tr>
                <td width='25%'>$print->user_id</td>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->email</td>
                <td width='25%'><a href='admin.php?page=crud.php&upt=$print->user_id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=crud.php&del=$print->user_id'><button type='button'>DELETE</button></a></td>
              </tr>
            ";
          }
        ?>
      </tbody>  
    </table>
    <br>
    <br>
    <?php
      if (isset($_GET['upt'])) {
        $upt_id = $_GET['upt'];
        $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$upt_id'");
        foreach($result as $print) {
          $name = $print->name;
          $email = $print->email;
        }
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='25%'>User ID</th>
              <th width='25%'>Name</th>
              <th width='25%'>Email Address</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='25%'>$print->user_id <input type='hidden' id='uptid' name='uptid' value='$print->user_id'></td>
                <td width='25%'><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                <td width='25%'><input type='text' id='uptemail' name='uptemail' value='$print->email'></td>
                <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=crud.php'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      }
    ?>
  </div>
  <?php
}