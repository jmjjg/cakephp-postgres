<?php
/**
 * Source code for the Postgres.AutovalidateBehavior class.
 *
 */
namespace Postgres\Model\Behavior;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;

/**
 * The Postgres.AutovalidateBehavior loads check constraints read from the table
 * it is attached to and adds them to the validator during the buildValidator()
 * callback.
 *
 * Configuration keys:
 *  - accepted: validation rules will only be added if the name of the validator
 *    is in this array. Default is NULL which means any name.
 *  - cache: wether or not to use the cache
 *  - domain: the domain name for translations
 */
class AutovalidateBehavior extends Behavior
{

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'accepted' => null,
        'cache' => null,
        'domain' => 'postgres'
    ];

    /**
     * Cache key name.
     *
     * @see class::cacheKey()
     * @var array
     */
    protected $cacheKey = null;

    /**
     * Initialize hook
     *
     * @param array $config The config for this behavior.
     * @return void
     * @throws \RuntimeException
     */
    public function initialize(array $config)
    {
        $expected = 'Postgres\Database\Driver\Postgres';
        if (!is_a($this->_table->connection()->driver(), $expected)) {
            $msgstr = sprintf(
                'Driver for table "%s" is not an instance of "%s" (using "%s" connection)',
                $this->_table->table(),
                $expected,
                $this->_table->connection()->configName()
            );
            throw new \RuntimeException($msgstr);
        }

        parent::initialize($config);
        $this->config($config);
    }

    /**
     * Read check constraints starting with "cakephp_validate_" for the current
     * table.
     *
     * @return array
     */
    protected function constraints()
    {
        $schema = Hash::get($this->_table->connection()->config(), 'schema') ? : 'public';

        $conditions = [
            "pg_namespace.nspname = '{$schema}'",
            "pg_class.relname = '{$this->_table->table()}'",
            "pg_constraint.consrc ~ '^cakephp_validate_'"
        ];

        return Hash::extract($this->_table->connection()->driver()->constraints($conditions), '{n}.source');
    }

    /**
     * Returns the formatted error message for a given rule.
     *
     * @param string $function The name of the validation rule
     * @param array $params The validation parameters
     * @return string
     */
    protected function formatMessage($function, array $params)
    {
        $messageParams = [];
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $messageParams[$key] = implode(', ', $param);
            } else {
                $messageParams[$key] = $param;
            }
        }

        return call_user_func_array('sprintf', array_merge((array)__d($this->config('domain'), "Validate::{$function}"), $messageParams));
    }

    /**
     * Returns the extracted parameters from a parameters string.
     *
     * @param string $params The parameters string
     * @return array
     */
    protected function formatParams($params)
    {
        //Clean parameters
        $params = preg_replace('/(\'[^\']+\')::[^ ,\]]+/', '\1', $params);

        if (preg_match('/^ARRAY\[(.*)\]$/', $params, $arrayParams)) {
            $params = [preg_split('/, /', $arrayParams[1], null, PREG_SPLIT_NO_EMPTY)];
        } else {
            $params = preg_split('/, /', $params, null, PREG_SPLIT_NO_EMPTY);
        }

        //Trim quotes around values
        foreach (Hash::flatten($params) as $path => $value) {
            $params = Hash::insert($params, $path, preg_replace('/^\'(.*)\'$/', '\1', $value));
        }

        return $params;
    }

    /**
     * Returns the cleaned up field name for a constraint.
     *
     * @param string $field The original field name
     * @return array
     */
    protected function formatField($field)
    {
        return preg_replace('/\(([^\)]+)\)::[^ ,]+/', '\1', $field);
    }

    /**
     * Parses a check constraint source code.
     *
     * @todo: ça et les méthodes pour formater dans un utilitaire ?
     *
     * @param string $constraint Le code source de la contrainte
     * @return array
     */
    protected function getValidationRuleFromConstraint($constraint)
    {
        $result = [];

        if (preg_match('/^cakephp_validate_(?<function>[^\(]+)\((?<field>[^, ]+)(, *){0,1}(?<params>.*)\)/', $constraint, $matches)) {
            $function = Inflector::variable($matches['function']);
            $field = $this->formatField($matches['field']);
            $params = $this->formatParams($matches['params']);

            $result = [
                $field,
                $function,
                [
                    'rule' => array_merge((array)$function, $params),
                    'message' => $this->formatMessage($function, $params)
                ]
            ];
        }

        return $result;
    }

    /**
     * Reads the cache config key: if NULL (default) returns false when in debug
     * mode, true otherwise or returns wether the cache key was set to TRUE.
     *
     * @return bool
     */
    protected function useCache() {
        $result = $this->config('cache');
        return $result === null ? Configure::read('debug') === false :  $result === true;
    }

    /**
     * Return the cache key name for the current table, language and domain.
     *
     * @return string
     */
    protected function cacheKey()
    {
        if ($this->cacheKey === null) {
            $plugin = Inflector::underscore(namespaceRoot(__CLASS__));//FIXME: Plugin Database / redefine
            $class = Inflector::underscore(namespaceTail(__CLASS__));//FIXME: Plugin Database / redefine
            $connection = Inflector::underscore($this->_table->connection()->configName());
            $table = Inflector::underscore($this->_table->table());
            $lang = strtolower(ini_get('intl.default_locale'));
            $domain = $this->config('domain');
            $this->cacheKey = $plugin . '_' . $class . '_' . $connection . '_' . $table . '_' . $lang . '_' . $domain;
        }

        return $this->cacheKey;
    }

    /**
     * Returns validation rules read from the check constraints on the table, to
     * use when adding rules to the validator.
     *
     * @return array
     */
    protected function getValidationRules()
    {
        $cacheKey = $this->cacheKey();
        $rules = Cache::read($cacheKey);

        if ($rules === false || $this->useCache() === false) {
            $rules = [];

            foreach ($this->constraints() as $constraint) {
                $rule = $this->getValidationRuleFromConstraint($constraint);
                if (!empty($rule)) {
                    $rules[] = $rule;
                }
            }

            Cache::write($cacheKey, $rules);
        }

        return $rules;
    }

    /**
     * Automatically add the translated validation rules if the event is not stopped
     * and if the name of the validator is amongst the accepted ones.
     *
     * @source http://book.cakephp.org/3.0/en/core-libraries/validation.html#validating-data
     *
     * @param \Cake\Event\Event $event Fired when the validator object identified by $name is being built.
     * @param \Cake\Validation\Validator $validator The validator object
     * @param string $name The name of the validator oject
     * @return void
     */
    public function buildValidator(\Cake\Event\Event $event, \Cake\Validation\Validator $validator, $name)
    {
        $accepted = $this->config('accepted');

        if ($event->isStopped() === false && ($accepted === null || in_array($name, (array)$accepted))) {
            foreach ($this->getValidationRules() as $validationRule) {
                call_user_func_array([$validator, 'add'], $validationRule);
            }
        }
    }
}
