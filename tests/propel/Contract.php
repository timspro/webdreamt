<?php

namespace Base;

use \Agent as ChildAgent;
use \AgentQuery as ChildAgentQuery;
use \Contract as ChildContract;
use \ContractQuery as ChildContractQuery;
use \Customer as ChildCustomer;
use \CustomerQuery as ChildCustomerQuery;
use \Location as ChildLocation;
use \LocationQuery as ChildLocationQuery;
use \Move as ChildMove;
use \MoveQuery as ChildMoveQuery;
use \Service as ChildService;
use \ServiceContract as ChildServiceContract;
use \ServiceContractQuery as ChildServiceContractQuery;
use \ServiceQuery as ChildServiceQuery;
use \DateTime;
use \Exception;
use \PDO;
use Map\ContractTableMap;
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
 * Base class that represents a row from the 'contract' table.
 *
 *
 *
* @package    propel.generator..Base
*/
abstract class Contract implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Map\\ContractTableMap';


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
     * The value for the location_id field.
     * @var        int
     */
    protected $location_id;

    /**
     * The value for the buyer_customer_id field.
     * @var        int
     */
    protected $buyer_customer_id;

    /**
     * The value for the seller_customer_id field.
     * @var        int
     */
    protected $seller_customer_id;

    /**
     * The value for the buyer_agent_id field.
     * @var        int
     */
    protected $buyer_agent_id;

    /**
     * The value for the seller_agent_id field.
     * @var        int
     */
    protected $seller_agent_id;

    /**
     * The value for the completed_time field.
     * @var        \DateTime
     */
    protected $completed_time;

    /**
     * The value for the completed_date field.
     * @var        \DateTime
     */
    protected $completed_date;

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
     * @var        ChildLocation
     */
    protected $aLocation;

    /**
     * @var        ChildCustomer
     */
    protected $aCustomerRelatedByBuyerCustomerId;

    /**
     * @var        ChildCustomer
     */
    protected $aCustomerRelatedBySellerCustomerId;

    /**
     * @var        ChildAgent
     */
    protected $aAgentRelatedByBuyerAgentId;

    /**
     * @var        ChildAgent
     */
    protected $aAgentRelatedBySellerAgentId;

    /**
     * @var        ObjectCollection|ChildMove[] Collection to store aggregation of ChildMove objects.
     */
    protected $collMovesRelatedByBuyerContractId;
    protected $collMovesRelatedByBuyerContractIdPartial;

    /**
     * @var        ObjectCollection|ChildMove[] Collection to store aggregation of ChildMove objects.
     */
    protected $collMovesRelatedBySellerContractId;
    protected $collMovesRelatedBySellerContractIdPartial;

    /**
     * @var        ObjectCollection|ChildServiceContract[] Collection to store aggregation of ChildServiceContract objects.
     */
    protected $collServiceContracts;
    protected $collServiceContractsPartial;

    /**
     * @var        ObjectCollection|ChildService[] Cross Collection to store aggregation of ChildService objects.
     */
    protected $collServices;

    /**
     * @var bool
     */
    protected $collServicesPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildService[]
     */
    protected $servicesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildMove[]
     */
    protected $movesRelatedByBuyerContractIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildMove[]
     */
    protected $movesRelatedBySellerContractIdScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildServiceContract[]
     */
    protected $serviceContractsScheduledForDeletion = null;

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
     * Initializes internal state of Base\Contract object.
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
     * Compares this with another <code>Contract</code> instance.  If
     * <code>obj</code> is an instance of <code>Contract</code>, delegates to
     * <code>equals(Contract)</code>.  Otherwise, returns <code>false</code>.
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
     * @return $this|Contract The current object, for fluid interface
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
     * Get the [location_id] column value.
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Get the [buyer_customer_id] column value.
     *
     * @return int
     */
    public function getBuyerCustomerId()
    {
        return $this->buyer_customer_id;
    }

    /**
     * Get the [seller_customer_id] column value.
     *
     * @return int
     */
    public function getSellerCustomerId()
    {
        return $this->seller_customer_id;
    }

    /**
     * Get the [buyer_agent_id] column value.
     *
     * @return int
     */
    public function getBuyerAgentId()
    {
        return $this->buyer_agent_id;
    }

    /**
     * Get the [seller_agent_id] column value.
     *
     * @return int
     */
    public function getSellerAgentId()
    {
        return $this->seller_agent_id;
    }

    /**
     * Get the [optionally formatted] temporal [completed_time] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCompletedTime($format = NULL)
    {
        if ($format === null) {
            return $this->completed_time;
        } else {
            return $this->completed_time instanceof \DateTime ? $this->completed_time->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [completed_date] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw DateTime object will be returned.
     *
     * @return string|DateTime Formatted date/time value as string or DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCompletedDate($format = NULL)
    {
        if ($format === null) {
            return $this->completed_date;
        } else {
            return $this->completed_date instanceof \DateTime ? $this->completed_date->format($format) : null;
        }
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
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ContractTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [location_id] column.
     *
     * @param  int $v new value
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setLocationId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->location_id !== $v) {
            $this->location_id = $v;
            $this->modifiedColumns[ContractTableMap::COL_LOCATION_ID] = true;
        }

        if ($this->aLocation !== null && $this->aLocation->getId() !== $v) {
            $this->aLocation = null;
        }

        return $this;
    } // setLocationId()

    /**
     * Set the value of [buyer_customer_id] column.
     *
     * @param  int $v new value
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setBuyerCustomerId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->buyer_customer_id !== $v) {
            $this->buyer_customer_id = $v;
            $this->modifiedColumns[ContractTableMap::COL_BUYER_CUSTOMER_ID] = true;
        }

        if ($this->aCustomerRelatedByBuyerCustomerId !== null && $this->aCustomerRelatedByBuyerCustomerId->getId() !== $v) {
            $this->aCustomerRelatedByBuyerCustomerId = null;
        }

        return $this;
    } // setBuyerCustomerId()

    /**
     * Set the value of [seller_customer_id] column.
     *
     * @param  int $v new value
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setSellerCustomerId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->seller_customer_id !== $v) {
            $this->seller_customer_id = $v;
            $this->modifiedColumns[ContractTableMap::COL_SELLER_CUSTOMER_ID] = true;
        }

        if ($this->aCustomerRelatedBySellerCustomerId !== null && $this->aCustomerRelatedBySellerCustomerId->getId() !== $v) {
            $this->aCustomerRelatedBySellerCustomerId = null;
        }

        return $this;
    } // setSellerCustomerId()

    /**
     * Set the value of [buyer_agent_id] column.
     *
     * @param  int $v new value
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setBuyerAgentId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->buyer_agent_id !== $v) {
            $this->buyer_agent_id = $v;
            $this->modifiedColumns[ContractTableMap::COL_BUYER_AGENT_ID] = true;
        }

        if ($this->aAgentRelatedByBuyerAgentId !== null && $this->aAgentRelatedByBuyerAgentId->getId() !== $v) {
            $this->aAgentRelatedByBuyerAgentId = null;
        }

        return $this;
    } // setBuyerAgentId()

    /**
     * Set the value of [seller_agent_id] column.
     *
     * @param  int $v new value
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setSellerAgentId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->seller_agent_id !== $v) {
            $this->seller_agent_id = $v;
            $this->modifiedColumns[ContractTableMap::COL_SELLER_AGENT_ID] = true;
        }

        if ($this->aAgentRelatedBySellerAgentId !== null && $this->aAgentRelatedBySellerAgentId->getId() !== $v) {
            $this->aAgentRelatedBySellerAgentId = null;
        }

        return $this;
    } // setSellerAgentId()

    /**
     * Sets the value of [completed_time] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setCompletedTime($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->completed_time !== null || $dt !== null) {
            if ($dt !== $this->completed_time) {
                $this->completed_time = $dt;
                $this->modifiedColumns[ContractTableMap::COL_COMPLETED_TIME] = true;
            }
        } // if either are not null

        return $this;
    } // setCompletedTime()

    /**
     * Sets the value of [completed_date] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setCompletedDate($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->completed_date !== null || $dt !== null) {
            if ($dt !== $this->completed_date) {
                $this->completed_date = $dt;
                $this->modifiedColumns[ContractTableMap::COL_COMPLETED_DATE] = true;
            }
        } // if either are not null

        return $this;
    } // setCompletedDate()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ( ($dt != $this->created_at) // normalized values don't match
                || ($dt->format('Y-m-d H:i:s') === NULL) // or the entered value matches the default
                 ) {
                $this->created_at = $dt;
                $this->modifiedColumns[ContractTableMap::COL_CREATED_AT] = true;
            }
        } // if either are not null

        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param  mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ( ($dt != $this->updated_at) // normalized values don't match
                || ($dt->format('Y-m-d H:i:s') === NULL) // or the entered value matches the default
                 ) {
                $this->updated_at = $dt;
                $this->modifiedColumns[ContractTableMap::COL_UPDATED_AT] = true;
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ContractTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ContractTableMap::translateFieldName('LocationId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->location_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ContractTableMap::translateFieldName('BuyerCustomerId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->buyer_customer_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ContractTableMap::translateFieldName('SellerCustomerId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->seller_customer_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ContractTableMap::translateFieldName('BuyerAgentId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->buyer_agent_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ContractTableMap::translateFieldName('SellerAgentId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->seller_agent_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : ContractTableMap::translateFieldName('CompletedTime', TableMap::TYPE_PHPNAME, $indexType)];
            $this->completed_time = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 7 + $startcol : ContractTableMap::translateFieldName('CompletedDate', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00') {
                $col = null;
            }
            $this->completed_date = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 8 + $startcol : ContractTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 9 + $startcol : ContractTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, 'DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 10; // 10 = ContractTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\Contract'), 0, $e);
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
        if ($this->aLocation !== null && $this->location_id !== $this->aLocation->getId()) {
            $this->aLocation = null;
        }
        if ($this->aCustomerRelatedByBuyerCustomerId !== null && $this->buyer_customer_id !== $this->aCustomerRelatedByBuyerCustomerId->getId()) {
            $this->aCustomerRelatedByBuyerCustomerId = null;
        }
        if ($this->aCustomerRelatedBySellerCustomerId !== null && $this->seller_customer_id !== $this->aCustomerRelatedBySellerCustomerId->getId()) {
            $this->aCustomerRelatedBySellerCustomerId = null;
        }
        if ($this->aAgentRelatedByBuyerAgentId !== null && $this->buyer_agent_id !== $this->aAgentRelatedByBuyerAgentId->getId()) {
            $this->aAgentRelatedByBuyerAgentId = null;
        }
        if ($this->aAgentRelatedBySellerAgentId !== null && $this->seller_agent_id !== $this->aAgentRelatedBySellerAgentId->getId()) {
            $this->aAgentRelatedBySellerAgentId = null;
        }
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
            $con = Propel::getServiceContainer()->getReadConnection(ContractTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildContractQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aLocation = null;
            $this->aCustomerRelatedByBuyerCustomerId = null;
            $this->aCustomerRelatedBySellerCustomerId = null;
            $this->aAgentRelatedByBuyerAgentId = null;
            $this->aAgentRelatedBySellerAgentId = null;
            $this->collMovesRelatedByBuyerContractId = null;

            $this->collMovesRelatedBySellerContractId = null;

            $this->collServiceContracts = null;

            $this->collServices = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Contract::setDeleted()
     * @see Contract::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ContractTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildContractQuery::create()
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
            $con = Propel::getServiceContainer()->getWriteConnection(ContractTableMap::DATABASE_NAME);
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
                ContractTableMap::addInstanceToPool($this);
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

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aLocation !== null) {
                if ($this->aLocation->isModified() || $this->aLocation->isNew()) {
                    $affectedRows += $this->aLocation->save($con);
                }
                $this->setLocation($this->aLocation);
            }

            if ($this->aCustomerRelatedByBuyerCustomerId !== null) {
                if ($this->aCustomerRelatedByBuyerCustomerId->isModified() || $this->aCustomerRelatedByBuyerCustomerId->isNew()) {
                    $affectedRows += $this->aCustomerRelatedByBuyerCustomerId->save($con);
                }
                $this->setCustomerRelatedByBuyerCustomerId($this->aCustomerRelatedByBuyerCustomerId);
            }

            if ($this->aCustomerRelatedBySellerCustomerId !== null) {
                if ($this->aCustomerRelatedBySellerCustomerId->isModified() || $this->aCustomerRelatedBySellerCustomerId->isNew()) {
                    $affectedRows += $this->aCustomerRelatedBySellerCustomerId->save($con);
                }
                $this->setCustomerRelatedBySellerCustomerId($this->aCustomerRelatedBySellerCustomerId);
            }

            if ($this->aAgentRelatedByBuyerAgentId !== null) {
                if ($this->aAgentRelatedByBuyerAgentId->isModified() || $this->aAgentRelatedByBuyerAgentId->isNew()) {
                    $affectedRows += $this->aAgentRelatedByBuyerAgentId->save($con);
                }
                $this->setAgentRelatedByBuyerAgentId($this->aAgentRelatedByBuyerAgentId);
            }

            if ($this->aAgentRelatedBySellerAgentId !== null) {
                if ($this->aAgentRelatedBySellerAgentId->isModified() || $this->aAgentRelatedBySellerAgentId->isNew()) {
                    $affectedRows += $this->aAgentRelatedBySellerAgentId->save($con);
                }
                $this->setAgentRelatedBySellerAgentId($this->aAgentRelatedBySellerAgentId);
            }

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

            if ($this->servicesScheduledForDeletion !== null) {
                if (!$this->servicesScheduledForDeletion->isEmpty()) {
                    $pks = array();
                    foreach ($this->servicesScheduledForDeletion as $entry) {
                        $entryPk = [];

                        $entryPk[1] = $this->getId();
                        $entryPk[0] = $entry->getId();
                        $pks[] = $entryPk;
                    }

                    \ServiceContractQuery::create()
                        ->filterByPrimaryKeys($pks)
                        ->delete($con);

                    $this->servicesScheduledForDeletion = null;
                }

            }

            if ($this->collServices) {
                foreach ($this->collServices as $service) {
                    if (!$service->isDeleted() && ($service->isNew() || $service->isModified())) {
                        $service->save($con);
                    }
                }
            }


            if ($this->movesRelatedByBuyerContractIdScheduledForDeletion !== null) {
                if (!$this->movesRelatedByBuyerContractIdScheduledForDeletion->isEmpty()) {
                    \MoveQuery::create()
                        ->filterByPrimaryKeys($this->movesRelatedByBuyerContractIdScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->movesRelatedByBuyerContractIdScheduledForDeletion = null;
                }
            }

            if ($this->collMovesRelatedByBuyerContractId !== null) {
                foreach ($this->collMovesRelatedByBuyerContractId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->movesRelatedBySellerContractIdScheduledForDeletion !== null) {
                if (!$this->movesRelatedBySellerContractIdScheduledForDeletion->isEmpty()) {
                    \MoveQuery::create()
                        ->filterByPrimaryKeys($this->movesRelatedBySellerContractIdScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->movesRelatedBySellerContractIdScheduledForDeletion = null;
                }
            }

            if ($this->collMovesRelatedBySellerContractId !== null) {
                foreach ($this->collMovesRelatedBySellerContractId as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->serviceContractsScheduledForDeletion !== null) {
                if (!$this->serviceContractsScheduledForDeletion->isEmpty()) {
                    \ServiceContractQuery::create()
                        ->filterByPrimaryKeys($this->serviceContractsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->serviceContractsScheduledForDeletion = null;
                }
            }

            if ($this->collServiceContracts !== null) {
                foreach ($this->collServiceContracts as $referrerFK) {
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

        $this->modifiedColumns[ContractTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ContractTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ContractTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(ContractTableMap::COL_LOCATION_ID)) {
            $modifiedColumns[':p' . $index++]  = 'location_id';
        }
        if ($this->isColumnModified(ContractTableMap::COL_BUYER_CUSTOMER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'buyer_customer_id';
        }
        if ($this->isColumnModified(ContractTableMap::COL_SELLER_CUSTOMER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'seller_customer_id';
        }
        if ($this->isColumnModified(ContractTableMap::COL_BUYER_AGENT_ID)) {
            $modifiedColumns[':p' . $index++]  = 'buyer_agent_id';
        }
        if ($this->isColumnModified(ContractTableMap::COL_SELLER_AGENT_ID)) {
            $modifiedColumns[':p' . $index++]  = 'seller_agent_id';
        }
        if ($this->isColumnModified(ContractTableMap::COL_COMPLETED_TIME)) {
            $modifiedColumns[':p' . $index++]  = 'completed_time';
        }
        if ($this->isColumnModified(ContractTableMap::COL_COMPLETED_DATE)) {
            $modifiedColumns[':p' . $index++]  = 'completed_date';
        }
        if ($this->isColumnModified(ContractTableMap::COL_CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'created_at';
        }
        if ($this->isColumnModified(ContractTableMap::COL_UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'updated_at';
        }

        $sql = sprintf(
            'INSERT INTO contract (%s) VALUES (%s)',
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
                    case 'location_id':
                        $stmt->bindValue($identifier, $this->location_id, PDO::PARAM_INT);
                        break;
                    case 'buyer_customer_id':
                        $stmt->bindValue($identifier, $this->buyer_customer_id, PDO::PARAM_INT);
                        break;
                    case 'seller_customer_id':
                        $stmt->bindValue($identifier, $this->seller_customer_id, PDO::PARAM_INT);
                        break;
                    case 'buyer_agent_id':
                        $stmt->bindValue($identifier, $this->buyer_agent_id, PDO::PARAM_INT);
                        break;
                    case 'seller_agent_id':
                        $stmt->bindValue($identifier, $this->seller_agent_id, PDO::PARAM_INT);
                        break;
                    case 'completed_time':
                        $stmt->bindValue($identifier, $this->completed_time ? $this->completed_time->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'completed_date':
                        $stmt->bindValue($identifier, $this->completed_date ? $this->completed_date->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
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
        $pos = ContractTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getLocationId();
                break;
            case 2:
                return $this->getBuyerCustomerId();
                break;
            case 3:
                return $this->getSellerCustomerId();
                break;
            case 4:
                return $this->getBuyerAgentId();
                break;
            case 5:
                return $this->getSellerAgentId();
                break;
            case 6:
                return $this->getCompletedTime();
                break;
            case 7:
                return $this->getCompletedDate();
                break;
            case 8:
                return $this->getCreatedAt();
                break;
            case 9:
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

        if (isset($alreadyDumpedObjects['Contract'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Contract'][$this->hashCode()] = true;
        $keys = ContractTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getLocationId(),
            $keys[2] => $this->getBuyerCustomerId(),
            $keys[3] => $this->getSellerCustomerId(),
            $keys[4] => $this->getBuyerAgentId(),
            $keys[5] => $this->getSellerAgentId(),
            $keys[6] => $this->getCompletedTime(),
            $keys[7] => $this->getCompletedDate(),
            $keys[8] => $this->getCreatedAt(),
            $keys[9] => $this->getUpdatedAt(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aLocation) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'location';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'location';
                        break;
                    default:
                        $key = 'Location';
                }

                $result[$key] = $this->aLocation->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aCustomerRelatedByBuyerCustomerId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'customer';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'customer';
                        break;
                    default:
                        $key = 'Customer';
                }

                $result[$key] = $this->aCustomerRelatedByBuyerCustomerId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aCustomerRelatedBySellerCustomerId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'customer';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'customer';
                        break;
                    default:
                        $key = 'Customer';
                }

                $result[$key] = $this->aCustomerRelatedBySellerCustomerId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAgentRelatedByBuyerAgentId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'agent';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'agent';
                        break;
                    default:
                        $key = 'Agent';
                }

                $result[$key] = $this->aAgentRelatedByBuyerAgentId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAgentRelatedBySellerAgentId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'agent';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'agent';
                        break;
                    default:
                        $key = 'Agent';
                }

                $result[$key] = $this->aAgentRelatedBySellerAgentId->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collMovesRelatedByBuyerContractId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'moves';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'moves';
                        break;
                    default:
                        $key = 'Moves';
                }

                $result[$key] = $this->collMovesRelatedByBuyerContractId->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMovesRelatedBySellerContractId) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'moves';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'moves';
                        break;
                    default:
                        $key = 'Moves';
                }

                $result[$key] = $this->collMovesRelatedBySellerContractId->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collServiceContracts) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'serviceContracts';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'service_contracts';
                        break;
                    default:
                        $key = 'ServiceContracts';
                }

                $result[$key] = $this->collServiceContracts->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
     * @return $this|\Contract
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ContractTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\Contract
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setLocationId($value);
                break;
            case 2:
                $this->setBuyerCustomerId($value);
                break;
            case 3:
                $this->setSellerCustomerId($value);
                break;
            case 4:
                $this->setBuyerAgentId($value);
                break;
            case 5:
                $this->setSellerAgentId($value);
                break;
            case 6:
                $this->setCompletedTime($value);
                break;
            case 7:
                $this->setCompletedDate($value);
                break;
            case 8:
                $this->setCreatedAt($value);
                break;
            case 9:
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
        $keys = ContractTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setLocationId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setBuyerCustomerId($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setSellerCustomerId($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setBuyerAgentId($arr[$keys[4]]);
        }
        if (array_key_exists($keys[5], $arr)) {
            $this->setSellerAgentId($arr[$keys[5]]);
        }
        if (array_key_exists($keys[6], $arr)) {
            $this->setCompletedTime($arr[$keys[6]]);
        }
        if (array_key_exists($keys[7], $arr)) {
            $this->setCompletedDate($arr[$keys[7]]);
        }
        if (array_key_exists($keys[8], $arr)) {
            $this->setCreatedAt($arr[$keys[8]]);
        }
        if (array_key_exists($keys[9], $arr)) {
            $this->setUpdatedAt($arr[$keys[9]]);
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
     * @return $this|\Contract The current object, for fluid interface
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
        $criteria = new Criteria(ContractTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ContractTableMap::COL_ID)) {
            $criteria->add(ContractTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(ContractTableMap::COL_LOCATION_ID)) {
            $criteria->add(ContractTableMap::COL_LOCATION_ID, $this->location_id);
        }
        if ($this->isColumnModified(ContractTableMap::COL_BUYER_CUSTOMER_ID)) {
            $criteria->add(ContractTableMap::COL_BUYER_CUSTOMER_ID, $this->buyer_customer_id);
        }
        if ($this->isColumnModified(ContractTableMap::COL_SELLER_CUSTOMER_ID)) {
            $criteria->add(ContractTableMap::COL_SELLER_CUSTOMER_ID, $this->seller_customer_id);
        }
        if ($this->isColumnModified(ContractTableMap::COL_BUYER_AGENT_ID)) {
            $criteria->add(ContractTableMap::COL_BUYER_AGENT_ID, $this->buyer_agent_id);
        }
        if ($this->isColumnModified(ContractTableMap::COL_SELLER_AGENT_ID)) {
            $criteria->add(ContractTableMap::COL_SELLER_AGENT_ID, $this->seller_agent_id);
        }
        if ($this->isColumnModified(ContractTableMap::COL_COMPLETED_TIME)) {
            $criteria->add(ContractTableMap::COL_COMPLETED_TIME, $this->completed_time);
        }
        if ($this->isColumnModified(ContractTableMap::COL_COMPLETED_DATE)) {
            $criteria->add(ContractTableMap::COL_COMPLETED_DATE, $this->completed_date);
        }
        if ($this->isColumnModified(ContractTableMap::COL_CREATED_AT)) {
            $criteria->add(ContractTableMap::COL_CREATED_AT, $this->created_at);
        }
        if ($this->isColumnModified(ContractTableMap::COL_UPDATED_AT)) {
            $criteria->add(ContractTableMap::COL_UPDATED_AT, $this->updated_at);
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
        $criteria = ChildContractQuery::create();
        $criteria->add(ContractTableMap::COL_ID, $this->id);

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
     * @param      object $copyObj An object of \Contract (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setLocationId($this->getLocationId());
        $copyObj->setBuyerCustomerId($this->getBuyerCustomerId());
        $copyObj->setSellerCustomerId($this->getSellerCustomerId());
        $copyObj->setBuyerAgentId($this->getBuyerAgentId());
        $copyObj->setSellerAgentId($this->getSellerAgentId());
        $copyObj->setCompletedTime($this->getCompletedTime());
        $copyObj->setCompletedDate($this->getCompletedDate());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getMovesRelatedByBuyerContractId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMoveRelatedByBuyerContractId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMovesRelatedBySellerContractId() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMoveRelatedBySellerContractId($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getServiceContracts() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addServiceContract($relObj->copy($deepCopy));
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
     * @return \Contract Clone of current object.
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
     * Declares an association between this object and a ChildLocation object.
     *
     * @param  ChildLocation $v
     * @return $this|\Contract The current object (for fluent API support)
     * @throws PropelException
     */
    public function setLocation(ChildLocation $v = null)
    {
        if ($v === null) {
            $this->setLocationId(NULL);
        } else {
            $this->setLocationId($v->getId());
        }

        $this->aLocation = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildLocation object, it will not be re-added.
        if ($v !== null) {
            $v->addContract($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildLocation object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildLocation The associated ChildLocation object.
     * @throws PropelException
     */
    public function getLocation(ConnectionInterface $con = null)
    {
        if ($this->aLocation === null && ($this->location_id !== null)) {
            $this->aLocation = ChildLocationQuery::create()->findPk($this->location_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aLocation->addContracts($this);
             */
        }

        return $this->aLocation;
    }

    /**
     * Declares an association between this object and a ChildCustomer object.
     *
     * @param  ChildCustomer $v
     * @return $this|\Contract The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCustomerRelatedByBuyerCustomerId(ChildCustomer $v = null)
    {
        if ($v === null) {
            $this->setBuyerCustomerId(NULL);
        } else {
            $this->setBuyerCustomerId($v->getId());
        }

        $this->aCustomerRelatedByBuyerCustomerId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCustomer object, it will not be re-added.
        if ($v !== null) {
            $v->addContractRelatedByBuyerCustomerId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCustomer object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildCustomer The associated ChildCustomer object.
     * @throws PropelException
     */
    public function getCustomerRelatedByBuyerCustomerId(ConnectionInterface $con = null)
    {
        if ($this->aCustomerRelatedByBuyerCustomerId === null && ($this->buyer_customer_id !== null)) {
            $this->aCustomerRelatedByBuyerCustomerId = ChildCustomerQuery::create()->findPk($this->buyer_customer_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCustomerRelatedByBuyerCustomerId->addContractsRelatedByBuyerCustomerId($this);
             */
        }

        return $this->aCustomerRelatedByBuyerCustomerId;
    }

    /**
     * Declares an association between this object and a ChildCustomer object.
     *
     * @param  ChildCustomer $v
     * @return $this|\Contract The current object (for fluent API support)
     * @throws PropelException
     */
    public function setCustomerRelatedBySellerCustomerId(ChildCustomer $v = null)
    {
        if ($v === null) {
            $this->setSellerCustomerId(NULL);
        } else {
            $this->setSellerCustomerId($v->getId());
        }

        $this->aCustomerRelatedBySellerCustomerId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildCustomer object, it will not be re-added.
        if ($v !== null) {
            $v->addContractRelatedBySellerCustomerId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildCustomer object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildCustomer The associated ChildCustomer object.
     * @throws PropelException
     */
    public function getCustomerRelatedBySellerCustomerId(ConnectionInterface $con = null)
    {
        if ($this->aCustomerRelatedBySellerCustomerId === null && ($this->seller_customer_id !== null)) {
            $this->aCustomerRelatedBySellerCustomerId = ChildCustomerQuery::create()->findPk($this->seller_customer_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aCustomerRelatedBySellerCustomerId->addContractsRelatedBySellerCustomerId($this);
             */
        }

        return $this->aCustomerRelatedBySellerCustomerId;
    }

    /**
     * Declares an association between this object and a ChildAgent object.
     *
     * @param  ChildAgent $v
     * @return $this|\Contract The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAgentRelatedByBuyerAgentId(ChildAgent $v = null)
    {
        if ($v === null) {
            $this->setBuyerAgentId(NULL);
        } else {
            $this->setBuyerAgentId($v->getId());
        }

        $this->aAgentRelatedByBuyerAgentId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildAgent object, it will not be re-added.
        if ($v !== null) {
            $v->addContractRelatedByBuyerAgentId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildAgent object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildAgent The associated ChildAgent object.
     * @throws PropelException
     */
    public function getAgentRelatedByBuyerAgentId(ConnectionInterface $con = null)
    {
        if ($this->aAgentRelatedByBuyerAgentId === null && ($this->buyer_agent_id !== null)) {
            $this->aAgentRelatedByBuyerAgentId = ChildAgentQuery::create()->findPk($this->buyer_agent_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAgentRelatedByBuyerAgentId->addContractsRelatedByBuyerAgentId($this);
             */
        }

        return $this->aAgentRelatedByBuyerAgentId;
    }

    /**
     * Declares an association between this object and a ChildAgent object.
     *
     * @param  ChildAgent $v
     * @return $this|\Contract The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAgentRelatedBySellerAgentId(ChildAgent $v = null)
    {
        if ($v === null) {
            $this->setSellerAgentId(NULL);
        } else {
            $this->setSellerAgentId($v->getId());
        }

        $this->aAgentRelatedBySellerAgentId = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildAgent object, it will not be re-added.
        if ($v !== null) {
            $v->addContractRelatedBySellerAgentId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildAgent object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildAgent The associated ChildAgent object.
     * @throws PropelException
     */
    public function getAgentRelatedBySellerAgentId(ConnectionInterface $con = null)
    {
        if ($this->aAgentRelatedBySellerAgentId === null && ($this->seller_agent_id !== null)) {
            $this->aAgentRelatedBySellerAgentId = ChildAgentQuery::create()->findPk($this->seller_agent_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAgentRelatedBySellerAgentId->addContractsRelatedBySellerAgentId($this);
             */
        }

        return $this->aAgentRelatedBySellerAgentId;
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
        if ('MoveRelatedByBuyerContractId' == $relationName) {
            return $this->initMovesRelatedByBuyerContractId();
        }
        if ('MoveRelatedBySellerContractId' == $relationName) {
            return $this->initMovesRelatedBySellerContractId();
        }
        if ('ServiceContract' == $relationName) {
            return $this->initServiceContracts();
        }
    }

    /**
     * Clears out the collMovesRelatedByBuyerContractId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addMovesRelatedByBuyerContractId()
     */
    public function clearMovesRelatedByBuyerContractId()
    {
        $this->collMovesRelatedByBuyerContractId = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collMovesRelatedByBuyerContractId collection loaded partially.
     */
    public function resetPartialMovesRelatedByBuyerContractId($v = true)
    {
        $this->collMovesRelatedByBuyerContractIdPartial = $v;
    }

    /**
     * Initializes the collMovesRelatedByBuyerContractId collection.
     *
     * By default this just sets the collMovesRelatedByBuyerContractId collection to an empty array (like clearcollMovesRelatedByBuyerContractId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMovesRelatedByBuyerContractId($overrideExisting = true)
    {
        if (null !== $this->collMovesRelatedByBuyerContractId && !$overrideExisting) {
            return;
        }
        $this->collMovesRelatedByBuyerContractId = new ObjectCollection();
        $this->collMovesRelatedByBuyerContractId->setModel('\Move');
    }

    /**
     * Gets an array of ChildMove objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildContract is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildMove[] List of ChildMove objects
     * @throws PropelException
     */
    public function getMovesRelatedByBuyerContractId(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collMovesRelatedByBuyerContractIdPartial && !$this->isNew();
        if (null === $this->collMovesRelatedByBuyerContractId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMovesRelatedByBuyerContractId) {
                // return empty collection
                $this->initMovesRelatedByBuyerContractId();
            } else {
                $collMovesRelatedByBuyerContractId = ChildMoveQuery::create(null, $criteria)
                    ->filterByContractRelatedByBuyerContractId($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collMovesRelatedByBuyerContractIdPartial && count($collMovesRelatedByBuyerContractId)) {
                        $this->initMovesRelatedByBuyerContractId(false);

                        foreach ($collMovesRelatedByBuyerContractId as $obj) {
                            if (false == $this->collMovesRelatedByBuyerContractId->contains($obj)) {
                                $this->collMovesRelatedByBuyerContractId->append($obj);
                            }
                        }

                        $this->collMovesRelatedByBuyerContractIdPartial = true;
                    }

                    return $collMovesRelatedByBuyerContractId;
                }

                if ($partial && $this->collMovesRelatedByBuyerContractId) {
                    foreach ($this->collMovesRelatedByBuyerContractId as $obj) {
                        if ($obj->isNew()) {
                            $collMovesRelatedByBuyerContractId[] = $obj;
                        }
                    }
                }

                $this->collMovesRelatedByBuyerContractId = $collMovesRelatedByBuyerContractId;
                $this->collMovesRelatedByBuyerContractIdPartial = false;
            }
        }

        return $this->collMovesRelatedByBuyerContractId;
    }

    /**
     * Sets a collection of ChildMove objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $movesRelatedByBuyerContractId A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function setMovesRelatedByBuyerContractId(Collection $movesRelatedByBuyerContractId, ConnectionInterface $con = null)
    {
        /** @var ChildMove[] $movesRelatedByBuyerContractIdToDelete */
        $movesRelatedByBuyerContractIdToDelete = $this->getMovesRelatedByBuyerContractId(new Criteria(), $con)->diff($movesRelatedByBuyerContractId);


        $this->movesRelatedByBuyerContractIdScheduledForDeletion = $movesRelatedByBuyerContractIdToDelete;

        foreach ($movesRelatedByBuyerContractIdToDelete as $moveRelatedByBuyerContractIdRemoved) {
            $moveRelatedByBuyerContractIdRemoved->setContractRelatedByBuyerContractId(null);
        }

        $this->collMovesRelatedByBuyerContractId = null;
        foreach ($movesRelatedByBuyerContractId as $moveRelatedByBuyerContractId) {
            $this->addMoveRelatedByBuyerContractId($moveRelatedByBuyerContractId);
        }

        $this->collMovesRelatedByBuyerContractId = $movesRelatedByBuyerContractId;
        $this->collMovesRelatedByBuyerContractIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Move objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Move objects.
     * @throws PropelException
     */
    public function countMovesRelatedByBuyerContractId(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collMovesRelatedByBuyerContractIdPartial && !$this->isNew();
        if (null === $this->collMovesRelatedByBuyerContractId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMovesRelatedByBuyerContractId) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMovesRelatedByBuyerContractId());
            }

            $query = ChildMoveQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByContractRelatedByBuyerContractId($this)
                ->count($con);
        }

        return count($this->collMovesRelatedByBuyerContractId);
    }

    /**
     * Method called to associate a ChildMove object to this object
     * through the ChildMove foreign key attribute.
     *
     * @param  ChildMove $l ChildMove
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function addMoveRelatedByBuyerContractId(ChildMove $l)
    {
        if ($this->collMovesRelatedByBuyerContractId === null) {
            $this->initMovesRelatedByBuyerContractId();
            $this->collMovesRelatedByBuyerContractIdPartial = true;
        }

        if (!$this->collMovesRelatedByBuyerContractId->contains($l)) {
            $this->doAddMoveRelatedByBuyerContractId($l);
        }

        return $this;
    }

    /**
     * @param ChildMove $moveRelatedByBuyerContractId The ChildMove object to add.
     */
    protected function doAddMoveRelatedByBuyerContractId(ChildMove $moveRelatedByBuyerContractId)
    {
        $this->collMovesRelatedByBuyerContractId[]= $moveRelatedByBuyerContractId;
        $moveRelatedByBuyerContractId->setContractRelatedByBuyerContractId($this);
    }

    /**
     * @param  ChildMove $moveRelatedByBuyerContractId The ChildMove object to remove.
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function removeMoveRelatedByBuyerContractId(ChildMove $moveRelatedByBuyerContractId)
    {
        if ($this->getMovesRelatedByBuyerContractId()->contains($moveRelatedByBuyerContractId)) {
            $pos = $this->collMovesRelatedByBuyerContractId->search($moveRelatedByBuyerContractId);
            $this->collMovesRelatedByBuyerContractId->remove($pos);
            if (null === $this->movesRelatedByBuyerContractIdScheduledForDeletion) {
                $this->movesRelatedByBuyerContractIdScheduledForDeletion = clone $this->collMovesRelatedByBuyerContractId;
                $this->movesRelatedByBuyerContractIdScheduledForDeletion->clear();
            }
            $this->movesRelatedByBuyerContractIdScheduledForDeletion[]= clone $moveRelatedByBuyerContractId;
            $moveRelatedByBuyerContractId->setContractRelatedByBuyerContractId(null);
        }

        return $this;
    }

    /**
     * Clears out the collMovesRelatedBySellerContractId collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addMovesRelatedBySellerContractId()
     */
    public function clearMovesRelatedBySellerContractId()
    {
        $this->collMovesRelatedBySellerContractId = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collMovesRelatedBySellerContractId collection loaded partially.
     */
    public function resetPartialMovesRelatedBySellerContractId($v = true)
    {
        $this->collMovesRelatedBySellerContractIdPartial = $v;
    }

    /**
     * Initializes the collMovesRelatedBySellerContractId collection.
     *
     * By default this just sets the collMovesRelatedBySellerContractId collection to an empty array (like clearcollMovesRelatedBySellerContractId());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMovesRelatedBySellerContractId($overrideExisting = true)
    {
        if (null !== $this->collMovesRelatedBySellerContractId && !$overrideExisting) {
            return;
        }
        $this->collMovesRelatedBySellerContractId = new ObjectCollection();
        $this->collMovesRelatedBySellerContractId->setModel('\Move');
    }

    /**
     * Gets an array of ChildMove objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildContract is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildMove[] List of ChildMove objects
     * @throws PropelException
     */
    public function getMovesRelatedBySellerContractId(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collMovesRelatedBySellerContractIdPartial && !$this->isNew();
        if (null === $this->collMovesRelatedBySellerContractId || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMovesRelatedBySellerContractId) {
                // return empty collection
                $this->initMovesRelatedBySellerContractId();
            } else {
                $collMovesRelatedBySellerContractId = ChildMoveQuery::create(null, $criteria)
                    ->filterByContractRelatedBySellerContractId($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collMovesRelatedBySellerContractIdPartial && count($collMovesRelatedBySellerContractId)) {
                        $this->initMovesRelatedBySellerContractId(false);

                        foreach ($collMovesRelatedBySellerContractId as $obj) {
                            if (false == $this->collMovesRelatedBySellerContractId->contains($obj)) {
                                $this->collMovesRelatedBySellerContractId->append($obj);
                            }
                        }

                        $this->collMovesRelatedBySellerContractIdPartial = true;
                    }

                    return $collMovesRelatedBySellerContractId;
                }

                if ($partial && $this->collMovesRelatedBySellerContractId) {
                    foreach ($this->collMovesRelatedBySellerContractId as $obj) {
                        if ($obj->isNew()) {
                            $collMovesRelatedBySellerContractId[] = $obj;
                        }
                    }
                }

                $this->collMovesRelatedBySellerContractId = $collMovesRelatedBySellerContractId;
                $this->collMovesRelatedBySellerContractIdPartial = false;
            }
        }

        return $this->collMovesRelatedBySellerContractId;
    }

    /**
     * Sets a collection of ChildMove objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $movesRelatedBySellerContractId A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function setMovesRelatedBySellerContractId(Collection $movesRelatedBySellerContractId, ConnectionInterface $con = null)
    {
        /** @var ChildMove[] $movesRelatedBySellerContractIdToDelete */
        $movesRelatedBySellerContractIdToDelete = $this->getMovesRelatedBySellerContractId(new Criteria(), $con)->diff($movesRelatedBySellerContractId);


        $this->movesRelatedBySellerContractIdScheduledForDeletion = $movesRelatedBySellerContractIdToDelete;

        foreach ($movesRelatedBySellerContractIdToDelete as $moveRelatedBySellerContractIdRemoved) {
            $moveRelatedBySellerContractIdRemoved->setContractRelatedBySellerContractId(null);
        }

        $this->collMovesRelatedBySellerContractId = null;
        foreach ($movesRelatedBySellerContractId as $moveRelatedBySellerContractId) {
            $this->addMoveRelatedBySellerContractId($moveRelatedBySellerContractId);
        }

        $this->collMovesRelatedBySellerContractId = $movesRelatedBySellerContractId;
        $this->collMovesRelatedBySellerContractIdPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Move objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Move objects.
     * @throws PropelException
     */
    public function countMovesRelatedBySellerContractId(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collMovesRelatedBySellerContractIdPartial && !$this->isNew();
        if (null === $this->collMovesRelatedBySellerContractId || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMovesRelatedBySellerContractId) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMovesRelatedBySellerContractId());
            }

            $query = ChildMoveQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByContractRelatedBySellerContractId($this)
                ->count($con);
        }

        return count($this->collMovesRelatedBySellerContractId);
    }

    /**
     * Method called to associate a ChildMove object to this object
     * through the ChildMove foreign key attribute.
     *
     * @param  ChildMove $l ChildMove
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function addMoveRelatedBySellerContractId(ChildMove $l)
    {
        if ($this->collMovesRelatedBySellerContractId === null) {
            $this->initMovesRelatedBySellerContractId();
            $this->collMovesRelatedBySellerContractIdPartial = true;
        }

        if (!$this->collMovesRelatedBySellerContractId->contains($l)) {
            $this->doAddMoveRelatedBySellerContractId($l);
        }

        return $this;
    }

    /**
     * @param ChildMove $moveRelatedBySellerContractId The ChildMove object to add.
     */
    protected function doAddMoveRelatedBySellerContractId(ChildMove $moveRelatedBySellerContractId)
    {
        $this->collMovesRelatedBySellerContractId[]= $moveRelatedBySellerContractId;
        $moveRelatedBySellerContractId->setContractRelatedBySellerContractId($this);
    }

    /**
     * @param  ChildMove $moveRelatedBySellerContractId The ChildMove object to remove.
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function removeMoveRelatedBySellerContractId(ChildMove $moveRelatedBySellerContractId)
    {
        if ($this->getMovesRelatedBySellerContractId()->contains($moveRelatedBySellerContractId)) {
            $pos = $this->collMovesRelatedBySellerContractId->search($moveRelatedBySellerContractId);
            $this->collMovesRelatedBySellerContractId->remove($pos);
            if (null === $this->movesRelatedBySellerContractIdScheduledForDeletion) {
                $this->movesRelatedBySellerContractIdScheduledForDeletion = clone $this->collMovesRelatedBySellerContractId;
                $this->movesRelatedBySellerContractIdScheduledForDeletion->clear();
            }
            $this->movesRelatedBySellerContractIdScheduledForDeletion[]= clone $moveRelatedBySellerContractId;
            $moveRelatedBySellerContractId->setContractRelatedBySellerContractId(null);
        }

        return $this;
    }

    /**
     * Clears out the collServiceContracts collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addServiceContracts()
     */
    public function clearServiceContracts()
    {
        $this->collServiceContracts = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collServiceContracts collection loaded partially.
     */
    public function resetPartialServiceContracts($v = true)
    {
        $this->collServiceContractsPartial = $v;
    }

    /**
     * Initializes the collServiceContracts collection.
     *
     * By default this just sets the collServiceContracts collection to an empty array (like clearcollServiceContracts());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initServiceContracts($overrideExisting = true)
    {
        if (null !== $this->collServiceContracts && !$overrideExisting) {
            return;
        }
        $this->collServiceContracts = new ObjectCollection();
        $this->collServiceContracts->setModel('\ServiceContract');
    }

    /**
     * Gets an array of ChildServiceContract objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildContract is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildServiceContract[] List of ChildServiceContract objects
     * @throws PropelException
     */
    public function getServiceContracts(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collServiceContractsPartial && !$this->isNew();
        if (null === $this->collServiceContracts || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collServiceContracts) {
                // return empty collection
                $this->initServiceContracts();
            } else {
                $collServiceContracts = ChildServiceContractQuery::create(null, $criteria)
                    ->filterByContract($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collServiceContractsPartial && count($collServiceContracts)) {
                        $this->initServiceContracts(false);

                        foreach ($collServiceContracts as $obj) {
                            if (false == $this->collServiceContracts->contains($obj)) {
                                $this->collServiceContracts->append($obj);
                            }
                        }

                        $this->collServiceContractsPartial = true;
                    }

                    return $collServiceContracts;
                }

                if ($partial && $this->collServiceContracts) {
                    foreach ($this->collServiceContracts as $obj) {
                        if ($obj->isNew()) {
                            $collServiceContracts[] = $obj;
                        }
                    }
                }

                $this->collServiceContracts = $collServiceContracts;
                $this->collServiceContractsPartial = false;
            }
        }

        return $this->collServiceContracts;
    }

    /**
     * Sets a collection of ChildServiceContract objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $serviceContracts A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function setServiceContracts(Collection $serviceContracts, ConnectionInterface $con = null)
    {
        /** @var ChildServiceContract[] $serviceContractsToDelete */
        $serviceContractsToDelete = $this->getServiceContracts(new Criteria(), $con)->diff($serviceContracts);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->serviceContractsScheduledForDeletion = clone $serviceContractsToDelete;

        foreach ($serviceContractsToDelete as $serviceContractRemoved) {
            $serviceContractRemoved->setContract(null);
        }

        $this->collServiceContracts = null;
        foreach ($serviceContracts as $serviceContract) {
            $this->addServiceContract($serviceContract);
        }

        $this->collServiceContracts = $serviceContracts;
        $this->collServiceContractsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ServiceContract objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related ServiceContract objects.
     * @throws PropelException
     */
    public function countServiceContracts(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collServiceContractsPartial && !$this->isNew();
        if (null === $this->collServiceContracts || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collServiceContracts) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getServiceContracts());
            }

            $query = ChildServiceContractQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByContract($this)
                ->count($con);
        }

        return count($this->collServiceContracts);
    }

    /**
     * Method called to associate a ChildServiceContract object to this object
     * through the ChildServiceContract foreign key attribute.
     *
     * @param  ChildServiceContract $l ChildServiceContract
     * @return $this|\Contract The current object (for fluent API support)
     */
    public function addServiceContract(ChildServiceContract $l)
    {
        if ($this->collServiceContracts === null) {
            $this->initServiceContracts();
            $this->collServiceContractsPartial = true;
        }

        if (!$this->collServiceContracts->contains($l)) {
            $this->doAddServiceContract($l);
        }

        return $this;
    }

    /**
     * @param ChildServiceContract $serviceContract The ChildServiceContract object to add.
     */
    protected function doAddServiceContract(ChildServiceContract $serviceContract)
    {
        $this->collServiceContracts[]= $serviceContract;
        $serviceContract->setContract($this);
    }

    /**
     * @param  ChildServiceContract $serviceContract The ChildServiceContract object to remove.
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function removeServiceContract(ChildServiceContract $serviceContract)
    {
        if ($this->getServiceContracts()->contains($serviceContract)) {
            $pos = $this->collServiceContracts->search($serviceContract);
            $this->collServiceContracts->remove($pos);
            if (null === $this->serviceContractsScheduledForDeletion) {
                $this->serviceContractsScheduledForDeletion = clone $this->collServiceContracts;
                $this->serviceContractsScheduledForDeletion->clear();
            }
            $this->serviceContractsScheduledForDeletion[]= clone $serviceContract;
            $serviceContract->setContract(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Contract is new, it will return
     * an empty collection; or if this Contract has previously
     * been saved, it will retrieve related ServiceContracts from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Contract.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildServiceContract[] List of ChildServiceContract objects
     */
    public function getServiceContractsJoinService(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildServiceContractQuery::create(null, $criteria);
        $query->joinWith('Service', $joinBehavior);

        return $this->getServiceContracts($query, $con);
    }

    /**
     * Clears out the collServices collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addServices()
     */
    public function clearServices()
    {
        $this->collServices = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Initializes the collServices crossRef collection.
     *
     * By default this just sets the collServices collection to an empty collection (like clearServices());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @return void
     */
    public function initServices()
    {
        $this->collServices = new ObjectCollection();
        $this->collServicesPartial = true;

        $this->collServices->setModel('\Service');
    }

    /**
     * Checks if the collServices collection is loaded.
     *
     * @return bool
     */
    public function isServicesLoaded()
    {
        return null !== $this->collServices;
    }

    /**
     * Gets a collection of ChildService objects related by a many-to-many relationship
     * to the current object by way of the service_contract cross-reference table.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildContract is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return ObjectCollection|ChildService[] List of ChildService objects
     */
    public function getServices(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collServicesPartial && !$this->isNew();
        if (null === $this->collServices || null !== $criteria || $partial) {
            if ($this->isNew()) {
                // return empty collection
                if (null === $this->collServices) {
                    $this->initServices();
                }
            } else {

                $query = ChildServiceQuery::create(null, $criteria)
                    ->filterByContract($this);
                $collServices = $query->find($con);
                if (null !== $criteria) {
                    return $collServices;
                }

                if ($partial && $this->collServices) {
                    //make sure that already added objects gets added to the list of the database.
                    foreach ($this->collServices as $obj) {
                        if (!$collServices->contains($obj)) {
                            $collServices[] = $obj;
                        }
                    }
                }

                $this->collServices = $collServices;
                $this->collServicesPartial = false;
            }
        }

        return $this->collServices;
    }

    /**
     * Sets a collection of Service objects related by a many-to-many relationship
     * to the current object by way of the service_contract cross-reference table.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param  Collection $services A Propel collection.
     * @param  ConnectionInterface $con Optional connection object
     * @return $this|ChildContract The current object (for fluent API support)
     */
    public function setServices(Collection $services, ConnectionInterface $con = null)
    {
        $this->clearServices();
        $currentServices = $this->getServices();

        $servicesScheduledForDeletion = $currentServices->diff($services);

        foreach ($servicesScheduledForDeletion as $toDelete) {
            $this->removeService($toDelete);
        }

        foreach ($services as $service) {
            if (!$currentServices->contains($service)) {
                $this->doAddService($service);
            }
        }

        $this->collServicesPartial = false;
        $this->collServices = $services;

        return $this;
    }

    /**
     * Gets the number of Service objects related by a many-to-many relationship
     * to the current object by way of the service_contract cross-reference table.
     *
     * @param      Criteria $criteria Optional query object to filter the query
     * @param      boolean $distinct Set to true to force count distinct
     * @param      ConnectionInterface $con Optional connection object
     *
     * @return int the number of related Service objects
     */
    public function countServices(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collServicesPartial && !$this->isNew();
        if (null === $this->collServices || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collServices) {
                return 0;
            } else {

                if ($partial && !$criteria) {
                    return count($this->getServices());
                }

                $query = ChildServiceQuery::create(null, $criteria);
                if ($distinct) {
                    $query->distinct();
                }

                return $query
                    ->filterByContract($this)
                    ->count($con);
            }
        } else {
            return count($this->collServices);
        }
    }

    /**
     * Associate a ChildService to this object
     * through the service_contract cross reference table.
     *
     * @param ChildService $service
     * @return ChildContract The current object (for fluent API support)
     */
    public function addService(ChildService $service)
    {
        if ($this->collServices === null) {
            $this->initServices();
        }

        if (!$this->getServices()->contains($service)) {
            // only add it if the **same** object is not already associated
            $this->collServices->push($service);
            $this->doAddService($service);
        }

        return $this;
    }

    /**
     *
     * @param ChildService $service
     */
    protected function doAddService(ChildService $service)
    {
        $serviceContract = new ChildServiceContract();

        $serviceContract->setService($service);

        $serviceContract->setContract($this);

        $this->addServiceContract($serviceContract);

        // set the back reference to this object directly as using provided method either results
        // in endless loop or in multiple relations
        if (!$service->isContractsLoaded()) {
            $service->initContracts();
            $service->getContracts()->push($this);
        } elseif (!$service->getContracts()->contains($this)) {
            $service->getContracts()->push($this);
        }

    }

    /**
     * Remove service of this object
     * through the service_contract cross reference table.
     *
     * @param ChildService $service
     * @return ChildContract The current object (for fluent API support)
     */
    public function removeService(ChildService $service)
    {
        if ($this->getServices()->contains($service)) { $serviceContract = new ChildServiceContract();

            $serviceContract->setService($service);
            if ($service->isContractsLoaded()) {
                //remove the back reference if available
                $service->getContracts()->removeObject($this);
            }

            $serviceContract->setContract($this);
            $this->removeServiceContract(clone $serviceContract);
            $serviceContract->clear();

            $this->collServices->remove($this->collServices->search($service));

            if (null === $this->servicesScheduledForDeletion) {
                $this->servicesScheduledForDeletion = clone $this->collServices;
                $this->servicesScheduledForDeletion->clear();
            }

            $this->servicesScheduledForDeletion->push($service);
        }


        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        if (null !== $this->aLocation) {
            $this->aLocation->removeContract($this);
        }
        if (null !== $this->aCustomerRelatedByBuyerCustomerId) {
            $this->aCustomerRelatedByBuyerCustomerId->removeContractRelatedByBuyerCustomerId($this);
        }
        if (null !== $this->aCustomerRelatedBySellerCustomerId) {
            $this->aCustomerRelatedBySellerCustomerId->removeContractRelatedBySellerCustomerId($this);
        }
        if (null !== $this->aAgentRelatedByBuyerAgentId) {
            $this->aAgentRelatedByBuyerAgentId->removeContractRelatedByBuyerAgentId($this);
        }
        if (null !== $this->aAgentRelatedBySellerAgentId) {
            $this->aAgentRelatedBySellerAgentId->removeContractRelatedBySellerAgentId($this);
        }
        $this->id = null;
        $this->location_id = null;
        $this->buyer_customer_id = null;
        $this->seller_customer_id = null;
        $this->buyer_agent_id = null;
        $this->seller_agent_id = null;
        $this->completed_time = null;
        $this->completed_date = null;
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
            if ($this->collMovesRelatedByBuyerContractId) {
                foreach ($this->collMovesRelatedByBuyerContractId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMovesRelatedBySellerContractId) {
                foreach ($this->collMovesRelatedBySellerContractId as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collServiceContracts) {
                foreach ($this->collServiceContracts as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collServices) {
                foreach ($this->collServices as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collMovesRelatedByBuyerContractId = null;
        $this->collMovesRelatedBySellerContractId = null;
        $this->collServiceContracts = null;
        $this->collServices = null;
        $this->aLocation = null;
        $this->aCustomerRelatedByBuyerCustomerId = null;
        $this->aCustomerRelatedBySellerCustomerId = null;
        $this->aAgentRelatedByBuyerAgentId = null;
        $this->aAgentRelatedBySellerAgentId = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ContractTableMap::DEFAULT_STRING_FORMAT);
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
