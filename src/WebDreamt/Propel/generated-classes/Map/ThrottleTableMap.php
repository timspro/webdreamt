<?php

namespace Map;

use \Throttle;
use \ThrottleQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'throttle' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class ThrottleTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.Map.ThrottleTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'throttle';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Throttle';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'Throttle';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 9;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 9;

    /**
     * the column name for the ID field
     */
    const COL_ID = 'throttle.ID';

    /**
     * the column name for the USER_ID field
     */
    const COL_USER_ID = 'throttle.USER_ID';

    /**
     * the column name for the IP_ADDRESS field
     */
    const COL_IP_ADDRESS = 'throttle.IP_ADDRESS';

    /**
     * the column name for the ATTEMPTS field
     */
    const COL_ATTEMPTS = 'throttle.ATTEMPTS';

    /**
     * the column name for the SUSPENDED field
     */
    const COL_SUSPENDED = 'throttle.SUSPENDED';

    /**
     * the column name for the BANNED field
     */
    const COL_BANNED = 'throttle.BANNED';

    /**
     * the column name for the LAST_ATTEMPT_AT field
     */
    const COL_LAST_ATTEMPT_AT = 'throttle.LAST_ATTEMPT_AT';

    /**
     * the column name for the SUSPENDED_AT field
     */
    const COL_SUSPENDED_AT = 'throttle.SUSPENDED_AT';

    /**
     * the column name for the BANNED_AT field
     */
    const COL_BANNED_AT = 'throttle.BANNED_AT';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'UserId', 'IpAddress', 'Attempts', 'Suspended', 'Banned', 'LastAttemptAt', 'SuspendedAt', 'BannedAt', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'userId', 'ipAddress', 'attempts', 'suspended', 'banned', 'lastAttemptAt', 'suspendedAt', 'bannedAt', ),
        self::TYPE_COLNAME       => array(ThrottleTableMap::COL_ID, ThrottleTableMap::COL_USER_ID, ThrottleTableMap::COL_IP_ADDRESS, ThrottleTableMap::COL_ATTEMPTS, ThrottleTableMap::COL_SUSPENDED, ThrottleTableMap::COL_BANNED, ThrottleTableMap::COL_LAST_ATTEMPT_AT, ThrottleTableMap::COL_SUSPENDED_AT, ThrottleTableMap::COL_BANNED_AT, ),
        self::TYPE_RAW_COLNAME   => array('COL_ID', 'COL_USER_ID', 'COL_IP_ADDRESS', 'COL_ATTEMPTS', 'COL_SUSPENDED', 'COL_BANNED', 'COL_LAST_ATTEMPT_AT', 'COL_SUSPENDED_AT', 'COL_BANNED_AT', ),
        self::TYPE_FIELDNAME     => array('id', 'user_id', 'ip_address', 'attempts', 'suspended', 'banned', 'last_attempt_at', 'suspended_at', 'banned_at', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'UserId' => 1, 'IpAddress' => 2, 'Attempts' => 3, 'Suspended' => 4, 'Banned' => 5, 'LastAttemptAt' => 6, 'SuspendedAt' => 7, 'BannedAt' => 8, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'userId' => 1, 'ipAddress' => 2, 'attempts' => 3, 'suspended' => 4, 'banned' => 5, 'lastAttemptAt' => 6, 'suspendedAt' => 7, 'bannedAt' => 8, ),
        self::TYPE_COLNAME       => array(ThrottleTableMap::COL_ID => 0, ThrottleTableMap::COL_USER_ID => 1, ThrottleTableMap::COL_IP_ADDRESS => 2, ThrottleTableMap::COL_ATTEMPTS => 3, ThrottleTableMap::COL_SUSPENDED => 4, ThrottleTableMap::COL_BANNED => 5, ThrottleTableMap::COL_LAST_ATTEMPT_AT => 6, ThrottleTableMap::COL_SUSPENDED_AT => 7, ThrottleTableMap::COL_BANNED_AT => 8, ),
        self::TYPE_RAW_COLNAME   => array('COL_ID' => 0, 'COL_USER_ID' => 1, 'COL_IP_ADDRESS' => 2, 'COL_ATTEMPTS' => 3, 'COL_SUSPENDED' => 4, 'COL_BANNED' => 5, 'COL_LAST_ATTEMPT_AT' => 6, 'COL_SUSPENDED_AT' => 7, 'COL_BANNED_AT' => 8, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'user_id' => 1, 'ip_address' => 2, 'attempts' => 3, 'suspended' => 4, 'banned' => 5, 'last_attempt_at' => 6, 'suspended_at' => 7, 'banned_at' => 8, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('throttle');
        $this->setPhpName('Throttle');
        $this->setClassName('\\Throttle');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, 10, null);
        $this->addColumn('USER_ID', 'UserId', 'INTEGER', true, 10, null);
        $this->addColumn('IP_ADDRESS', 'IpAddress', 'VARCHAR', false, 255, null);
        $this->addColumn('ATTEMPTS', 'Attempts', 'INTEGER', true, null, 0);
        $this->addColumn('SUSPENDED', 'Suspended', 'TINYINT', true, null, 0);
        $this->addColumn('BANNED', 'Banned', 'TINYINT', true, null, 0);
        $this->addColumn('LAST_ATTEMPT_AT', 'LastAttemptAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('SUSPENDED_AT', 'SuspendedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('BANNED_AT', 'BannedAt', 'TIMESTAMP', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }
    
    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? ThrottleTableMap::CLASS_DEFAULT : ThrottleTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (Throttle object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = ThrottleTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ThrottleTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ThrottleTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ThrottleTableMap::OM_CLASS;
            /** @var Throttle $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ThrottleTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();
    
        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = ThrottleTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ThrottleTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Throttle $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ThrottleTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(ThrottleTableMap::COL_ID);
            $criteria->addSelectColumn(ThrottleTableMap::COL_USER_ID);
            $criteria->addSelectColumn(ThrottleTableMap::COL_IP_ADDRESS);
            $criteria->addSelectColumn(ThrottleTableMap::COL_ATTEMPTS);
            $criteria->addSelectColumn(ThrottleTableMap::COL_SUSPENDED);
            $criteria->addSelectColumn(ThrottleTableMap::COL_BANNED);
            $criteria->addSelectColumn(ThrottleTableMap::COL_LAST_ATTEMPT_AT);
            $criteria->addSelectColumn(ThrottleTableMap::COL_SUSPENDED_AT);
            $criteria->addSelectColumn(ThrottleTableMap::COL_BANNED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.USER_ID');
            $criteria->addSelectColumn($alias . '.IP_ADDRESS');
            $criteria->addSelectColumn($alias . '.ATTEMPTS');
            $criteria->addSelectColumn($alias . '.SUSPENDED');
            $criteria->addSelectColumn($alias . '.BANNED');
            $criteria->addSelectColumn($alias . '.LAST_ATTEMPT_AT');
            $criteria->addSelectColumn($alias . '.SUSPENDED_AT');
            $criteria->addSelectColumn($alias . '.BANNED_AT');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(ThrottleTableMap::DATABASE_NAME)->getTable(ThrottleTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(ThrottleTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(ThrottleTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new ThrottleTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a Throttle or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Throttle object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ThrottleTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Throttle) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ThrottleTableMap::DATABASE_NAME);
            $criteria->add(ThrottleTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ThrottleQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ThrottleTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ThrottleTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the throttle table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return ThrottleQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Throttle or Criteria object.
     *
     * @param mixed               $criteria Criteria or Throttle object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ThrottleTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Throttle object
        }

        if ($criteria->containsKey(ThrottleTableMap::COL_ID) && $criteria->keyContainsValue(ThrottleTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ThrottleTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = ThrottleQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // ThrottleTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
ThrottleTableMap::buildTableMap();
