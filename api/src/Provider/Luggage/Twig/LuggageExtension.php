<?php

namespace Provider\Luggage\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig_SimpleFunction;

class LuggageExtension extends \Twig_Extension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('asset', array($this, 'getAssetPath')),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('powerLevelClass', array($this, 'getPowerLevelClass')),
            new \Twig_SimpleFilter('avatar_path', array($this, 'getAvatarPath')),
        );
    }

    public function getAssetPath($path)
    {
        return $this->requestStack->getCurrentRequest()->getBasePath().'/'.$path;
    }

    public function getAvatarPath($number)
    {
        return sprintf('img/avatar%s.png', $number);
    }


    public function getName()
    {
        return 'luggage';
    }


}