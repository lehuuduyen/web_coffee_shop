<?php
  $userCommissions = $wpdb->get_results( 'SELECT * FROM ' . $tableUserCommission . ' ORDER BY id ASC', ARRAY_A );
  $userCommissionsStatus4 = [];
  $historyRotation = $wpdb->get_results( 'SELECT * FROM ' . $tableListRotation  , ARRAY_A );
  

  $currentPage = (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting3') ? (int) $_GET['paged'] : 1;
  $total = count( $userCommissionsStatus4 );
  $perPage = 10;
  $totalPages = ceil($total/ $perPage);
  $currentPage = max($currentPage, 1);
  $currentPage = min($currentPage, $totalPages);
  $offset = ($currentPage - 1) * $perPage;
  if ($offset < 0) $offset = 0;
?>



<br />
<div>
<button type="button" class="button button-primary"  onclick="openLowerModalRotation('')">
  Thêm
</button>
  </div>
  <br>
<table class="wp-list-table widefat fixed striped table-view-list users">
  <thead>
    <tr>
      <th>Tên</th>
      <th>Giá trị</th>
      <th>Tỉ lệ</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach ($historyRotation as $rotation) {
       
    ?>
      <tr>
        <td><?php echo $rotation['name'] ?></td>
        <td><?php echo $rotation['point'] ?></td>
        <td><?php echo $rotation['rate'] ?></td>
        <td><button type="button" class="btn btn-primary" onclick="openLowerModalRotation('<?php echo $rotation['id']; ?>')">
  Update
</button></td>
        
      </tr>
      <div class="modal d-none modal-lower-level-rotation-<?php echo $rotation['id']; ?>">
    <div class="modal-wrapper">
      <p onclick="closeLowerModalRotation('<?php echo $rotation['id']; ?>')" class="close">✕</p>
      <div class="modal-header">
        <p>Cập nhật</p>
        <hr>
      </div>
      <div class="modal-content">
      <form action="?page=rotation&paged=1&tab=setting2" method="POST">
        <h4>Tên</h4>
        <input type="hidden" class="regular-text" name="id" value="<?php echo $rotation['id']; ?>" />
        <input type="text" class="regular-text" name="name" value="<?php echo $rotation['name']; ?>" />
        <h4>Giá trị</h4>
        <input type="text" class="regular-text" name="point" value="<?php echo $rotation['point']; ?>" />
        <h4>Tỉ lệ</h4>
        <input type="text" class="regular-text" name="rate" value="<?php echo $rotation['rate']; ?>" />
        <br>
        <br>
        <button type="submit" class="button button-primary" name="updateTableRotation">Lưu lại</button>
      </form>
      </div>
    </div>
  </div>
    <?php } ?>
  </tbody>
</table>
<div class="modal d-none modal-lower-level-rotation-">
    <div class="modal-wrapper">
      <p onclick="closeLowerModalRotation('')" class="close">✕</p>
      <div class="modal-header">
        <p>Thêm</p>
        <hr>
      </div>
      <div class="modal-content">
      <form action="?page=rotation&paged=1&tab=setting2" method="POST">
        <h4>Tên</h4>
        <input type="text" class="regular-text" name="name" value="" />
        <h4>Giá trị</h4>
        <input type="text" class="regular-text" name="point" value="" />
        <h4>Tỉ lệ</h4>
        <input type="text" class="regular-text" name="rate" value="" />
        <br>
        <br>
        <button type="submit" class="button button-primary" name="updateTableRotation">Lưu lại</button>
      </form>
      </div>
    </div>
  </div>
<ul class="pagination">
  <?php
    if ( (! empty( $_GET['paged'] )) && ($_GET['tab'] == 'setting3') ) $pg = $_GET['paged'];
    else $pg = 1;
    $paramFilterSetting3 = '';
    if (isset($_GET['choDoiSoat'])) {
      $paramFilterSetting3 = '&choDoiSoat';
    }
    if (isset($_GET['daThanhToan'])) {
      $paramFilterSetting3 = '&daThanhToan';
    }

    if ( isset( $pg ) && $pg > 1 ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . ( $pg - 1 ) . '&tab=setting3' . $paramFilterSetting3 . '">«</a></li>';
    }

    for ( $i = 1; $i <= $totalPages; $i++ ) {
      if ( isset( $pg ) && $pg == $i )  $active = 'active';
      else $active = '';
      echo '<li><a href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . $i . '&tab=setting3' . $paramFilterSetting3 . '" class="button ' . $active . '">' . $i . '</a></li>';
    }

    if ( isset( $pg ) && $pg < $totalPages ) {
      echo '<li><a class="button" href="'.site_url().'/wp-admin/admin.php?page=hoa-hong&paged=' . ( $pg + 1 ). '&tab=setting3' . $paramFilterSetting3 . '">»</a></li>';
    }
  ?>
</ul>
