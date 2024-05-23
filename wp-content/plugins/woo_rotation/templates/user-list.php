<?php
$arrayCommission = [];
$totalCommission = 0;
$listUser = [];
foreach ($users as $keyUser => $user) {
  $listUser[$user['ID']] = $user['user_nicename'] . ' - ' . $user['user_login'];
}
foreach ($userRotations as $key => $commission1) {
  $userRotations[$key]['user_nicename'] = $listUser[$commission1['user_id']] ;

}
?>

<br />
<div class="flex-center space-between">
  <div class="flex-center">
    <form action="" method="GET" class="flex-center">
      <input type="hidden" name="page" value="rotation" />
      <input type="hidden" name="paged" value="1" />
      <input type="hidden" name="tab" value="setting1" />
      <!-- <input type="text" maxlength="48" placeholder="Điền tên user muốn tìm" name="username" value="<?php echo isset($_GET['username']) ? $_GET['username'] : ''; ?>" /> -->
      <!-- <button type="submit" name="searchUser" class="button button-primary">Tìm kiếm</button> -->
    </form>
  
  </div>
 
</div>
<br />
<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên</th>
      <th>Tổng xu<span class="d-none"><br /> (sum commission status 4 (CURRENT USER))</span></th>
      <th>Thời gian <span class="d-none"><br /> (sum commission status 2 (CURRENT USER))</span></th>
    </tr>
  </thead>
  <tbody>
    <?php

      foreach ($userRotations as $history) {
       
      ?>
      <tr>
        <td><?php echo $history['user_nicename'];?></td>
        <td><?php echo $history['commission']; ?></td>
        <td><?php echo $history['create_at']; ?></td>
      
      </tr>
    <?php
    }
    ?>
  </tbody>
</table>
<ul class="pagination">
    <?php
    
    if ((!empty($_GET['paged'])) && ($_GET['tab'] == 'setting1')) $pg = $_GET['paged'];
    else $pg = 1;
    $paramFilter = isset($_GET['searchUser']) && isset($_GET['username']) ? '&username=' . $_GET['username'] . '&searchUser' : '';

    if (isset($pg) && $pg > 1) {
      echo '<li><a class="button" href="' . site_url() . '/wp-admin/admin.php?page=rotation&paged=' . ($pg - 1) . '&tab=setting1' . $paramFilter . '">«</a></li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
      if (isset($pg) && $pg == $i)  $active = 'active';
      else $active = '';
      echo '<li><a href="' . site_url() . '/wp-admin/admin.php?page=rotation&paged=' . $i . '&tab=setting1' . $paramFilter . '" class="button ' . $active . '">' . $i . '</a></li>';
    }

    if (isset($pg) && $pg < $totalPages) {
      echo '<li><a class="button" href="' . site_url() . '/wp-admin/admin.php?page=rotation&paged=' . ($pg + 1) . '&tab=setting1' . $paramFilter . '">»</a></li>';
    }
    ?>
  </ul>
