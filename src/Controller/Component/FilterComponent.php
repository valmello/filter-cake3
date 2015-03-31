<?php
namespace Filter\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

class FilterComponent extends Component
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $controller = $this->_registry->getController();
        if(empty($controller->filters)) return null;
        $query = &$controller->request->query;
        $data = &$controller->request->data;
        $paginate = &$controller->paginate;
        foreach($controller->filters as $key=>$filter){
            if(is_numeric($key) || empty($filter) || empty($query[$key])) continue;
            if(empty($filter['type'])) $filter['type'] = 'text';
            if(empty($paginate[$filter['model']]['conditions'])) $paginate[$filter['model']]['conditions'] = [];
            $condition = &$paginate[$filter['model']]['conditions'];
            $method = '_get' . ucfirst($filter['type']);
            $filter['value'] = $query[$key];
            $condition[] = $this->$method($filter);
            $data[$key] = $query[$key];
        }
    }

    protected function _getRange($options)
    {
        $options +=['separator'=>';'];
        $values = explode($options['separator'],$options['value']);
        return "{$options['model']}.{$options['field']} BETWEEN {$values[0]} AND {$values[1]}";
    }

    protected function _getText($options)
    {
        $value = preg_replace('/"([\w\d\s-_?!#$]+)"|([\w\d]+)/u','%$1$2%',$options['value']);
        return "{$options['model']}.{$options['field']} LIKE {$value}";
    }

    protected function _getMultiple($options)
    {
        return "{$options['model']}.{$options['field']} IN {$options['value']}";
    }


}