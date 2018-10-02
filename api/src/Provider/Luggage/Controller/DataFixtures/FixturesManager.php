<?php

namespace Provider\DataFixtures;


use Silex\Application;
use Symfony\Component\Filesystem\Filesystem;

class FixturesManager
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        // 1) check DB permissions
        $dbPath = $this->app['root_dir'].'/data';
        $dbDir = dirname($dbPath);

        // make sure the directory is available and writeable
        $filesystem = new Filesystem();
        $filesystem->mkdir($dbPath);
        $filesystem->chmod($dbDir, 0777, 0000, true);
        if (!is_writable($dbDir)) {
            throw new \Exception('Unable to write to '.$dbPath);
        }
    }


}