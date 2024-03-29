<?php
namespace BF13\Component\Breadcrumb;

use Symfony\Component\Yaml\Yaml;

class Breadcrumb
{
    protected $root_node;

    protected $kernel;

    protected $active_route = null;

    protected $active_menu_root = null;

    protected $roots = null;

    protected $data = array();

    public function __construct($settingsFile)
    {
        $this->data = Yaml::parse($settingsFile);
    }

    public function setRootNode($nodeName)
    {
        $this->root_node = $nodeName;
    }

    public function setActiveRoute($route)
    {
        $this->active_route = $route;
    }

    protected function setActiveRoot()
    {
        $data = $this->data[$this->root_node];

        foreach ($data as $menu_root => $options) {

            if (array_key_exists('default', $options)) {

                $this->active_menu_root = $menu_root;
            }

            if (array_key_exists('nodes', $options)) {

                if (array_key_exists($this->active_route, $options['nodes'])) {
                    $this->active_menu_root = $menu_root;

                    return;
                }

                foreach ($options['nodes'] as $route_alias => $child) {
                    if (array_key_exists('linked', $child) && in_array($this->active_route, $child['linked'])) {
                        $this->active_menu_root = $menu_root;

                        $this->active_route = $route_alias;

                        return;
                    }
                }
            }
        }
    }

    protected function setRoots()
    {
        $data = $this->data[$this->root_node];

        $roots = array();

        foreach ($data as $key => $item) {

            $roots[$key] = $item;

            if ($key == $this->getActiveRoot()) {

                $roots[$key]['active'] = true;

            } else {

                $roots[$key]['active'] = false;
            }

            if (array_key_exists('nodes', $item)) {

                $nodes = array_keys($item['nodes']);

                $first = array_shift($nodes);

                $roots[$key]['route'] = $first;

                $roots[$key]['nodes'] = 1 < sizeOf($item['nodes']);
            }
        }

        $this->roots = $roots;
    }

    public function getActiveRoot()
    {
        if (is_null($this->active_menu_root)) {

            $this->setActiveRoot();
        }

        return $this->active_menu_root;
    }

    public function getRoots()
    {
        if (is_null($this->roots)) {

            $this->setRoots();
        }

        return $this->roots;
    }

    public function getRootName($root = null)
    {
        if (is_null($root)) {

            $root = $this->getActiveRoot();
        }

        return $this->data[$this->root_node][$root]['label'];
    }

    public function getRootIcon($root = null)
    {
        if (is_null($root)) {

            $root = $this->getActiveRoot();
        }

        return $this->data[$this->root_node][$root]['icon'];
    }

    public function getChilds($root = null)
    {
        if (is_null($root)) {

            $root = $this->getActiveRoot();
        }

        if(is_null($root))
        {
            return array();
        }

        $nodes = $this->data[$this->root_node][$root]['nodes'];

        $data = array();

        $defaults = array(
            'label' => 'unknow',
            'active' => false,
            'target' => 'page',
            'hidden' => false,
        );

        foreach ($nodes as $route => $child) {

            $defaults['active'] = ($this->active_route == $route);

            $data[$child['submenu']][$route] = array_merge($defaults, $child);
        }

        return $data;
    }

    public function getActivePath()
    {
        $root = $this->getActiveRoot();

        $label_root = $this->getRootName();

        $path = array();

        $path[] = array('label' => $label_root, 'route' => $root, 'last' => false);

        $nodes = $this->data[$this->root_node][$root]['nodes'];

        foreach ($nodes as $route => $child) {

            if ($this->active_route == $route) {

                $path[] = array('label' => $child['label'], 'route' => $route, 'last' => true);

                return $path;
            }
        }
    }

    public function getRaw()
    {
        return $this->data;
    }
}
