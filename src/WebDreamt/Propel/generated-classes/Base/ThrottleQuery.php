<?php

namespace Base;

use \Throttle as ChildThrottle;
use \ThrottleQuery as ChildThrottleQuery;
use \Exception;
use \PDO;
use Map\ThrottleTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'throttle' table.
 *
 * 
 *
 * @method     ChildThrottleQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildThrottleQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildThrottleQuery orderByIpAddress($order = Criteria::ASC) Order by the ip_address column
 * @method     ChildThrottleQuery orderByAttempts($order = Criteria::ASC) Order by the attempts column
 * @method     ChildThrottleQuery orderBySuspended($order = Criteria::ASC) Order by the suspended column
 * @method     ChildThrottleQuery orderByBanned($order = Criteria::ASC) Order by the banned column
 * @method     ChildThrottleQuery orderByLastAttemptAt($order = Criteria::ASC) Order by the last_attempt_at column
 * @method     ChildThrottleQuery orderBySuspendedAt($order = Criteria::ASC) Order by the suspended_at column
 * @method     ChildThrottleQuery orderByBannedAt($order = Criteria::ASC) Order by the banned_at column
 *
 * @method     ChildThrottleQuery groupById() Group by the id column
 * @method     ChildThrottleQuery groupByUserId() Group by the user_id column
 * @method     ChildThrottleQuery groupByIpAddress() Group by the ip_address column
 * @method     ChildThrottleQuery groupByAttempts() Group by the attempts column
 * @method     ChildThrottleQuery groupBySuspended() Group by the suspended column
 * @method     ChildThrottleQuery groupByBanned() Group by the banned column
 * @method     ChildThrottleQuery groupByLastAttemptAt() Group by the last_attempt_at column
 * @method     ChildThrottleQuery groupBySuspendedAt() Group by the suspended_at column
 * @method     ChildThrottleQuery groupByBannedAt() Group by the banned_at column
 *
 * @method     ChildThrottleQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildThrottleQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildThrottleQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildThrottle findOne(ConnectionInterface $con = null) Return the first ChildThrottle matching the query
 * @method     ChildThrottle findOneOrCreate(ConnectionInterface $con = null) Return the first ChildThrottle matching the query, or a new ChildThrottle object populated from the query conditions when no match is found
 *
 * @method     ChildThrottle findOneById(int $id) Return the first ChildThrottle filtered by the id column
 * @method     ChildThrottle findOneByUserId(int $user_id) Return the first ChildThrottle filtered by the user_id column
 * @method     ChildThrottle findOneByIpAddress(string $ip_address) Return the first ChildThrottle filtered by the ip_address column
 * @method     ChildThrottle findOneByAttempts(int $attempts) Return the first ChildThrottle filtered by the attempts column
 * @method     ChildThrottle findOneBySuspended(int $suspended) Return the first ChildThrottle filtered by the suspended column
 * @method     ChildThrottle findOneByBanned(int $banned) Return the first ChildThrottle filtered by the banned column
 * @method     ChildThrottle findOneByLastAttemptAt(string $last_attempt_at) Return the first ChildThrottle filtered by the last_attempt_at column
 * @method     ChildThrottle findOneBySuspendedAt(string $suspended_at) Return the first ChildThrottle filtered by the suspended_at column
 * @method     ChildThrottle findOneByBannedAt(string $banned_at) Return the first ChildThrottle filtered by the banned_at column
 *
 * @method     ChildThrottle[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildThrottle objects based on current ModelCriteria
 * @method     ChildThrottle[]|ObjectCollection findById(int $id) Return ChildThrottle objects filtered by the id column
 * @method     ChildThrottle[]|ObjectCollection findByUserId(int $user_id) Return ChildThrottle objects filtered by the user_id column
 * @method     ChildThrottle[]|ObjectCollection findByIpAddress(string $ip_address) Return ChildThrottle objects filtered by the ip_address column
 * @method     ChildThrottle[]|ObjectCollection findByAttempts(int $attempts) Return ChildThrottle objects filtered by the attempts column
 * @method     ChildThrottle[]|ObjectCollection findBySuspended(int $suspended) Return ChildThrottle objects filtered by the suspended column
 * @method     ChildThrottle[]|ObjectCollection findByBanned(int $banned) Return ChildThrottle objects filtered by the banned column
 * @method     ChildThrottle[]|ObjectCollection findByLastAttemptAt(string $last_attempt_at) Return ChildThrottle objects filtered by the last_attempt_at column
 * @method     ChildThrottle[]|ObjectCollection findBySuspendedAt(string $suspended_at) Return ChildThrottle objects filtered by the suspended_at column
 * @method     ChildThrottle[]|ObjectCollection findByBannedAt(string $banned_at) Return ChildThrottle objects filtered by the banned_at column
 * @method     ChildThrottle[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ThrottleQuery extends ModelCriteria
{
    
    /**
     * Initializes internal state of \Base\ThrottleQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Throttle', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildThrottleQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildThrottleQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildThrottleQuery) {
            return $criteria;
        }
        $query = new ChildThrottleQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildThrottle|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ThrottleTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ThrottleTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildThrottle A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT ID, USER_ID, IP_ADDRESS, ATTEMPTS, SUSPENDED, BANNED, LAST_ATTEMPT_AT, SUSPENDED_AT, BANNED_AT FROM throttle WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);            
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildThrottle $obj */
            $obj = new ChildThrottle();
            $obj->hydrate($row);
            ThrottleTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildThrottle|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ThrottleTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ThrottleTableMap::COL_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE user_id > 12
     * </code>
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_USER_ID, $userId, $comparison);
    }

    /**
     * Filter the query on the ip_address column
     *
     * Example usage:
     * <code>
     * $query->filterByIpAddress('fooValue');   // WHERE ip_address = 'fooValue'
     * $query->filterByIpAddress('%fooValue%'); // WHERE ip_address LIKE '%fooValue%'
     * </code>
     *
     * @param     string $ipAddress The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByIpAddress($ipAddress = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($ipAddress)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $ipAddress)) {
                $ipAddress = str_replace('*', '%', $ipAddress);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_IP_ADDRESS, $ipAddress, $comparison);
    }

    /**
     * Filter the query on the attempts column
     *
     * Example usage:
     * <code>
     * $query->filterByAttempts(1234); // WHERE attempts = 1234
     * $query->filterByAttempts(array(12, 34)); // WHERE attempts IN (12, 34)
     * $query->filterByAttempts(array('min' => 12)); // WHERE attempts > 12
     * </code>
     *
     * @param     mixed $attempts The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByAttempts($attempts = null, $comparison = null)
    {
        if (is_array($attempts)) {
            $useMinMax = false;
            if (isset($attempts['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_ATTEMPTS, $attempts['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($attempts['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_ATTEMPTS, $attempts['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_ATTEMPTS, $attempts, $comparison);
    }

    /**
     * Filter the query on the suspended column
     *
     * Example usage:
     * <code>
     * $query->filterBySuspended(1234); // WHERE suspended = 1234
     * $query->filterBySuspended(array(12, 34)); // WHERE suspended IN (12, 34)
     * $query->filterBySuspended(array('min' => 12)); // WHERE suspended > 12
     * </code>
     *
     * @param     mixed $suspended The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterBySuspended($suspended = null, $comparison = null)
    {
        if (is_array($suspended)) {
            $useMinMax = false;
            if (isset($suspended['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_SUSPENDED, $suspended['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($suspended['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_SUSPENDED, $suspended['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_SUSPENDED, $suspended, $comparison);
    }

    /**
     * Filter the query on the banned column
     *
     * Example usage:
     * <code>
     * $query->filterByBanned(1234); // WHERE banned = 1234
     * $query->filterByBanned(array(12, 34)); // WHERE banned IN (12, 34)
     * $query->filterByBanned(array('min' => 12)); // WHERE banned > 12
     * </code>
     *
     * @param     mixed $banned The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByBanned($banned = null, $comparison = null)
    {
        if (is_array($banned)) {
            $useMinMax = false;
            if (isset($banned['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_BANNED, $banned['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($banned['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_BANNED, $banned['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_BANNED, $banned, $comparison);
    }

    /**
     * Filter the query on the last_attempt_at column
     *
     * Example usage:
     * <code>
     * $query->filterByLastAttemptAt('2011-03-14'); // WHERE last_attempt_at = '2011-03-14'
     * $query->filterByLastAttemptAt('now'); // WHERE last_attempt_at = '2011-03-14'
     * $query->filterByLastAttemptAt(array('max' => 'yesterday')); // WHERE last_attempt_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $lastAttemptAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByLastAttemptAt($lastAttemptAt = null, $comparison = null)
    {
        if (is_array($lastAttemptAt)) {
            $useMinMax = false;
            if (isset($lastAttemptAt['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_LAST_ATTEMPT_AT, $lastAttemptAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastAttemptAt['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_LAST_ATTEMPT_AT, $lastAttemptAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_LAST_ATTEMPT_AT, $lastAttemptAt, $comparison);
    }

    /**
     * Filter the query on the suspended_at column
     *
     * Example usage:
     * <code>
     * $query->filterBySuspendedAt('2011-03-14'); // WHERE suspended_at = '2011-03-14'
     * $query->filterBySuspendedAt('now'); // WHERE suspended_at = '2011-03-14'
     * $query->filterBySuspendedAt(array('max' => 'yesterday')); // WHERE suspended_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $suspendedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterBySuspendedAt($suspendedAt = null, $comparison = null)
    {
        if (is_array($suspendedAt)) {
            $useMinMax = false;
            if (isset($suspendedAt['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_SUSPENDED_AT, $suspendedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($suspendedAt['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_SUSPENDED_AT, $suspendedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_SUSPENDED_AT, $suspendedAt, $comparison);
    }

    /**
     * Filter the query on the banned_at column
     *
     * Example usage:
     * <code>
     * $query->filterByBannedAt('2011-03-14'); // WHERE banned_at = '2011-03-14'
     * $query->filterByBannedAt('now'); // WHERE banned_at = '2011-03-14'
     * $query->filterByBannedAt(array('max' => 'yesterday')); // WHERE banned_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $bannedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function filterByBannedAt($bannedAt = null, $comparison = null)
    {
        if (is_array($bannedAt)) {
            $useMinMax = false;
            if (isset($bannedAt['min'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_BANNED_AT, $bannedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($bannedAt['max'])) {
                $this->addUsingAlias(ThrottleTableMap::COL_BANNED_AT, $bannedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ThrottleTableMap::COL_BANNED_AT, $bannedAt, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildThrottle $throttle Object to remove from the list of results
     *
     * @return $this|ChildThrottleQuery The current query, for fluid interface
     */
    public function prune($throttle = null)
    {
        if ($throttle) {
            $this->addUsingAlias(ThrottleTableMap::COL_ID, $throttle->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the throttle table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ThrottleTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ThrottleTableMap::clearInstancePool();
            ThrottleTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ThrottleTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ThrottleTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            
            ThrottleTableMap::removeInstanceFromPool($criteria);
        
            $affectedRows += ModelCriteria::delete($con);
            ThrottleTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // ThrottleQuery
