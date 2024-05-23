<?php
  global $wpdb;
  $tableListRotation = $wpdb->prefix .'woo_list_rotations';

  $successMessage = '';
  $errorMessage = '';
  $tableUser = $wpdb->prefix . 'users';
  $tableShareLink = $wpdb->prefix . 'woo_history_share_link';
  $tableUserRotation = $wpdb->prefix . 'woo_history_user_rotation';
  $tablePost = $wpdb->prefix . 'posts';
  $status = [
    'PURCHASE' => 1,
    'USE_POINT' => 2,
    'CANCEL' => 5,
    'USE_POINT_IN_PROCESS' => 4,
  ];

  $usersDisplay = [];
  $users = $wpdb->get_results( 'SELECT * FROM ' . $tableUser . ' WHERE user_login != "77777777" ORDER BY id ASC', ARRAY_A );
  $userRotations = $wpdb->get_results( 'SELECT * FROM ' . $tableUserRotation . ' WHERE status = 1 ORDER BY id DESC', ARRAY_A );
  $userRotationsRut = $wpdb->get_results( 'SELECT * FROM ' . $tableUserRotation . ' WHERE status = 2 ORDER BY id DESC', ARRAY_A );
  $usersDisplay = $users;
 
  // if (isset($_GET['searchUser']) && isset($_GET['username'])) {
  //   $usersSearch = $wpdb->get_results( 'SELECT * FROM ' . $tableUser . ' WHERE user_login != "77777777" && (user_login LIKE "%' . $_GET['username'] . '%" OR user_nicename LIKE "%' . $_GET['username'] . '%" ) ORDER BY id ASC', ARRAY_A );
  //   $usersDisplay = $usersSearch;
  // } else {
  //   $usersDisplay = $users;
  // }

  $currentPage = (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting1') ? (int) $_GET['paged'] : 1;
  $paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
  $stt = $paged - 1;
  $total = count( $userRotations );
  $perPage = 10;                                                                                                                                                   ;
  $totalPages = ceil($total/ $perPage);
  
  $totalRut = count( $userRotationsRut );
  $totalPagesRut = ceil($totalRut/ $perPage);


  $currentPage = max($currentPage, 1);
  $currentPage = min($currentPage, $totalPages);
  $offset = ($currentPage - 1) * $perPage;
  if ($offset < 0) $offset = 0;
  $userRotations = array_slice($userRotations, $offset, $perPage);

  if (isset($_POST['saveSetting'])) {
    $wprotation = get_option('woo_aff_setting');
    if ($wprotation) {
      update_option('woo_aff_setting',$_POST['woo_aff_setting']);
    } else {
      add_option('woo_aff_setting',$_POST['woo_aff_setting']);
    }
    $successMessage = 'Lưu cài đặt thành công';
  }
  if (isset($_POST['updateTableRotation'])) {
    if(isset($_POST['id'])){
      $up = $wpdb->update( $tableListRotation, array( 'name' => $_POST['name'],'rate' => $_POST['rate'],'point' => $_POST['point']),array('id'=>$_POST['id']));
    }else{
      $up = $wpdb->insert( $tableListRotation, array( 'name' => $_POST['name'],'rate' => $_POST['rate'],'point' => $_POST['point']));

    }
   
    $successMessage = 'Lưu cài đặt thành công';
  }
  
  if (isset($_POST['saveSettingRotation'])) {
    $wprotationLevel2 = get_option('woo_rotation_price_from');
    if ($wprotationLevel2) {
      update_option('woo_rotation_price_from',$_POST['woo_rotation_price_from']);
    } else {
      add_option('woo_rotation_price_from',$_POST['woo_rotation_price_from']);
    }
    $wprotationLevel2 = get_option('woo_rotation_price_to');
    if ($wprotationLevel2) {
      update_option('woo_rotation_price_to',$_POST['woo_rotation_price_to']);
    } else {
      add_option('woo_rotation_price_to',$_POST['woo_rotation_price_to']);
    }
    $wprotationLevel2 = get_option('woo_rotation_xu');
    if ($wprotationLevel2) {
      update_option('woo_rotation_xu',$_POST['woo_rotation_xu']);
    } else {
      add_option('woo_rotation_xu',$_POST['woo_rotation_xu']);
    }
    $successMessage = 'Lưu cài đặt thành công';
  }
  if (isset($_POST['saveSettingRotation4'])) {
    $woo_rotation_change_xu = get_option('woo_rotation_change_xu');
    if ($woo_rotation_change_xu) {
      update_option('woo_rotation_change_xu',$_POST['woo_rotation_change_xu']);
    } else {
      add_option('woo_rotation_change_xu',$_POST['woo_rotation_change_xu']);
    }
    $woo_rotation_to_point = get_option('woo_rotation_to_point');
    if ($wprotationLevel2) {
      update_option('woo_rotation_to_point',$_POST['woo_rotation_to_point']);
    } else {
      add_option('woo_rotation_to_point',$_POST['woo_rotation_to_point']);
    }
    $successMessage = 'Lưu cài đặt thành công';
  }

  if (isset($_POST['updateStatus'])) {
    $update = $wpdb->update($tableUserRotation, ['status' => $_POST['status']], ['id' => $_POST['userRotationId']]);
    $successMessage = 'Cập nhật trạng thái thành công';
  }
?>

<div class="wrap">
  <h1>Cấu hình vòng quay</h1>
  <hr />
  <?php if ($successMessage) { ?>
    <div id="message" class="success-message">
      <p><?php echo $successMessage; ?></p>
      <button id="remove-message" type="button"></button>
    </div>
  <?php } ?>
  <ul class="nav-tabs-rotation">
    <li id="tabSettingRotation1" class="active" onclick="changeUrlRotation(1)">
      <a href="#tab-setting-rotation-1-content">Lịch sử nhận</a>
    </li>
    <li id="tabSettingRotation5"  onclick="changeUrlRotation(5)">
      <a href="#tab-setting-rotation-5-content">Lịch sử đổi</a>
    </li>
    <li id="tabSettingRotation2" onclick="changeUrlRotation(2)">
      <a href="#tab-setting-rotation-2-content">Cài đặt tỉ lệ vòng quay</a>
    </li>
    <li id="tabSettingRotation3" onclick="changeUrlRotation(3)">
      <a href="#tab-setting-rotation-3-content">Cấu hình vòng quay</a>
    </li>
    <li id="tabSettingRotation4" onclick="changeUrlRotation(4)">
      <a href="#tab-setting-rotation-4-content">Cấu hình đổi xu</a>
    </li>
   
  </ul>
  <div class="tab-content">
    <div id="tab-setting-rotation-1-content" class="tab-pane-rotation active">
      <?php require_once(dirname(__FILE__) . '/user-list.php'); ?>
    </div>
    <div id="tab-setting-rotation-5-content" class="tab-pane-rotation">
      <?php require_once(dirname(__FILE__) . '/user-list-rut.php'); ?>
    </div>
    <div id="tab-setting-rotation-3-content" class="tab-pane-rotation">
    <form action="?page=rotation&paged=1&tab=setting3" method="POST">
        <h4>Giá trị đơn hàng từ</h4>
        <input type="number" max="100000000" class="regular-text" name="woo_rotation_price_from" value="<?php echo get_option('woo_rotation_price_from'); ?>" />
        <h4>Giá trị đơn hàng đến</h4>
        <input type="number" max="100000000" class="regular-text" name="woo_rotation_price_to" value="<?php echo get_option('woo_rotation_price_to'); ?>" />
        <h4>Số lượt</h4>
        <input type="number" max="100000000" class="regular-text" name="woo_rotation_xu" value="<?php echo get_option('woo_rotation_xu'); ?>" />
        <br>
        <br>
        <button type="submit" class="button button-primary" name="saveSettingRotation">Lưu lại</button>
      </form>
    </div>
    <div id="tab-setting-rotation-4-content" class="tab-pane-rotation">
    <form action="?page=rotation&paged=1&tab=setting4" method="POST">
        <h4>Cần bao nhiêu xu</h4>
        <input type="number" max="100000000" id="numberInput" class="regular-text" name="woo_rotation_change_xu" value="<?php echo get_option('woo_rotation_change_xu'); ?>" />
        <p id="result" style="color:red"></p>

        <h4>Đổi thành điểm</h4>
        <input type="number" class="regular-text" name="woo_rotation_to_point" value="1" disabled />
        
        <br>
        <br>
        <button type="submit" class="button button-primary" id="submit1" name="saveSettingRotation4">Lưu lại</button>
      </form>
    </div>
    <div id="tab-setting-rotation-2-content" class="tab-pane-rotation">
    <?php require_once(dirname(__FILE__) . '/transfer.php'); ?>
    </div>
  </div>
    
    
  <div class="overlay d-none"></div>
  <?php require_once(dirname(__FILE__) . '/child-user-modal.php'); ?>
  <script>
    // Get the input element
const numberInput = document.getElementById('numberInput');

// Add onchange event listener
numberInput.addEventListener('change', function() {
    // Get the value of the input
    const inputValue = parseInt(this.value);

    // Check if the input value is a multiple of 100
    if (inputValue % 100 === 0) {
      document.getElementById('result').textContent = "";
      document.getElementById('submit1').disabled = false;
      
    } else {
      document.getElementById('result').textContent = "Phải là bội số 100";
      document.getElementById('submit1').disabled = true;

    }
});
  </script>
</div>
