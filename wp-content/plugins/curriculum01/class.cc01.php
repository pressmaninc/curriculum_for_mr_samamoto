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

}