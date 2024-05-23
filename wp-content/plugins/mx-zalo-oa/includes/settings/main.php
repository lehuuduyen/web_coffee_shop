<?php


defined("ABSPATH") or exit("No script kiddies please!");
$current_tab = isset($_REQUEST["tab"]) ? esc_html($_REQUEST["tab"]) : "general";
$zaloerror = isset($_GET["zaloerror"]) && $_GET["zaloerror"] ? true : false;
$sub = isset($_GET["sub"]) ? sanitize_text_field($_GET["sub"]) : "";
$mess = isset($_GET["mess"]) && $_GET["mess"] ? sanitize_text_field(wp_unslash($_GET["mess"])) : "";
if ($mess) : ?>
    <?php $class = $zaloerror ? "notice-error" : "notice-success"; ?>
    <div class="notice <?= $class ?> is-dismissible">
        <p><?= $mess ?></p>
    </div>
<?php endif; ?>

<div class="wrap mx_zalooa_wrap">
    <h1><?php _e("Cài đặt Zalo OA", MX_ZALOOA_TEXTDOMAIN); ?></h1>

    <h2 class="nav-tab-wrapper mx-nav-tab-wrapper">
        <a href="?page=<?= MX_ZALOOA_TEXTDOMAIN ?>&tab=general" class="nav-tab <?= $current_tab == "general" ? "nav-tab-active" : "" ?>">
            <?php _e("Cài đặt chung", MX_ZALOOA_TEXTDOMAIN); ?>
        </a>

        <?php if ($this->has_access_token()) : ?>
            <a href="?page=<?= MX_ZALOOA_TEXTDOMAIN ?>&tab=zns" class="nav-tab <?= $current_tab == "zns" ? "nav-tab-active" : "" ?>">
                <?php _e("ZNS API", MX_ZALOOA_TEXTDOMAIN); ?>
            </a>
            <a href="?page=<?= MX_ZALOOA_TEXTDOMAIN ?>&tab=all-mess" class="nav-tab <?= $current_tab == "all-mess" ? "nav-tab-active" : "" ?>"><?php _e("Quản lý tin nhắn", MX_ZALOOA_TEXTDOMAIN); ?></a>
            <a href="?page=<?= MX_ZALOOA_TEXTDOMAIN ?>&tab=phone" class="nav-tab <?= $current_tab == "phone" ? "nav-tab-active" : "" ?>"><?php _e("Quản lý số điện thoại", MX_ZALOOA_TEXTDOMAIN); ?></a>
            <?php do_action("zalo_main_tab", $current_tab); ?>
        <?php endif; ?>
    </h2>

    <?php switch ($current_tab):
        case "general":
            include "general.php";
            break;
        case "transaction":
            include "transaction.php";
            break;
        case "zns":
            include "zns.php";
            break;
        case "license":
            include "license.php";
            break;
        case "all-mess":
            include "all-mess.php";
            break;
        case "phone":
            include "all-phone.php";
            break;
        case "login":
            include "zalo-login.php";
            break;
        case "campaigns":
            if ($sub == "add" || $sub == "edit") {
                include "campaigns-add.php";
            } else {
                include "campaigns.php";
            }
            break;
        default:
            do_action("zalo_main_tab_content", $current_tab);
    endswitch; ?>

</div>