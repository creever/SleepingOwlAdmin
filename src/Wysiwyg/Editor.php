<?php

namespace SleepingOwl\Admin\Wysiwyg;

use Illuminate\Config\Repository;
use KodiCMS\Assets\Contracts\MetaInterface;
use KodiCMS\Assets\Package;
use Meta;
use SleepingOwl\Admin\Contracts\Wysiwyg\WysiwygEditorInterface;
use SleepingOwl\Admin\Contracts\Wysiwyg\WysiwygFilterInterface;

/**
 * @method Editor js($handle = false, $src = null, $dependency = null, $footer = false)
 * @method Editor css($handle = null, $src = null, $dependency = null, array $attributes = [])
 */
final class Editor implements WysiwygEditorInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var WysiwygFilterInterface
     */
    private $filter;

    /**
     * @var Repository
     */
    private $config;

    /**
     * @var bool
     */
    private $used = false;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var MetaInterface
     */
    private $meta;

    /**
     * @param string $id
     * @param string $name
     * @param WysiwygFilterInterface|null $filter
     * @param Package $package
     * @param MetaInterface $meta
     * @param array $config
     */
    public function __construct($id, $name, WysiwygFilterInterface $filter, Package $package, MetaInterface $meta, array $config = [])
    {
        $this->id = $id;
        $this->package = $package;
        $this->name = is_null($name) ? studly_case($id) : $name;
        $this->filter = is_null($filter) ? $this->loadDefaultFilter() : $filter;
        $this->config = new Repository($config);
        $this->meta = $meta;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return WysiwygFilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return Repository
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public function applyFilter($text)
    {
        return $this->getFilter()->apply($text);
    }

    public function load()
    {
        $this->meta->loadPackage($this->getId());

        $this->used = true;
    }

    public function unload()
    {
        $this->used = false;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'   => $this->getId(),
            'name' => $this->getName(),
        ];
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, ['js', 'css'])) {
            call_user_func_array([$this->getPackage(), $method], $arguments);

            return $this;
        }

        throw new \BadMethodCallException("Call to undefined method [{$method}]");
    }
}
