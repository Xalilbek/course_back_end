<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class FileHelper
{
    private $file;
    private $file_name;
    private $has_file;

    public function __construct($file)
    {
        if (!$file) {
            $this->has_file = false;
            $this->file_name = null;
            return;
        }
        $this->has_file = true;
        $extension = $file->getClientOriginalExtension();
        $filename = time();
        $filename .= Str::random(20);
        $filename .= '.' . $extension;
        $this->file = $file;
        $this->file_name = $filename;
    }
    public function getName()
    {
        return $this->file_name;
    }
    public function move(): void
    {
        $this->file->move(...func_get_args());
    }
    public function save($public_path): void
    {
        if ($this->has()) {
            $public_path = ltrim($public_path, '/');
            $this->move(public_path($public_path), $this->getName());
        }
    }
    public function has(): bool
    {
        return $this->has_file;
    }
}
