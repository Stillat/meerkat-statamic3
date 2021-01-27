<?php

namespace Stillat\Meerkat\Http\Composers;

use Illuminate\View\View;
use Statamic\Statamic;
use Stillat\Meerkat\Addon;

class InstallValidationComposer
{

    protected $configPaths = [
        'config_supplement' => 'meerkat/supplement/',
        'config_users' => 'meerkat/users/'
    ];

    public function compose(View $view)
    {
        $storageDirectoriesToCheck = [];

        $storageDirectoriesToCheck['storage_content'] = base_path('content/comments/');
        $storageDirectoriesToCheck['storage_meerkat'] = storage_path('meerkat/');

        $configurationDirectories = [];
        $storageDirectories = [];

        $variables = [];

        $variables[] = [
            'name' => trans('meerkat::validation.statamic_version'),
            'value' => Statamic::version()
        ];
        $variables[] = [
            'name' => trans('meerkat::validation.meerkat_version'),
            'value' => Addon::VERSION
        ];
        $variables[] = [
            'name' => trans('meerkat::validation.server_type'),
            'value' => $_SERVER['SERVER_SOFTWARE']
        ];

        foreach ($this->configPaths as $langKey => $path) {
            $fullPath = config_path($path);
            $doesExist = $this->directoryExists($fullPath);
            $canRead = false;
            $canWrite = false;

            if ($doesExist) {
                $canRead = is_readable($fullPath);
                $canWrite = is_writable($fullPath);
            }

            $configurationDirectories[] = [
                'is_writeable' => $canWrite,
                'is_readable' => $canRead,
                'exists' => $doesExist,
                'path' => $fullPath,
                'description' => trans('meerkat::validation.' . $langKey),
                'name' => trans('meerkat::validation.' . $langKey . '_name')
            ];
        }

        foreach ($storageDirectoriesToCheck as $langKey => $fullPath) {
            $doesExist = $this->directoryExists($fullPath);
            $canRead = false;
            $canWrite = false;

            if ($doesExist) {
                $canRead = is_readable($fullPath);
                $canWrite = is_writable($fullPath);
            }

            $configurationDirectories[] = [
                'is_writeable' => $canWrite,
                'is_readable' => $canRead,
                'exists' => $doesExist,
                'path' => $fullPath,
                'description' => trans('meerkat::validation.' . $langKey),
                'name' => trans('meerkat::validation.' . $langKey . '_name')
            ];
        }

        $view->with([
            'config_directories' => $configurationDirectories,
            'system_information' => $variables
        ]);
    }

    private function directoryExists($path)
    {
        return file_exists($path) && is_dir($path);
    }

}