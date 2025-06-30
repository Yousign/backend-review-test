<?php

namespace App\Service;

class PayloadSanitizer
{
    public function sanitize(mixed $data): mixed
    {
        if (is_string($data)) {
            return str_replace("\u{0000}", '', $data);
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitize($value);
            }
        }

        return $data;
    }
}
