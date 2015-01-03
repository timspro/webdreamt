<?php

namespace Base;

use \Contract as ChildContract;
use \ContractQuery as ChildContractQuery;
use \Customer as ChildCustomer;
use \CustomerQuery as ChildCustomerQuery;
use \DateTime;
use \Exception;
use \PDO;
use Map\CustomerTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;

/**
 * Base class that represents a row from the 'customer' table.
 *
 *
 *
* @package    propel.generator..Base
*/
abstract class Customer implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Map\\CustomerTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the first_name field.
     * @var        string
     */
    protected $first_name;

    /**
     * The value for the last_name field.
     * @var        string
     */
    protected $last_name;

    /**
     * The value for the company_name field.
     * @var        string
     */
    protected $company_name;

    /**
     * The value for the phone field.
     * @var        string
     */
    protected $phone;

    /**
     * The value for the active field.
     * @var        boolean
     */
    protected $active;

    /**
     * The value for the type field.
     * @var        string
     */
    protected $type;

    /**
     * The value for the created_at field.
     * Note: this column has a database default value of: NULL
     * @var        \DateTime
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * Note: this column has a database default value of: NULL
     * @var        \DateTime
     */
    protected $updated_at;

    /**
     * @var        ObjectCollection|ChildContract[] Collection to store aggregation of ChildContract objects.
     */
    protected $collContractsRelatedByBuyerCustomerId;
    protected $collContractsRelatedByBuyerCustomerIdPartial;

    /**
     * @var        ObjectCollection|ChildContract[] Collection to store aggregation of ChildContract objects.
     */
    protected $collContractsRelatedBySellerCustomerId;
    protected $collContractsRelatedBySellerCustomerIdPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildContract[]
     */
    protected $contractsRelatedByBuyerCustomerIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildContract[]
     */
    protected $contractsRelatedBySellerCustomerIdScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->created_at = PropelDateTime::newInstance(NULL, null, 'DateTime');
        $this->updated_at = PropelDateTime::newInstance(NULL, null, 'DateTime');
    }

    /**
     * Initializes internal state of Base\Customer object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>Customer</code> instance.  If
     * <code>obj</code> is an instance of <code>Customer</code>, delegates to
     * <code>equals(Customer)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|Customer The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [first_name] column value.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Get the [last_name] column value.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Get the [company_name] column value.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * Get the [phone] column value.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get the [active] column value.
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Get the [active] column value.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getActive();
    }

    /**
     * Get the [type] column value.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTime ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTime ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Set the value of [id] column.
     *
     * @param  int $v new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[CustomerTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [first_name] column.
     *
     * @param  string $v new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setFirstName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->first_name !== $v) {
            $this->first_name = $v;
            $this->modifiedColumns[CustomerTableMap::COL_FIRST_NAME] = true;
        }

        return $this;
    } // setFirstName()

    /**
     * Set the value of [last_name] column.
     *
     * @param  string $v new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setLastName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->last_name !== $v) {
            $this->last_name = $v;
            $this->modifiedColumns[CustomerTableMap::COL_LAST_NAME] = true;
        }

        return $this;
    } // setLastName()

    /**
     * Set the value of [company_name] column.
     *
     * @param  string $v new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setCompanyName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->company_name !== $v) {
            $this->company_name = $v;
            $this->modifiedColumns[CustomerTableMap::COL_COMPANY_NAME] = true;
        }

        return $this;
    } // setCompanyName()

    /**
     * Set the value of [phone] column.
     *
     * @param  string $v new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setPhone($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->phone !== $v) {
            $this->phone = $v;
            $this->modifiedColumns[CustomerTableMap::COL_PHONE] = true;
        }

        return $this;
    } // setPhone()

    /**
     * Sets the value of the [active] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param  boolean|integer|string $v The new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setActive($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->active !== $v) {
            $this->active = $v;
            $this->modifiedColumns[CustomerTableMap::COL_ACTIVE] = true;
        }

        return $this;
    } // setActive()

    /**
     * Set the value of [type] column.
     *
     * @param  string $v new value
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setType($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->type !== $v) {
            $this->type = $v;
            $this->modifiedColumns[CustomerTableMap::COL_TYPE] = true;
        }

        return $this;
    } // setType()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ( ($dt != $this->created_at) // normalized values don't match
                || ($dt->format('Y-m-d H:i:s') === NULL) // or the entered value matches the default
                 ) {
                $this->created_at = $dt;
                $this->modifiedColumns[CustomerTableMap::COL_CREATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ( ($dt != $this->updated_at) // normalized values don't match
                || ($dt->format('Y-m-d H:i:s') === NULL) // or the entered value matches the default
                 ) {
                $this->updated_at = $dt;
                $this->modifiedColumns[CustomerTableMap::COL_UPDATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setUpdatedAt()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->created_at && $this->created_at->format('Y-m-d H:i:s') !== NULL) {
                return false;
            }

            if ($this->updated_at && $this->updated_at->format('Y-m-d H:i:s') !== NULL) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : CustomerTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : CustomerTableMap::translateFieldName('FirstName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->first_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : CustomerTableMap::translateFieldName('LastName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->last_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : CustomerTableMap::translateFieldName('CompanyName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->company_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : CustomerTableMap::translateFieldName('Phone', TableMap::TYPE_PHPNAME, $indexType)];
            $this->phone = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : CustomerTableMap::translateFieldName('Active', TableMap::TYPE_PHPNAME, $indexType)];
            $this->active = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : CustomerTableMap::translateFieldName('Type', TableMap::TYPE_PHPNAME, $indexType)];
            $this->type = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : CustomerTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : CustomerTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 9; // 9 = CustomerTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Customer'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CustomerTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildCustomerQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collContractsRelatedByBuyerCustomerId = null;

            $this->collContractsRelatedBySellerCustomerId = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Customer::setDeleted()
     * @see Customer::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CustomerTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildCustomerQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(CustomerTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $isInsert = $this->isNew();
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                CustomerTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->contractsRelatedByBuyerCustomerIdScheduledForDeletion !== null) {
                if (!$this->contractsRelatedByBuyerCustomerIdScheduledForDeletion->isEmpty()) {
                    \ContractQuery::create()
                        ->filterByPrimaryKeys($this->contractsRelatedByBuyerCustomerIdScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->contractsRelatedByBuyerCustomerIdScheduledForDeletion = null;
                }
            }

            if ($this->collContractsRelatedByBuyerCustomerId !== null) {
                foreach ($this->collContractsRelatedByBuyerCustomerId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->contractsRelatedBySellerCustomerIdScheduledForDeletion !== null) {
                if (!$this->contractsRelatedBySellerCustomerIdScheduledForDeletion->isEmpty()) {
                    \ContractQuery::create()
                        ->filterByPrimaryKeys($this->contractsRelatedBySellerCustomerIdScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->contractsRelatedBySellerCustomerIdScheduledForDeletion = null;
                }
            }

            if ($this->collContractsRelatedBySellerCustomerId !== null) {
                foreach ($this->collContractsRelatedBySellerCustomerId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[CustomerTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . CustomerTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(CustomerTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_FIRST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'first_name';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_LAST_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'last_name';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_COMPANY_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'company_name';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_PHONE)) {
            $modifiedColumns[':p' . $index++]  = 'phone';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_ACTIVE)) {
            $modifiedColumns[':p' . $index++]  = 'active';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_TYPE)) {
            $modifiedColumns[':p' . $index++]  = 'type';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'created_at';
        }
        if ($this->isColumnModified(CustomerTableMap::COL_UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'updated_at';
        }

        $sql = sprintf(
            'INSERT INTO customer (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'first_name':
                        $stmt->bindValue($identifier, $this->first_name, PDO::PARAM_STR);
                        break;
                    case 'last_name':
                        $stmt->bindValue($identifier, $this->last_name, PDO::PARAM_STR);
                        break;
                    case 'company_name':
                        $stmt->bindValue($identifier, $this->company_name, PDO::PARAM_STR);
                        break;
                    case 'phone':
                        $stmt->bindValue($identifier, $this->phone, PDO::PARAM_STR);
                        break;
                    case 'active':
                        $stmt->bindValue($identifier, (int) $this->active, PDO::PARAM_INT);
                        break;
                    case 'type':
                        $stmt->bindValue($identifier, $this->type, PDO::PARAM_STR);
                        break;
                    case 'created_at':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'updated_at':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CustomerTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getFirstName();
                break;
            case 2:
                return $this->getLastName();
                break;
            case 3:
                return $this->getCompanyName();
                break;
            case 4:
                return $this->getPhone();
                break;
            case 5:
                return $this->getActive();
                break;
            case 6:
                return $this->getType();
                break;
            case 7:
                return $this->getCreatedAt();
                break;
            case 8:
                return $this->getUpdatedAt();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['Customer'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Customer'][$this->hashCode()] = true;
        $keys = CustomerTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getFirstName(),
            $keys[2] => $this->getLastName(),
            $keys[3] => $this->getCompanyName(),
            $keys[4] => $this->getPhone(),
            $keys[5] => $this->getActive(),
            $keys[6] => $this->getType(),
            $keys[7] => $this->getCreatedAt(),
            $keys[8] => $this->getUpdatedAt(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collContractsRelatedByBuyerCustomerId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'contracts';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'contracts';
                        break;
                    default:
                        $key = 'Contracts';
                }

                $result[$key] = $this->collContractsRelatedByBuyerCustomerId->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collContractsRelatedBySellerCustomerId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'contracts';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'contracts';
                        break;
                    default:
                        $key = 'Contracts';
                }

                $result[$key] = $this->collContractsRelatedBySellerCustomerId->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\Customer
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = CustomerTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\Customer
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setFirstName($value);
                break;
            case 2:
                $this->setLastName($value);
                break;
            case 3:
                $this->setCompanyName($value);
                break;
            case 4:
                $this->setPhone($value);
                break;
            case 5:
                $this->setActive($value);
                break;
            case 6:
                $this->setType($value);
                break;
            case 7:
                $this->setCreatedAt($value);
                break;
            case 8:
                $this->setUpdatedAt($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = CustomerTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setFirstName($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setLastName($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setCompanyName($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setPhone($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setActive($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setType($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setCreatedAt($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setUpdatedAt($arr[$keys[8]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\Customer The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(CustomerTableMap::DATABASE_NAME);

        if ($this->isColumnModified(CustomerTableMap::COL_ID)) {
            $criteria->add(CustomerTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_FIRST_NAME)) {
            $criteria->add(CustomerTableMap::COL_FIRST_NAME, $this->first_name);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_LAST_NAME)) {
            $criteria->add(CustomerTableMap::COL_LAST_NAME, $this->last_name);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_COMPANY_NAME)) {
            $criteria->add(CustomerTableMap::COL_COMPANY_NAME, $this->company_name);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_PHONE)) {
            $criteria->add(CustomerTableMap::COL_PHONE, $this->phone);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_ACTIVE)) {
            $criteria->add(CustomerTableMap::COL_ACTIVE, $this->active);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_TYPE)) {
            $criteria->add(CustomerTableMap::COL_TYPE, $this->type);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_CREATED_AT)) {
            $criteria->add(CustomerTableMap::COL_CREATED_AT, $this->created_at);
        }
        if ($this->isColumnModified(CustomerTableMap::COL_UPDATED_AT)) {
            $criteria->add(CustomerTableMap::COL_UPDATED_AT, $this->updated_at);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildCustomerQuery::create();
        $criteria->add(CustomerTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \Customer (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setFirstName($this->getFirstName());
        $copyObj->setLastName($this->getLastName());
        $copyObj->setCompanyName($this->getCompanyName());
        $copyObj->setPhone($this->getPhone());
        $copyObj->setActive($this->getActive());
        $copyObj->setType($this->getType());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getContractsRelatedByBuyerCustomerId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addContractRelatedByBuyerCustomerId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getContractsRelatedBySellerCustomerId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addContractRelatedBySellerCustomerId($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \Customer Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('ContractRelatedByBuyerCustomerId' == $relationName) {
            return $this->initContractsRelatedByBuyerCustomerId();
        }
        if ('ContractRelatedBySellerCustomerId' == $relationName) {
            return $this->initContractsRelatedBySellerCustomerId();
        }
    }

    /**
     * Clears out the collContractsRelatedByBuyerCustomerId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addContractsRelatedByBuyerCustomerId()
     */
    public function clearContractsRelatedByBuyerCustomerId()
    {
        $this->collContractsRelatedByBuyerCustomerId = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collContractsRelatedByBuyerCustomerId collection loaded partially.
     */
    public function resetPartialContractsRelatedByBuyerCustomerId($v = true)
    {
        $this->collContractsRelatedByBuyerCustomerIdPartial = $v;
    }

    /**
     * Initializes the collContractsRelatedByBuyerCustomerId collection.
     *
     * By default this just sets the collContractsRelatedByBuyerCustomerId collection to an empty array (like clearcollContractsRelatedByBuyerCustomerId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initContractsRelatedByBuyerCustomerId($overrideExisting = true)
    {
        if (null !== $this->collContractsRelatedByBuyerCustomerId && !$overrideExisting) {
            return;
        }
        $this->collContractsRelatedByBuyerCustomerId = new ObjectCollection();
        $this->collContractsRelatedByBuyerCustomerId->setModel('\Contract');
    }

    /**
     * Gets an array of ChildContract objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCustomer is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     * @throws PropelException
     */
    public function getContractsRelatedByBuyerCustomerId(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collContractsRelatedByBuyerCustomerIdPartial && !$this->isNew();
        if (null === $this->collContractsRelatedByBuyerCustomerId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collContractsRelatedByBuyerCustomerId) {
                // return empty collection
                $this->initContractsRelatedByBuyerCustomerId();
            } else {
                $collContractsRelatedByBuyerCustomerId = ChildContractQuery::create(null, $criteria)
                    ->filterByCustomerRelatedByBuyerCustomerId($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collContractsRelatedByBuyerCustomerIdPartial && count($collContractsRelatedByBuyerCustomerId)) {
                        $this->initContractsRelatedByBuyerCustomerId(false);

                        foreach ($collContractsRelatedByBuyerCustomerId as $obj) {
                            if (false == $this->collContractsRelatedByBuyerCustomerId->contains($obj)) {
                                $this->collContractsRelatedByBuyerCustomerId->append($obj);
                            }
                        }

                        $this->collContractsRelatedByBuyerCustomerIdPartial = true;
                    }

                    return $collContractsRelatedByBuyerCustomerId;
                }

                if ($partial && $this->collContractsRelatedByBuyerCustomerId) {
                    foreach ($this->collContractsRelatedByBuyerCustomerId as $obj) {
                        if ($obj->isNew()) {
                            $collContractsRelatedByBuyerCustomerId[] = $obj;
                        }
                    }
                }

                $this->collContractsRelatedByBuyerCustomerId = $collContractsRelatedByBuyerCustomerId;
                $this->collContractsRelatedByBuyerCustomerIdPartial = false;
            }
        }

        return $this->collContractsRelatedByBuyerCustomerId;
    }

    /**
     * Sets a collection of ChildContract objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $contractsRelatedByBuyerCustomerId A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildCustomer The current object (for fluent API support)
     */
    public function setContractsRelatedByBuyerCustomerId(Collection $contractsRelatedByBuyerCustomerId, ConnectionInterface $con = null)
    {
        /** @var ChildContract[] $contractsRelatedByBuyerCustomerIdToDelete */
        $contractsRelatedByBuyerCustomerIdToDelete = $this->getContractsRelatedByBuyerCustomerId(new Criteria(), $con)->diff($contractsRelatedByBuyerCustomerId);


        $this->contractsRelatedByBuyerCustomerIdScheduledForDeletion = $contractsRelatedByBuyerCustomerIdToDelete;

        foreach ($contractsRelatedByBuyerCustomerIdToDelete as $contractRelatedByBuyerCustomerIdRemoved) {
            $contractRelatedByBuyerCustomerIdRemoved->setCustomerRelatedByBuyerCustomerId(null);
        }

        $this->collContractsRelatedByBuyerCustomerId = null;
        foreach ($contractsRelatedByBuyerCustomerId as $contractRelatedByBuyerCustomerId) {
            $this->addContractRelatedByBuyerCustomerId($contractRelatedByBuyerCustomerId);
        }

        $this->collContractsRelatedByBuyerCustomerId = $contractsRelatedByBuyerCustomerId;
        $this->collContractsRelatedByBuyerCustomerIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Contract objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Contract objects.
     * @throws PropelException
     */
    public function countContractsRelatedByBuyerCustomerId(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collContractsRelatedByBuyerCustomerIdPartial && !$this->isNew();
        if (null === $this->collContractsRelatedByBuyerCustomerId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collContractsRelatedByBuyerCustomerId) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getContractsRelatedByBuyerCustomerId());
            }

            $query = ChildContractQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCustomerRelatedByBuyerCustomerId($this)
                ->count($con);
        }

        return count($this->collContractsRelatedByBuyerCustomerId);
    }

    /**
     * Method called to associate a ChildContract object to this object
     * through the ChildContract foreign key attribute.
     *
     * @param  ChildContract $l ChildContract
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function addContractRelatedByBuyerCustomerId(ChildContract $l)
    {
        if ($this->collContractsRelatedByBuyerCustomerId === null) {
            $this->initContractsRelatedByBuyerCustomerId();
            $this->collContractsRelatedByBuyerCustomerIdPartial = true;
        }

        if (!$this->collContractsRelatedByBuyerCustomerId->contains($l)) {
            $this->doAddContractRelatedByBuyerCustomerId($l);
        }

        return $this;
    }

    /**
     * @param ChildContract $contractRelatedByBuyerCustomerId The ChildContract object to add.
     */
    protected function doAddContractRelatedByBuyerCustomerId(ChildContract $contractRelatedByBuyerCustomerId)
    {
        $this->collContractsRelatedByBuyerCustomerId[]= $contractRelatedByBuyerCustomerId;
        $contractRelatedByBuyerCustomerId->setCustomerRelatedByBuyerCustomerId($this);
    }

    /**
     * @param  ChildContract $contractRelatedByBuyerCustomerId The ChildContract object to remove.
     * @return $this|ChildCustomer The current object (for fluent API support)
     */
    public function removeContractRelatedByBuyerCustomerId(ChildContract $contractRelatedByBuyerCustomerId)
    {
        if ($this->getContractsRelatedByBuyerCustomerId()->contains($contractRelatedByBuyerCustomerId)) {
            $pos = $this->collContractsRelatedByBuyerCustomerId->search($contractRelatedByBuyerCustomerId);
            $this->collContractsRelatedByBuyerCustomerId->remove($pos);
            if (null === $this->contractsRelatedByBuyerCustomerIdScheduledForDeletion) {
                $this->contractsRelatedByBuyerCustomerIdScheduledForDeletion = clone $this->collContractsRelatedByBuyerCustomerId;
                $this->contractsRelatedByBuyerCustomerIdScheduledForDeletion->clear();
            }
            $this->contractsRelatedByBuyerCustomerIdScheduledForDeletion[]= clone $contractRelatedByBuyerCustomerId;
            $contractRelatedByBuyerCustomerId->setCustomerRelatedByBuyerCustomerId(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Customer is new, it will return
     * an empty collection; or if this Customer has previously
     * been saved, it will retrieve related ContractsRelatedByBuyerCustomerId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Customer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     */
    public function getContractsRelatedByBuyerCustomerIdJoinLocation(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildContractQuery::create(null, $criteria);
        $query->joinWith('Location', $joinBehavior);

        return $this->getContractsRelatedByBuyerCustomerId($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Customer is new, it will return
     * an empty collection; or if this Customer has previously
     * been saved, it will retrieve related ContractsRelatedByBuyerCustomerId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Customer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     */
    public function getContractsRelatedByBuyerCustomerIdJoinAgentRelatedByBuyerAgentId(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildContractQuery::create(null, $criteria);
        $query->joinWith('AgentRelatedByBuyerAgentId', $joinBehavior);

        return $this->getContractsRelatedByBuyerCustomerId($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Customer is new, it will return
     * an empty collection; or if this Customer has previously
     * been saved, it will retrieve related ContractsRelatedByBuyerCustomerId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Customer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     */
    public function getContractsRelatedByBuyerCustomerIdJoinAgentRelatedBySellerAgentId(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildContractQuery::create(null, $criteria);
        $query->joinWith('AgentRelatedBySellerAgentId', $joinBehavior);

        return $this->getContractsRelatedByBuyerCustomerId($query, $con);
    }

    /**
     * Clears out the collContractsRelatedBySellerCustomerId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addContractsRelatedBySellerCustomerId()
     */
    public function clearContractsRelatedBySellerCustomerId()
    {
        $this->collContractsRelatedBySellerCustomerId = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collContractsRelatedBySellerCustomerId collection loaded partially.
     */
    public function resetPartialContractsRelatedBySellerCustomerId($v = true)
    {
        $this->collContractsRelatedBySellerCustomerIdPartial = $v;
    }

    /**
     * Initializes the collContractsRelatedBySellerCustomerId collection.
     *
     * By default this just sets the collContractsRelatedBySellerCustomerId collection to an empty array (like clearcollContractsRelatedBySellerCustomerId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initContractsRelatedBySellerCustomerId($overrideExisting = true)
    {
        if (null !== $this->collContractsRelatedBySellerCustomerId && !$overrideExisting) {
            return;
        }
        $this->collContractsRelatedBySellerCustomerId = new ObjectCollection();
        $this->collContractsRelatedBySellerCustomerId->setModel('\Contract');
    }

    /**
     * Gets an array of ChildContract objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildCustomer is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     * @throws PropelException
     */
    public function getContractsRelatedBySellerCustomerId(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collContractsRelatedBySellerCustomerIdPartial && !$this->isNew();
        if (null === $this->collContractsRelatedBySellerCustomerId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collContractsRelatedBySellerCustomerId) {
                // return empty collection
                $this->initContractsRelatedBySellerCustomerId();
            } else {
                $collContractsRelatedBySellerCustomerId = ChildContractQuery::create(null, $criteria)
                    ->filterByCustomerRelatedBySellerCustomerId($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collContractsRelatedBySellerCustomerIdPartial && count($collContractsRelatedBySellerCustomerId)) {
                        $this->initContractsRelatedBySellerCustomerId(false);

                        foreach ($collContractsRelatedBySellerCustomerId as $obj) {
                            if (false == $this->collContractsRelatedBySellerCustomerId->contains($obj)) {
                                $this->collContractsRelatedBySellerCustomerId->append($obj);
                            }
                        }

                        $this->collContractsRelatedBySellerCustomerIdPartial = true;
                    }

                    return $collContractsRelatedBySellerCustomerId;
                }

                if ($partial && $this->collContractsRelatedBySellerCustomerId) {
                    foreach ($this->collContractsRelatedBySellerCustomerId as $obj) {
                        if ($obj->isNew()) {
                            $collContractsRelatedBySellerCustomerId[] = $obj;
                        }
                    }
                }

                $this->collContractsRelatedBySellerCustomerId = $collContractsRelatedBySellerCustomerId;
                $this->collContractsRelatedBySellerCustomerIdPartial = false;
            }
        }

        return $this->collContractsRelatedBySellerCustomerId;
    }

    /**
     * Sets a collection of ChildContract objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $contractsRelatedBySellerCustomerId A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildCustomer The current object (for fluent API support)
     */
    public function setContractsRelatedBySellerCustomerId(Collection $contractsRelatedBySellerCustomerId, ConnectionInterface $con = null)
    {
        /** @var ChildContract[] $contractsRelatedBySellerCustomerIdToDelete */
        $contractsRelatedBySellerCustomerIdToDelete = $this->getContractsRelatedBySellerCustomerId(new Criteria(), $con)->diff($contractsRelatedBySellerCustomerId);


        $this->contractsRelatedBySellerCustomerIdScheduledForDeletion = $contractsRelatedBySellerCustomerIdToDelete;

        foreach ($contractsRelatedBySellerCustomerIdToDelete as $contractRelatedBySellerCustomerIdRemoved) {
            $contractRelatedBySellerCustomerIdRemoved->setCustomerRelatedBySellerCustomerId(null);
        }

        $this->collContractsRelatedBySellerCustomerId = null;
        foreach ($contractsRelatedBySellerCustomerId as $contractRelatedBySellerCustomerId) {
            $this->addContractRelatedBySellerCustomerId($contractRelatedBySellerCustomerId);
        }

        $this->collContractsRelatedBySellerCustomerId = $contractsRelatedBySellerCustomerId;
        $this->collContractsRelatedBySellerCustomerIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Contract objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Contract objects.
     * @throws PropelException
     */
    public function countContractsRelatedBySellerCustomerId(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collContractsRelatedBySellerCustomerIdPartial && !$this->isNew();
        if (null === $this->collContractsRelatedBySellerCustomerId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collContractsRelatedBySellerCustomerId) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getContractsRelatedBySellerCustomerId());
            }

            $query = ChildContractQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByCustomerRelatedBySellerCustomerId($this)
                ->count($con);
        }

        return count($this->collContractsRelatedBySellerCustomerId);
    }

    /**
     * Method called to associate a ChildContract object to this object
     * through the ChildContract foreign key attribute.
     *
     * @param  ChildContract $l ChildContract
     * @return $this|\Customer The current object (for fluent API support)
     */
    public function addContractRelatedBySellerCustomerId(ChildContract $l)
    {
        if ($this->collContractsRelatedBySellerCustomerId === null) {
            $this->initContractsRelatedBySellerCustomerId();
            $this->collContractsRelatedBySellerCustomerIdPartial = true;
        }

        if (!$this->collContractsRelatedBySellerCustomerId->contains($l)) {
            $this->doAddContractRelatedBySellerCustomerId($l);
        }

        return $this;
    }

    /**
     * @param ChildContract $contractRelatedBySellerCustomerId The ChildContract object to add.
     */
    protected function doAddContractRelatedBySellerCustomerId(ChildContract $contractRelatedBySellerCustomerId)
    {
        $this->collContractsRelatedBySellerCustomerId[]= $contractRelatedBySellerCustomerId;
        $contractRelatedBySellerCustomerId->setCustomerRelatedBySellerCustomerId($this);
    }

    /**
     * @param  ChildContract $contractRelatedBySellerCustomerId The ChildContract object to remove.
     * @return $this|ChildCustomer The current object (for fluent API support)
     */
    public function removeContractRelatedBySellerCustomerId(ChildContract $contractRelatedBySellerCustomerId)
    {
        if ($this->getContractsRelatedBySellerCustomerId()->contains($contractRelatedBySellerCustomerId)) {
            $pos = $this->collContractsRelatedBySellerCustomerId->search($contractRelatedBySellerCustomerId);
            $this->collContractsRelatedBySellerCustomerId->remove($pos);
            if (null === $this->contractsRelatedBySellerCustomerIdScheduledForDeletion) {
                $this->contractsRelatedBySellerCustomerIdScheduledForDeletion = clone $this->collContractsRelatedBySellerCustomerId;
                $this->contractsRelatedBySellerCustomerIdScheduledForDeletion->clear();
            }
            $this->contractsRelatedBySellerCustomerIdScheduledForDeletion[]= clone $contractRelatedBySellerCustomerId;
            $contractRelatedBySellerCustomerId->setCustomerRelatedBySellerCustomerId(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Customer is new, it will return
     * an empty collection; or if this Customer has previously
     * been saved, it will retrieve related ContractsRelatedBySellerCustomerId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Customer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     */
    public function getContractsRelatedBySellerCustomerIdJoinLocation(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildContractQuery::create(null, $criteria);
        $query->joinWith('Location', $joinBehavior);

        return $this->getContractsRelatedBySellerCustomerId($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Customer is new, it will return
     * an empty collection; or if this Customer has previously
     * been saved, it will retrieve related ContractsRelatedBySellerCustomerId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Customer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     */
    public function getContractsRelatedBySellerCustomerIdJoinAgentRelatedByBuyerAgentId(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildContractQuery::create(null, $criteria);
        $query->joinWith('AgentRelatedByBuyerAgentId', $joinBehavior);

        return $this->getContractsRelatedBySellerCustomerId($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Customer is new, it will return
     * an empty collection; or if this Customer has previously
     * been saved, it will retrieve related ContractsRelatedBySellerCustomerId from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Customer.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildContract[] List of ChildContract objects
     */
    public function getContractsRelatedBySellerCustomerIdJoinAgentRelatedBySellerAgentId(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildContractQuery::create(null, $criteria);
        $query->joinWith('AgentRelatedBySellerAgentId', $joinBehavior);

        return $this->getContractsRelatedBySellerCustomerId($query, $con);
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->id = null;
        $this->first_name = null;
        $this->last_name = null;
        $this->company_name = null;
        $this->phone = null;
        $this->active = null;
        $this->type = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collContractsRelatedByBuyerCustomerId) {
                foreach ($this->collContractsRelatedByBuyerCustomerId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collContractsRelatedBySellerCustomerId) {
                foreach ($this->collContractsRelatedBySellerCustomerId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collContractsRelatedByBuyerCustomerId = null;
        $this->collContractsRelatedBySellerCustomerId = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(CustomerTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
