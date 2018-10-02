<?php

namespace Provider\Luggage;

use Provider\DataFixtures\FixturesManager;
use Provider\Luggage\Twig\LuggageExtension;
use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureServices();
        $this->configureProviders();
    }

    /**
     * Dynamically finds all *Controller.php files in the Controller directory,
     * instantiates them, and mounts their routes.
     *
     * This is done so we can easily create new controllers without worrying
     * about some of the Silex mechanisms to hook things together.
     */
    public function mountControllers()
    {
        $controllerPath = 'src/Provider/Luggage/Controller';
        $finder = new Finder();
        $finder->in($this['root_dir'].'/'.$controllerPath)
            ->name('*Controller.php')
        ;

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            // e.g. Api/FooController.php
            $cleanedPathName = $file->getRelativePathname();
            // e.g. Api\FooController.php
            $cleanedPathName = str_replace('/', '\\', $cleanedPathName);
            // e.g. Api\FooController
            $cleanedPathName = str_replace('.php', '', $cleanedPathName);

            $class = 'Provider\\Luggage\\Controller\\'.$cleanedPathName;

            // don't instantiate the abstract base class
            $refl = new \ReflectionClass($class);
            if ($refl->isAbstract()) {
                continue;
            }

            $this->mount('/', new $class($this));
        }
    }

    private function configureParameters()
    {
        $this['root_dir'] = __DIR__.'/../../..';
        $this['sqlite_path'] = $this['root_dir'].'/data/luggage-api.sqlite';
    }

    private function configureServices()
    {
        $app = $this;


        $this['fixtures_manager'] = $this->share(function () use ($app) {
            return new FixturesManager($app);
        });

        $this['twig.luggage_extension'] = $this->share(function() use ($app) {
            return new LuggageExtension(
                $app['request_stack']
            );
        });

    }


    private function configureProviders()
    {
        // URL generation
        $this->register(new UrlGeneratorServiceProvider());

        // Twig
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => $this['root_dir'].'/views',
        ));
        $app['twig'] = $this->share($this->extend('twig', function(\Twig_Environment $twig, $app) {
            $twig->addExtension($app['twig.luggage_extension']);

            return $twig;
        }));

        // Sessions
        $this->register(new SessionServiceProvider());

        // Doctrine DBAL
        $this->register(new DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver'   => 'pdo_sqlite',
                'path'     => $this['sqlite_path'],
            ),
        ));

        // Monolog
        $this->register(new MonologServiceProvider(), array(
            'monolog.logfile' => $this['root_dir'].'/logs/development.log',
        ));

        // Validation
        $this->register(new ValidatorServiceProvider());
        // configure validation to load from a YAML file
        $this['validator.mapping.class_metadata_factory'] = $this->share(function() {
            return new ClassMetadataFactory(
                new AnnotationLoader($this['annotation_reader'])
            );
        });

    }

}