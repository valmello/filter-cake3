<?PHP
namespace Filter\Controller\Component;
use Cake\Controller\Component;

class FilterComponent extends Component{

    public function initialize(array $config) {
        parent::initialize($config);
    }

    public function __get($method){
        $method = "_get".ucfirst($method);
        if(method_exists($this,$method)) return $this->$method();
        return null;
    }

    protected function _getConditions(){
        $controller = $this->_registry->getController();
        $queries = $controller->request->query;
        $conditions = [];
        if(empty($controller->filters)) return null;
        if(empty($queries)) return null;
        foreach($controller->filters as $key=>$filter){
            if(is_numeric($key) || empty($filter['field']) || empty($queries[$key])) continue;
            if(empty($filter['type'])) $filter['type'] = 'text';
            $method = "__get".ucfirst($filter['type']);
            $filter['value'] = $queries[$key];
            $conditions += $this->$method($filter);
            $controller->request->data[$key] = $queries[$key];
        }
        return $conditions;
    }

    protected function __getText($options){
        $value = preg_replace('/"([\w\d\s-_?!#$]+)"|([\w\d]+)/u','%$1$2%',$options['value']);
        $field = $options['field'];
        return ["{$field} LIKE"=>$value];
    }

    protected function __getRange($options){
        $options +=['separator'=>';'];
        $values = explode($options['separator'],$options['value']);
        return ["{$options['field']} BETWEEN {$values[0]} AND {$values[1]}"];
    }

    protected function __getMultiple($options){
        return ["{$options['field']} IN"=>$options['value']];
    }
}