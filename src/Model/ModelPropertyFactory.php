<?php
declare(strict_types=1);

namespace MixerApi\Core\Model;

use Cake\Database\Schema\TableSchema;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Builds ModelProperty
 *
 * @see ModelProperty
 */
class ModelPropertyFactory
{
    /**
     * @param \Cake\Database\Schema\TableSchema $schema cake TableSchema instance
     * @param \Cake\ORM\Table $table cake Table instance
     * @param string $columnName the tables column name
     * @param \Cake\Datasource\EntityInterface $entity EntityInterface
     */
    public function __construct(
        private TableSchema $schema,
        private Table $table,
        private string $columnName,
        private EntityInterface $entity,
    ) {
    }

    /**
     * @return \MixerApi\Core\Model\ModelProperty
     */
    public function create(): ModelProperty
    {
        $column = $this->schema->getColumn($this->columnName);
        $default = $column['default'] ?? '';

        return (new ModelProperty())
            ->setName($this->columnName)
            ->setType($this->schema->getColumnType($this->columnName))
            ->setDefault((string)$default)
            ->setIsPrimaryKey($this->isPrimaryKey())
            ->setIsHidden(in_array($this->columnName, $this->entity->getHidden()))
            ->setIsAccessible($this->isAccessible())
            ->setValidationSet($this->table->validationDefault(new Validator())->field($this->columnName));
    }

    /**
     * Checks if this column is part of the primary key.
     *
     * @return bool
     */
    private function isPrimaryKey(): bool
    {
        return in_array($this->columnName, $this->schema->getPrimaryKey());
    }

    /**
     * Returns accessibility of the property.
     *
     * @link https://book.cakephp.org/4/en/orm/entities.html#mass-assignment
     * @return bool
     */
    private function isAccessible(): bool
    {
        $accessible = $this->entity->getAccessible();
        if (isset($accessible[$this->columnName]) && is_bool($accessible[$this->columnName])) {
            return $accessible[$this->columnName];
        }

        if (isset($accessible['*']) && is_bool($accessible['*'])) {
            return $accessible['*'];
        }

        return false;
    }
}
