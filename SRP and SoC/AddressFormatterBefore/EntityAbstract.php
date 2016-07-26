<?php
/**
 * @author Rocket Internet SE
 * @copyright Copyright (c) 2015 Rocket Internet SE, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 * @created 10.02.15
 */

namespace Common\Db\Entity;

use Common\Journal\Entity\Journal;
use Phalcon\DI;
use Phalcon\Text;
use Spot\Entity;
use Spot\EntityInterface;
use Spot\EventEmitter;
use Spot\Exception;
use Spot\MapperInterface;
use Spot\Relation\BelongsTo;
use Spot\Relation\HasOne;

/**
 * Class EntityAbstract
 * @package Common\Db\Entity
 * @method void setCreatedBy($data)
 * @method void setCreatedAt($dateTime)
 * @method void setUpdatedBy($data)
 * @method void setUpdatedAt($dateTime)
 * @method array getCreatedBy()
 * @method \DateTime getCreatedAt()
 * @method array getUpdatedBy()
 * @method \DateTime getUpdatedAt()
 */
abstract class EntityAbstract extends Entity
{
    /**
     * Set it to false in your entity if you don't want to have the strict and automated historical behaviour
     *
     * @var bool
     */
    protected $historical = true;

    /**
     * @return array
     */
    public static function fields()
    {
        return [
            'created_at' => ['type' => 'datetimetz'],
            'created_by' => ['type' => 'json_array'],
            'updated_at' => ['type' => 'datetimetz'],
            'updated_by' => ['type' => 'json_array']
        ];
    }

    /**
     * Get or set property if defined
     *
     * example: getFirstName() will return value of property first_name
     *
     * @param string $method
     * @param array $arguments
     * @return static|mixed
     */
    public function __call($method, $arguments = [])
    {
        if (method_exists($this, $method)) {
            // @todo
            // PHP 5.5 compatibility
            // required for now on AWS
            //return $this->{$method}(...$arguments);

            return call_user_func_array([$this, $method], $arguments);
        }

        preg_match('/^([a-z]+)(.+)/', $method, $matches);
        $action = $matches[1];
        $field = Text::uncamelize($matches[2]);

        switch ($action) {
            case 'get':
                return $this->get($field);
            case 'set':
                //type
                return $this->set($field, $arguments[0]);
            case 'has':
            case 'is':
                $value = $this->get($field);
                return !empty($value);
            default:
                throw new \BadMethodCallException(sprintf('Unknown method: %s (for field: %s)', $method, $field));
        }
    }

    /**
     * Overwritten because of consistent __set __get behaviour
     * Is returned by reference
     *
     * @param string $field
     * @return bool|mixed|null
     */
    public function &__get($field)
    {
        // needed because it is returned by reference
        $returnedField = $this->get($field);

        return $returnedField;
    }

    /**
     * Overwritten because of consistent __set __get behaviour
     * in case of retrieving a BelongTo object from a relation field it is casted to entity
     * collections will not be converted
     *
     * @param string $field
     * @return bool|mixed
     */
    public function get($field)
    {
        $returnedField = parent::__get($field);

        if ($returnedField instanceof BelongsTo || $returnedField instanceof HasOne) {
            $returnedField = $returnedField->entity();
        }

        return $returnedField;
    }

    /**
     * Overwrite parent::set to add strict functionality for set field and relation and to return return values
     * of declared set functions for fields
     *
     * Modified content of parent::set
     * Solve bug which causes overwriting of values when function for property is declared
     * AND
     * added return $value to be able to use fluent interface
     *
     * @param string $field
     * @param mixed $value
     * @param bool $modified
     * @return static|mixed
     */
    public function set($field, $value, $modified = true)
    {
        $returnValue = $this->callMethodIfExists($field, $value, $modified);
        if (!empty($returnValue)) {
            return $returnValue;
        }

        if (!empty(static::$relationFields[get_class($this)])
            && in_array($field, static::$relationFields[get_class($this)])
        ) {
            $this->relation($field, $value);
        } elseif (array_key_exists($field, $this->_inSetter) && $this->_inSetter[$field] === true
            || !array_key_exists($field, $this->_inSetter) && $modified
        ) {
            if ($this->hasField($field)) {
                $this->_dataModified[$field] = $value;
            }
        } else {
            if ($this->hasField($field)) {
                $this->_data[$field] = $value;
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param bool $modified
     * @return bool|mixed
     */
    protected function callMethodIfExists($field, $value, $modified)
    {
        // Custom setter method
        $setterMethod = 'set' . Text::camelize($field);
        if (!array_key_exists($field, $this->_inSetter) && method_exists($this, $setterMethod)) {
            $this->_inSetter[$field] = $modified;
            $returnValue = call_user_func([$this, $setterMethod], $value);
            unset($this->_inSetter[$field]);

            return $returnValue;
        }

        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasField($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * Workaround for testing, as the static method cannot be mocked.
     * @return array
     */
    public function getFields()
    {
        return $this::fields();
    }

    /**
     * @param EventEmitter $eventEmitter
     * @throws \Spot\Exception
     */
    public static function events(EventEmitter $eventEmitter)
    {
        $eventEmitter->on('beforeInsert', function (EntityInterface $entity, MapperInterface $mapper) {
            if ($entity instanceof Journal) {
                return true;
            }
            /** @var EntityAbstract $entity */
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy(static::getDI()->get('auth')->toArray());

            return true;
        });

        $eventEmitter->on('beforeSave', function (EntityInterface $entity, MapperInterface $mapper) {
            if ($entity instanceof Journal) {
                return true;
            }

            //TODO we could also implement a entity validation here
            $valid = true;

            /** @var EntityAbstract $entity */
            if ($entity->hasStrictHistoricalBehaviour()) {
                $valid = $entity->validateHistorical();
            }

            if ($entity->isModified() || $entity->isNew()) {
                $entity->setUpdatedAt(new \DateTime());
                $entity->setUpdatedBy(static::getDI()->get('auth')->toArray());
            }

            return $valid;
        });

        $eventEmitter->on('afterInsert', function (EntityInterface $entity, MapperInterface $mapper) {
            if ($entity instanceof Journal || $entity instanceof Migration) {
                return true;
            }
            static::getDI()->get('journal')->add($entity, 'created');

            return true;
        });

        $eventEmitter->on('afterUpdate', function (EntityInterface $entity, MapperInterface $mapper) {
            if ($entity instanceof Journal || $entity instanceof Migration) {
                return true;
            }
            static::getDI()->get('journal')->add($entity);

            return true;
        });

        $eventEmitter->on('afterDelete', function (EntityInterface $entity, MapperInterface $mapper) {
            if ($entity instanceof Journal || $entity instanceof Migration) {
                return true;
            }
            static::getDI()->get('journal')->add($entity, 'deleted');

            return true;
        });
    }

    /**
     * @return \Phalcon\DiInterface
     */
    protected static function getDI()
    {
        return DI::getDefault();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function validateHistorical()
    {
        $valid = $this->hasField('created_at') && $this->hasField('created_by')
            && $this->hasField('updated_at') && $this->hasField('updated_by');

        if (!$valid) {
            throw new Exception(
                sprintf(
                    'Entity "%s" must have the fields created_at, created_by, updated_at and updated_by',
                    get_class($this)
                )
            );
        }

        return $valid;
    }

    /**
     * @return bool
     */
    public function hasStrictHistoricalBehaviour()
    {
        return $this->historical;
    }

    /**
     * @return EntityAbstract|null
     */
    public function getCreatedByReferenceEntity()
    {
        return $this->getHistoricalReferenceEntity($this->getCreatedBy());
    }

    /**
     * @return EntityAbstract|null
     */
    public function getCreatedByReferenceId()
    {
        $referenceData = $this->getCreatedBy();
        if (!empty($referenceData['reference_id'])) {
            return $referenceData['reference_id'];
        }
        return null;
    }

    /**
     * @return EntityAbstract|null
     */
    public function getUpdatedByReferenceEntity()
    {
        return $this->getHistoricalReferenceEntity($this->getUpdatedBy());
    }

    /**
     * @param array $referenceData
     * @return EntityAbstract|null
     */
    protected function getHistoricalReferenceEntity($referenceData)
    {
        if (empty($referenceData['reference_entity_class']) || empty($referenceData['reference_id'])) {
            return null;
        }

        $service = $this->getDI()->get('dbResolver')->get($referenceData['reference_entity_class']);
        $entity = $service->findById($referenceData['reference_id']);
        return $entity;
    }

    /**
     * Render entity as string
     *
     * @param string $format Use entity fields surrounded by curly braces, e.g. {fieldone}, {fieldTwo} etc
     * @return mixed
     */
    public function getFormatted($format = '')
    {
        return preg_replace_callback(
            '/({(\w+)})/',
            function ($matches) {
                return $this->get($matches[2]);
            },
            $format
        );
    }
}
