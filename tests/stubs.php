<?php

declare(strict_types=1);

/*
 * Minimal stand-ins for the WordPress classes our unit tests touch. WordPress
 * itself is not loaded in the unit suite; behaviour that depends on a running
 * WP install is covered by the integration suite instead.
 */

if (! class_exists('WP_Error')) {
    class WP_Error
    {
        /** @param array<string,mixed> $data */
        public function __construct(
            public string $code = '',
            public string $message = '',
            public array $data = [],
        ) {}

        public function get_error_message(): string
        {
            return $this->message;
        }
    }
}

if (! class_exists('WP_REST_Request')) {
    class WP_REST_Request
    {
        /** @param array<string,string> $headers */
        public function __construct(private array $headers = []) {}

        public function get_header(string $name): ?string
        {
            return $this->headers[strtolower($name)] ?? null;
        }
    }
}

if (! class_exists('WP_REST_Response')) {
    class WP_REST_Response
    {
        /** @param mixed $data */
        public function __construct(private $data = null, public int $status = 200) {}

        /** @return mixed */
        public function get_data()
        {
            return $this->data;
        }
    }
}

if (! function_exists('is_wp_error')) {
    function is_wp_error($thing): bool
    {
        return $thing instanceof WP_Error;
    }
}
