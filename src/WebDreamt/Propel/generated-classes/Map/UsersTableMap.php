<?php

namespace Map;

use \Users;
use \UsersQuery;
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
 * This class defines the structure of the 'users' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class UsersTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.Map.UsersTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'users';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\Users';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'Users';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 14;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 14;

    /**
     * the column name for the ID field
     */
    const COL_ID = 'users.ID';

    /**
     * the column name for the EMAIL field
     */
    const COL_EMAIL = 'users.EMAIL';

    /**
     * the column name for the PASSWORD field
     */
    const COL_PASSWORD = 'users.PASSWORD';

    /**
     * the column name for the PERMISSIONS field
     */
    const COL_PERMISSIONS = 'users.PERMISSIONS';

    /**
     * the column name for the ACTIVATED field
     */
    const COL_ACTIVATED = 'users.ACTIVATED';

    /**
     * the column name for the ACTIVATION_CODE field
     */
    const COL_ACTIVATION_CODE = 'users.ACTIVATION_CODE';

    /**
     * the column name for the ACTIVATED_AT field
     */
    const COL_ACTIVATED_AT = 'users.ACTIVATED_AT';

    /**
     * the column name for the LAST_LOGIN field
     */
    const COL_LAST_LOGIN = 'users.LAST_LOGIN';

    /**
     * the column name for the PERSIST_CODE field
     */
    const COL_PERSIST_CODE = 'users.PERSIST_CODE';

    /**
     * the column name for the RESET_PASSWORD_CODE field
     */
    const COL_RESET_PASSWORD_CODE = 'users.RESET_PASSWORD_CODE';

    /**
     * the column name for the FIRST_NAME field
     */
    const COL_FIRST_NAME = 'users.FIRST_NAME';

    /**
     * the column name for the LAST_NAME field
     */
    const COL_LAST_NAME = 'users.LAST_NAME';

    /**
     * the column name for the CREATED_AT field
     */
    const COL_CREATED_AT = 'users.CREATED_AT';

    /**
     * the column name for the UPDATED_AT field
     */
    const COL_UPDATED_AT = 'users.UPDATED_AT';

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
        self::TYPE_PHPNAME       => array('Id', 'Email', 'Password', 'Permissions', 'Activated', 'ActivationCode', 'ActivatedAt', 'LastLogin', 'PersistCode', 'ResetPasswordCode', 'FirstName', 'LastName', 'CreatedAt', 'UpdatedAt', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'email', 'password', 'permissions', 'activated', 'activationCode', 'activatedAt', 'lastLogin', 'persistCode', 'resetPasswordCode', 'firstName', 'lastName', 'createdAt', 'updatedAt', ),
        self::TYPE_COLNAME       => array(UsersTableMap::COL_ID, UsersTableMap::COL_EMAIL, UsersTableMap::COL_PASSWORD, UsersTableMap::COL_PERMISSIONS, UsersTableMap::COL_ACTIVATED, UsersTableMap::COL_ACTIVATION_CODE, UsersTableMap::COL_ACTIVATED_AT, UsersTableMap::COL_LAST_LOGIN, UsersTableMap::COL_PERSIST_CODE, UsersTableMap::COL_RESET_PASSWORD_CODE, UsersTableMap::COL_FIRST_NAME, UsersTableMap::COL_LAST_NAME, UsersTableMap::COL_CREATED_AT, UsersTableMap::COL_UPDATED_AT, ),
        self::TYPE_RAW_COLNAME   => array('COL_ID', 'COL_EMAIL', 'COL_PASSWORD', 'COL_PERMISSIONS', 'COL_ACTIVATED', 'COL_ACTIVATION_CODE', 'COL_ACTIVATED_AT', 'COL_LAST_LOGIN', 'COL_PERSIST_CODE', 'COL_RESET_PASSWORD_CODE', 'COL_FIRST_NAME', 'COL_LAST_NAME', 'COL_CREATED_AT', 'COL_UPDATED_AT', ),
        self::TYPE_FIELDNAME     => array('id', 'email', 'password', 'permissions', 'activated', 'activation_code', 'activated_at', 'last_login', 'persist_code', 'reset_password_code', 'first_name', 'last_name', 'created_at', 'updated_at', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'Email' => 1, 'Password' => 2, 'Permissions' => 3, 'Activated' => 4, 'ActivationCode' => 5, 'ActivatedAt' => 6, 'LastLogin' => 7, 'PersistCode' => 8, 'ResetPasswordCode' => 9, 'FirstName' => 10, 'LastName' => 11, 'CreatedAt' => 12, 'UpdatedAt' => 13, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'email' => 1, 'password' => 2, 'permissions' => 3, 'activated' => 4, 'activationCode' => 5, 'activatedAt' => 6, 'lastLogin' => 7, 'persistCode' => 8, 'resetPasswordCode' => 9, 'firstName' => 10, 'lastName' => 11, 'createdAt' => 12, 'updatedAt' => 13, ),
        self::TYPE_COLNAME       => array(UsersTableMap::COL_ID => 0, UsersTableMap::COL_EMAIL => 1, UsersTableMap::COL_PASSWORD => 2, UsersTableMap::COL_PERMISSIONS => 3, UsersTableMap::COL_ACTIVATED => 4, UsersTableMap::COL_ACTIVATION_CODE => 5, UsersTableMap::COL_ACTIVATED_AT => 6, UsersTableMap::COL_LAST_LOGIN => 7, UsersTableMap::COL_PERSIST_CODE => 8, UsersTableMap::COL_RESET_PASSWORD_CODE => 9, UsersTableMap::COL_FIRST_NAME => 10, UsersTableMap::COL_LAST_NAME => 11, UsersTableMap::COL_CREATED_AT => 12, UsersTableMap::COL_UPDATED_AT => 13, ),
        self::TYPE_RAW_COLNAME   => array('COL_ID' => 0, 'COL_EMAIL' => 1, 'COL_PASSWORD' => 2, 'COL_PERMISSIONS' => 3, 'COL_ACTIVATED' => 4, 'COL_ACTIVATION_CODE' => 5, 'COL_ACTIVATED_AT' => 6, 'COL_LAST_LOGIN' => 7, 'COL_PERSIST_CODE' => 8, 'COL_RESET_PASSWORD_CODE' => 9, 'COL_FIRST_NAME' => 10, 'COL_LAST_NAME' => 11, 'COL_CREATED_AT' => 12, 'COL_UPDATED_AT' => 13, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'email' => 1, 'password' => 2, 'permissions' => 3, 'activated' => 4, 'activation_code' => 5, 'activated_at' => 6, 'last_login' => 7, 'persist_code' => 8, 'reset_password_code' => 9, 'first_name' => 10, 'last_name' => 11, 'created_at' => 12, 'updated_at' => 13, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, )
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
        $this->setName('users');
        $this->setPhpName('Users');
        $this->setClassName('\\Users');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, 10, null);
        $this->addColumn('EMAIL', 'Email', 'VARCHAR', true, 255, null);
        $this->addColumn('PASSWORD', 'Password', 'VARCHAR', true, 255, null);
        $this->addColumn('PERMISSIONS', 'Permissions', 'LONGVARCHAR', false, null, null);
        $this->addColumn('ACTIVATED', 'Activated', 'TINYINT', true, null, 0);
        $this->addColumn('ACTIVATION_CODE', 'ActivationCode', 'VARCHAR', false, 255, null);
        $this->addColumn('ACTIVATED_AT', 'ActivatedAt', 'VARCHAR', false, 255, null);
        $this->addColumn('LAST_LOGIN', 'LastLogin', 'VARCHAR', false, 255, null);
        $this->addColumn('PERSIST_CODE', 'PersistCode', 'VARCHAR', false, 255, null);
        $this->addColumn('RESET_PASSWORD_CODE', 'ResetPasswordCode', 'VARCHAR', false, 255, null);
        $this->addColumn('FIRST_NAME', 'FirstName', 'VARCHAR', false, 255, null);
        $this->addColumn('LAST_NAME', 'LastName', 'VARCHAR', false, 255, null);
        $this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', true, null, '0000-00-00 00:00:00');
        $this->addColumn('UPDATED_AT', 'UpdatedAt', 'TIMESTAMP', true, null, '0000-00-00 00:00:00');
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
        return $withPrefix ? UsersTableMap::CLASS_DEFAULT : UsersTableMap::OM_CLASS;
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
     * @return array           (Users object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = UsersTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = UsersTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + UsersTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = UsersTableMap::OM_CLASS;
            /** @var Users $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            UsersTableMap::addInstanceToPool($obj, $key);
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
            $key = UsersTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = UsersTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var Users $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                UsersTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(UsersTableMap::COL_ID);
            $criteria->addSelectColumn(UsersTableMap::COL_EMAIL);
            $criteria->addSelectColumn(UsersTableMap::COL_PASSWORD);
            $criteria->addSelectColumn(UsersTableMap::COL_PERMISSIONS);
            $criteria->addSelectColumn(UsersTableMap::COL_ACTIVATED);
            $criteria->addSelectColumn(UsersTableMap::COL_ACTIVATION_CODE);
            $criteria->addSelectColumn(UsersTableMap::COL_ACTIVATED_AT);
            $criteria->addSelectColumn(UsersTableMap::COL_LAST_LOGIN);
            $criteria->addSelectColumn(UsersTableMap::COL_PERSIST_CODE);
            $criteria->addSelectColumn(UsersTableMap::COL_RESET_PASSWORD_CODE);
            $criteria->addSelectColumn(UsersTableMap::COL_FIRST_NAME);
            $criteria->addSelectColumn(UsersTableMap::COL_LAST_NAME);
            $criteria->addSelectColumn(UsersTableMap::COL_CREATED_AT);
            $criteria->addSelectColumn(UsersTableMap::COL_UPDATED_AT);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.EMAIL');
            $criteria->addSelectColumn($alias . '.PASSWORD');
            $criteria->addSelectColumn($alias . '.PERMISSIONS');
            $criteria->addSelectColumn($alias . '.ACTIVATED');
            $criteria->addSelectColumn($alias . '.ACTIVATION_CODE');
            $criteria->addSelectColumn($alias . '.ACTIVATED_AT');
            $criteria->addSelectColumn($alias . '.LAST_LOGIN');
            $criteria->addSelectColumn($alias . '.PERSIST_CODE');
            $criteria->addSelectColumn($alias . '.RESET_PASSWORD_CODE');
            $criteria->addSelectColumn($alias . '.FIRST_NAME');
            $criteria->addSelectColumn($alias . '.LAST_NAME');
            $criteria->addSelectColumn($alias . '.CREATED_AT');
            $criteria->addSelectColumn($alias . '.UPDATED_AT');
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
        return Propel::getServiceContainer()->getDatabaseMap(UsersTableMap::DATABASE_NAME)->getTable(UsersTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(UsersTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(UsersTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new UsersTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a Users or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or Users object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(UsersTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Users) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(UsersTableMap::DATABASE_NAME);
            $criteria->add(UsersTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = UsersQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            UsersTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                UsersTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the users table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return UsersQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a Users or Criteria object.
     *
     * @param mixed               $criteria Criteria or Users object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UsersTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from Users object
        }

        if ($criteria->containsKey(UsersTableMap::COL_ID) && $criteria->keyContainsValue(UsersTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.UsersTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = UsersQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // UsersTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
UsersTableMap::buildTableMap();
