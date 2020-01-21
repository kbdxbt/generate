<?php

namespace Kbdxbt\Generate\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Requeset.
 */
class Request extends GeneratorCommand
{
    // The following are the supported abstract column data types.
    const TYPE_PK = 'pk';
    const TYPE_UPK = 'upk';
    const TYPE_BIGPK = 'bigpk';
    const TYPE_UBIGPK = 'ubigpk';
    const TYPE_CHAR = 'char';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_TINYINT = 'tinyint';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TIME = 'time';
    const TYPE_DATE = 'date';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MONEY = 'money';
    const TYPE_JSON = 'json';

    /**
     * @var string
     */
    protected $name = 'generate:request';

    /**
     * @var string
     */
    protected $description = 'Create a new request class';

    /**
     * @var string
     */
    protected $type = 'Request';

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        $replace = $this->buildModelReplacements($replace);

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     *
     * @return array
     */
    protected function buildModelReplacements(array $replace = [])
    {
        $tableClass = $this->option('table');
        $fields = $this->parseFields($tableClass);

        return array_merge($replace, [
            'DummyFullModelClass' => $tableClass,
            'DummyModelClass'     => class_basename($tableClass),
            'DummyModelVariable'  => lcfirst(class_basename($tableClass)),
            'DummyRule'  => $fields['rule'] ?? '',
            'DummyAttribute'  => $fields['attribute'] ?? '',
        ]);
    }

    /**
     * Get the fully-qualified fields class name.
     *
     * @param string $table
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function parseFields($table)
    {
        $columns = \Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($table);

        if (empty($columns)) {
            return;
        }

        foreach ($columns as $column) {
            if ($column->getAutoincrement()) {
                continue;
            }

            // 处理是否必填规则
            if ($column->getNotnull() && $column->getDefault() == null) {
                $rules[$column->getName()][] = 'required';
            } else {
                $rules[$column->getName()][]  = 'sometimes';
            }

            // 处理类型规则
            switch ($column->getType()->getName()) {
                case self::TYPE_SMALLINT:
                case self::TYPE_INTEGER:
                case self::TYPE_BIGINT:
                case self::TYPE_TINYINT:
                case self::TYPE_BOOLEAN:
                    $rules[$column->getName()][] = 'integer';
                    break;
                case self::TYPE_FLOAT:
                case self::TYPE_DOUBLE:
                case self::TYPE_DECIMAL:
                case self::TYPE_MONEY:
                    $rules[$column->getName()][] = 'numeric';
                    break;
                case self::TYPE_DATE:
                case self::TYPE_TIME:
                case self::TYPE_DATETIME:
                case self::TYPE_TIMESTAMP:
                    $rules[$column->getName()][] = 'date';
                    break;
                case self::TYPE_JSON:
                    $rules[$column->getName()][] = 'json';
                    break;
                default: // strings
                    if ($column->getLength() > 0) {
                        $rules[$column->getName()][] = 'string';
                    }
            }

            $rule_generals = [
                '/(.*)email$/U' => 'email',
                '/(.*)mobile$/U' => 'mobile',
                '/(.*)ip$/U' => 'ip',
            ];
            // 处理通用自定义路由规则
            foreach ($rule_generals as $key => $val) {
                if (preg_match($key, $column->getName())) {
                    $rules[$column->getName()][] = $val;
                }
            }

            // 生成长度规则
            if ($column->getLength() > 0) {
                $rules[$column->getName()][]  = 'max:'.$column->getLength();
            }

            // 默认去掉注释内容
            if ($end = strpos($column->getComment(), '[')) {
                preg_match_all('/#(.*):/U', substr($column->getComment(), $end), $scores);

                // 生成in规则
                if (isset($scores[1])) {
                    $rules[$column->getName()][]  = 'in:'.implode($scores[1], ',');
                }

                $messages[$column->getName()] = substr($column->getComment(), 0, $end);
            } else {
                $messages[$column->getName()] = $column->getComment();
            }
        }

        $fields['rule'] = $fields['attribute'] = '';
        foreach ($rules as $key => $rule) {
            $fields['rule'] .= "'".$key."' => '".implode($rule, '|')."',\n            ";
        }
        $fields['rule'] = rtrim($fields['rule'], ",\n            ");

        foreach ($messages as $key => $message) {
            $fields['attribute'] .= "'".$key."' => '".$message."',\n            ";
        }
        $fields['attribute'] = rtrim($fields['attribute'], ",\n            ");

        return $fields;
    }

    /**
     * Get the fully-qualified table class name.
     *
     * @param string $table
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function parseTable($table)
    {
        $columns = \Schema::getColumnListing($table);

        $fields = '';
        foreach ($columns as $column) {
            $fields .= "'".$column."', ";
        }
        $fields = rtrim($fields, ', ');

        return $fields;
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/request.stub';
    }

    /**
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['table', 't', InputOption::VALUE_REQUIRED, 'The table name.'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the repository already exists'],
        ];
    }
}
