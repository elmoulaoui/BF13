<?php
namespace BF13\Component\Datagrid\Model;
use Doctrine\ORM\Tools\Pagination\Paginator;
/**
 *
 * @author FYAMANI
 *
 */

class DatagridEntity extends DatagridObject
{
    public $column_headers = array();

    public $column_values = array();

    public $ref;

    public $raw_columns = array();

    public $total_pages = 0;

    public $current_page = 0;

    public $offset = 0;

    public function __construct($DatagridSettings, $kernel)
    {
        $this->config = $DatagridSettings;

        $this->DomainRepository = $kernel->getContainer()->get('bf13.dom.repository');

        $this->setColumnHeaders($DatagridSettings->getColumns());
    }

    protected function setColumnHeaders($columns)
    {
        $this->raw_columns = $columns;

        $labels = array();

        foreach($columns as $key => $opt) {

            if(!array_key_exists('hidden', $opt) || true !== $opt['hidden'])
            {
                $label = '' != array_key_exists('label', $opt) && trim($opt['label']) ? $opt['label'] : $key;

                $labels[$key] = $label;
            }
        }

        $this->column_headers = $labels;
    }

    public function loadData($data, $pager = null)
    {
        $fields = array_keys($this->raw_columns);

        $query = $this->DomainRepository
        ->getQuerizer($this->config->getSource())
            ->datafields($fields);

        if($data && $condition = $this->config->getCondition() ) {
            $query->conditions(array($condition => $data));
        }

        if(! is_null($pager))
        {
            $this->offset = ($pager['page'] - 1) * $pager['max_items'];

            $this->bind($query->resultsWithPager($this->offset, $pager['max_items']));

            $totalitems = $query->totalResults();

            $total = isset($totalitems['total']) ? $totalitems['total'] : 0;

            $this->total_pages = ceil($total / $pager['max_items']);

            $this->current_page = ($this->offset / $pager['max_items']) + 1;

        } else {

            $this->bind($query->results());
        }
    }

    public function updateConfig($config)
    {
        if(isset($config['source']))
        {
            $this->config->setSource($config['source']);
        }
        if(isset($config['condition']))
        {
            $this->config->setCondition($config['condition']);
        }
    }

    public function totalPages()
    {
        return $this->total_pages;
    }

    public function currentPage()
    {
        return $this->current_page;
    }

    public function previousPages($range = 3)
    {
        $offset = array();

        $limit = $this->current_page - $range;

        for($i=$limit; $i < $this->current_page; $i++)
        {
            if(0 >= $i)
            {
                continue;
            }

            $offset[] = $i;
        }
        return $offset;
    }

    public function nextPages($range = 3)
    {
        $offset = array();

        $limit = $this->current_page + $range;

        for($i=$this->current_page + 1; $i <= $this->total_pages; $i++)
        {
            $offset[] = $i;

            if($limit == $i)
            {
                break;
            }
        }
        return $offset;
    }
}
