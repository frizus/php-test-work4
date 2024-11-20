<?php

declare(strict_types=1);

namespace League\Flysystem\UnixVisibility;

use League\Flysystem\Visibility;

/*
 * @see \League\Flysystem\UnixVisibility\PortableVisibilityConverter
 */
$file = file_get_contents(base_path('vendor') . '/league/flysystem/src/UnixVisibility/PortableVisibilityConverter.php');
$className = 'PortableVisibilityConverter';
$file = str_replace('class ' . $className, 'class OLD_' . $className, $file);
$file = trim($file);
if (str_starts_with($file, '<?php')) {
    $file = substr($file, 5);
}
if (str_ends_with($file, '?>')) {
    $file = substr($file, 0, -2);
}
//dd($file);
eval($file);
unset($file);

class PortableVisibilityConverter extends \League\Flysystem\UnixVisibility\OLD_PortableVisibilityConverter
{
    private const LARAVEL_FILE_PUBLIC = 0644;

    private const LARAVEL_FILE_PRIVATE = 0600;

    private const LARAVEL_DIRECTORY_PUBLIC = 0755;

    private const LARAVEL_DIRECTORY_PRIVATE = 0700;

    public function __construct(
        private int $filePublic = self::LARAVEL_FILE_PUBLIC,
        private int $filePrivate = self::LARAVEL_FILE_PRIVATE,
        private int $directoryPublic = self::LARAVEL_DIRECTORY_PUBLIC,
        private int $directoryPrivate = self::LARAVEL_DIRECTORY_PRIVATE,
        private string $defaultForDirectories = Visibility::PRIVATE
    ) {
        $this->swapMode();

        parent::__construct(
            $this->filePublic,
            $this->filePrivate,
            $this->directoryPublic,
            $this->directoryPrivate,
            $this->defaultForDirectories
        );
    }

    private function swapMode()
    {
        if ($this->filePublic === self::LARAVEL_FILE_PUBLIC) {
            $this->filePublic = config('filesystems.default_permissions.file.public', self::LARAVEL_FILE_PUBLIC);
        }
        if ($this->filePrivate === self::LARAVEL_FILE_PRIVATE) {
            $this->filePrivate = config('filesystems.default_permissions.file.private', self::LARAVEL_FILE_PRIVATE);
        }
        if ($this->directoryPublic === self::LARAVEL_DIRECTORY_PUBLIC) {
            $this->directoryPublic = config('filesystems.default_permissions.dir.public', self::LARAVEL_DIRECTORY_PUBLIC);
        }
        if ($this->directoryPrivate === self::LARAVEL_DIRECTORY_PRIVATE) {
            $this->directoryPrivate = config('filesystems.default_permissions.dir.private', self::LARAVEL_DIRECTORY_PRIVATE);
        }
    }
}
