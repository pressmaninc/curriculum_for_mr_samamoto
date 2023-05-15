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
        add_filter('acf/validate_value/name=book_number', [$this, 'only_alphanumeric'], 10, 4);
        add_action('acf/save_post', [$this, 'sync_post_title']);    // カスタム投稿タイプ（福利厚生書籍）で新規登録した際に発火するイベント

        // 一覧
        add_filter('manage_book-rental_posts_columns', [$this, 'add_book_rental_column']);
        add_filter('manage_edit-book-rental_sortable_columns', [$this, 'posts_sortable_columns']);
        add_filter('manage-book-rental_pages_custom_column', [$this, 'add_table_class_name'], 10, 2);
        add_action('manage_book-rental_posts_custom_column', [$this, 'set_average_book_rental_column'], 10, 2);

        // ウィジェットを追加
        add_action('wp_dashboard_setup', [$this, 'add_custom_widget']);

        // 返却期限が過ぎているレコードは背景色を赤くする
        add_action('admin_head', [$this, 'change_background_rental_list']);
        add_filter( 'request', [$this, 'add_posts_column_orderby_wpp_views']);
    }

    /**
     * 半角英数のみ許可 (ACF)
     * @param $valid
     * @param $value
     * @param $field
     * @param $input_name
     * @return mixed|string|true|null
     */
    public function only_alphanumeric($valid, $value, $field, $input_name): mixed
    {
        if( $valid !== true ) {
            return $valid;
        }

        if (!preg_match("/^[a-zA-Z0-9]+$/", $value)) {
            return __('半角英数字で入力してください。');
        }

        return $valid;
    }

    /**
     * カスタム投稿タイプ（書籍レンタル）：保存する際にタイトルをpost_titleへコピーする
     * @param $post_id
     * @return void
     */
    public function sync_post_title($post_id)
    {
        // カスタム投稿タイプか判定
        if (get_post_type($post_id) !== 'welfare-book') {
            return;
        }
        // 書籍のタイトルをpost_titleへコピー
        wp_update_post([
            'ID' => $post_id,
            'post_title' => get_field('title', $post_id),
        ]);
    }


    public function add_book_rental_column($columns)
    {
        $columns['release_date'] = '発売日';
        $columns['made'] = '著者';
        $columns['publisher'] = '出版社';
        $columns['book_image'] = '書籍表紙画像';
        $columns['average'] = '評点';
        return $columns;
    }

    public function posts_sortable_columns($sortable_column)
    {
        $sortable_column['average'] = '評点';
        return $sortable_column;
    }



    public function set_average_book_rental_column($column_name, $post_id)
    {
        global $wpdb;

        // 書籍のIDを取得
        $book_id = get_field('title', $post_id);

        // 書籍の情報を取得
        $book_meta = get_post_meta($book_id);

        // 対象の書籍のデータを取得
        $query = "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'comment-book' AND meta_value = %s";

        $book_list_post_id = $wpdb->get_results($wpdb->prepare($query, $book_id));

        $book_point_rate = '';
        if ($book_list_post_id) {
            $book_list = [];
            foreach ($book_list_post_id as $item) {
                $book_list[] = $item->post_id;
            }

            // %sを生成
            $book_list_prepare = implode(',', array_fill(0, count($book_list), '%s'));

            // 評価の平均値を算出
            $query = "SELECT AVG(meta_value) AS `avg` FROM {$wpdb->prefix}postmeta WHERE post_id IN ({$book_list_prepare}) AND meta_key = 'evaluation' ORDER BY `avg` ASC";
            $book_point_rate = $wpdb->get_results($wpdb->prepare($query, $book_list));
        }

        // 表示の条件分岐
        echo match ($column_name) {
            'release_date' => esc_attr(date('Y年m月d日', strtotime($book_meta['release_date'][0]))),
            'made' => esc_attr($book_meta['made'][0]),
            'publisher' => esc_attr($book_meta['publisher'][0]),
            'average' => $book_point_rate ? $book_point_rate[0]->avg : '-',
            'book_image' => esc_attr($book_meta['book_image'][0]),
            default => __(''),
        };

    }

    public function add_custom_widget()
    {
        wp_add_dashboard_widget( 'custom_widget', '24時間以内に返却期限を迎える書籍', [$this, 'add_rental_book_return_limit']);
    }

    public function add_rental_book_return_limit()
    {

        if (!current_user_can('administrator')) {
            $current_user = wp_get_current_user();
            $post_data = get_posts([
                'post_type' => 'book-rental',
                'posts_per_page' => -1,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'returned-at',
                        'value' => date("Ymd",strtotime("-24 hour")),
                        'compare' => '<',
                    ],
                    [
                        'key' => 'book-lender',
                        'value' => $current_user->ID,
                        'compare' => '=',
                    ]
                ]
            ]);
        }else {
            $post_data = get_posts([
                'post_type' => 'book-rental',
                'posts_per_page' => -1,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'returned-at',
                        'value' => date("Ymd",strtotime("-24 hour")),
                        'compare' => '<',
                    ],
                ]
            ]);
        }

        // 返却書籍の一覧を表示する
        if ($post_data) {
            echo '<ul>';
            foreach ($post_data as $item) {
                $post_id = get_post_meta($item->ID, 'title', true);
                $post = get_post($post_id);
                echo '<li>' . $post->post_title . '</li>';
            }
            echo '</ul>';
        }else {
            echo '24時間以内に返却期限を迎える書籍はありません。';
        }

    }

    public function change_background_rental_list()
    {

        //
        $post_data = get_posts([
            'post_type' => 'book-rental',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'returned-at',
                    'value' => date("Ymd",strtotime("-24 hour")),
                    'compare' => '<',
                ],
            ]
        ]);

        $css = '';

        //
        foreach ($post_data as $item) {
            $css .= "#post-{$item->ID} { background-color: var(--ac-color-error-alt); }\n";
        }

        if ($css !== '') {
            echo '<style>' . $css . '</style>';
        }

    }

    public function add_posts_column_orderby_wpp_views( $vars )
    {
        return $vars;
    }


    public function add_table_class_name($column_name, $post_id)
    {
        echo 'test-class';
    }

}