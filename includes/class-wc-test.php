<?php

defined('ABSPATH') || exit;

/**
 * Test.
 */
class WC_TEST
{
    public function __construct()
    {
        add_action('admin_notices', [$this, 'test_hello']);
    }

    public function test_hello()
    {
        echo 'TESTE FUNCTION ACTION';
    }
}

new WC_TEST();
