<?php
/*
Plugin Name:[福利厚生]購入書籍管理プラグイン
Plugin URI:https://pressman.ne.jp
Description: 福利厚生で購入した書籍を管理するプラグインです。特定のACFフィールドのバリデーションを設定します。
Version: 1.0
Author: 阪本大将 (Daisuke Sakamoto)
Author URI: https://pressman.ne.jp
License: GPL2
*/

// 各種情報定義
define('CC01_PLUGIN_DIR', plugin_dir_path(__FILE__));

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once CC01_PLUGIN_DIR . '/class.cc01.php';

Curriculum_01::get_instance();




