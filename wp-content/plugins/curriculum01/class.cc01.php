<?php

class Curriculum_01 {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // メニュー追加
        add_action( 'admin_menu', array( $this, 'add_book_menu_page' ), 10 );
        add_action( 'admin_menu', array( $this, 'add_comment_menu_page' ), 10 );
    }

    // View
    public static function view( $name) {
        $file = CC01_PLUGIN_DIR . 'views/'. $name . '.php';
        include($file);
    }

    // メニュー
    public function add_book_menu_page() {
        add_menu_page(
            __( '福利厚生書籍', '' ),                // ページのタイトルタグ<title>に表示されるテキスト
            __( "福利厚生書籍", '' ),                // 左メニューとして表示されるテキスト
            'manage_options',               // 必要な権限 manage_options は通常 administrator のみに与えられた権限
            'welfare-books',               // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
            array( $this, 'top_page' ),     // メニューページを表示する際に実行される関数
            'dashicons-book',        // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
            99                               // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
        );

        add_submenu_page(
            'welfare-books'    // 親メニューのスラッグ
            , '購入書籍一覧' // ページのタイトルタグ<title>に表示されるテキスト
            , '購入書籍一覧' // サブメニューとして表示されるテキスト
            , 'manage_options' // 必要な権限 manage_options は通常 administrator のみに与えられた権限
            , 'book-list'  // サブメニューのスラッグ名。この名前を親メニューのスラッグと同じにすると親メニューを押したときにこのサブメニューを表示します。一般的にはこの形式を採用していることが多い。
            , array($this, 'book_list') //（任意）このページのコンテンツを出力するために呼び出される関数
        );

        add_submenu_page(
            'welfare-books'    // 親メニューのスラッグ
            , '購入書籍の登録' // ページのタイトルタグ<title>に表示されるテキスト
            , '購入書籍の登録' // サブメニューとして表示されるテキスト
            , 'manage_options' // 必要な権限 manage_options は通常 administrator のみに与えられた権限
            , 'book-add'  // サブメニューのスラッグ名。この名前を親メニューのスラッグと同じにすると親メニューを押したときにこのサブメニューを表示します。一般的にはこの形式を採用していることが多い。
            , array($this, 'view_add_book') //（任意）このページのコンテンツを出力するために呼び出される関数
        );

    }

    public function add_comment_menu_page()
    {
        add_menu_page(
            __( '購読書籍の感想', '' ),                // ページのタイトルタグ<title>に表示されるテキスト
            __( '購読書籍の感想', '' ),                // 左メニューとして表示されるテキスト
            'manage_options',               // 必要な権限 manage_options は通常 administrator のみに与えられた権限
            'welfare-book-comment',               // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
            array( $this, 'view_sample_page' ),     // メニューページを表示する際に実行される関数
            'dashicons-admin-comments',        // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
            99                               // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
        );

        add_submenu_page(
            'welfare-book-comment'    // 親メニューのスラッグ
            , '感想一覧' // ページのタイトルタグ<title>に表示されるテキスト
            , '感想一覧' // サブメニューとして表示されるテキスト
            , 'manage_options' // 必要な権限 manage_options は通常 administrator のみに与えられた権限
            , 'book-comment-list'  // サブメニューのスラッグ名。この名前を親メニューのスラッグと同じにすると親メニューを押したときにこのサブメニューを表示します。一般的にはこの形式を採用していることが多い。
            , array($this, 'view_sample_page') //（任意）このページのコンテンツを出力するために呼び出される関数
        );

        add_submenu_page(
            'welfare-book-comment'    // 親メニューのスラッグ
            , '感想の登録' // ページのタイトルタグ<title>に表示されるテキスト
            , '感想の登録' // サブメニューとして表示されるテキスト
            , 'manage_options' // 必要な権限 manage_options は通常 administrator のみに与えられた権限
            , 'book-comment-add'  // サブメニューのスラッグ名。この名前を親メニューのスラッグと同じにすると親メニューを押したときにこのサブメニューを表示します。一般的にはこの形式を採用していることが多い。
            , array($this, 'view_add_comment') //（任意）このページのコンテンツを出力するために呼び出される関数
        );
    }

    public function top_page() {
        $this->view('index');
    }

    public function book_list()
    {
        $this->view('index');
    }

    public function view_add_book()
    {
        $this->view('add_book');
    }

    public function view_add_comment()
    {
        $this->view('add_comment');
    }

}