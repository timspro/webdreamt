<?php

namespace Base;

use \Contract as ChildContract;
use \ContractQuery as ChildContractQuery;
use \Exception;
use \PDO;
use Map\ContractTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'contract' table.
 *
 *
 *
 * @method     ChildContractQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildContractQuery orderByLocationId($order = Criteria::ASC) Order by the location_id column
 * @method     ChildContractQuery orderByBuyerCustomerId($order = Criteria::ASC) Order by the buyer_customer_id column
 * @method     ChildContractQuery orderBySellerCustomerId($order = Criteria::ASC) Order by the seller_customer_id column
 * @method     ChildContractQuery orderByBuyerAgentId($order = Criteria::ASC) Order by the buyer_agent_id column
 * @method     ChildContractQuery orderBySellerAgentId($order = Criteria::ASC) Order by the seller_agent_id column
 * @method     ChildContractQuery orderByCompletedTime($order = Criteria::ASC) Order by the completed_time column
 * @method     ChildContractQuery orderByCompletedDate($order = Criteria::ASC) Order by the completed_date column
 * @method     ChildContractQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method     ChildContractQuery orderByUpdatedAt($order = Criteria::ASC) Order by the updated_at column
 *
 * @method     ChildContractQuery groupById() Group by the id column
 * @method     ChildContractQuery groupByLocationId() Group by the location_id column
 * @method     ChildContractQuery groupByBuyerCustomerId() Group by the buyer_customer_id column
 * @method     ChildContractQuery groupBySellerCustomerId() Group by the seller_customer_id column
 * @method     ChildContractQuery groupByBuyerAgentId() Group by the buyer_agent_id column
 * @method     ChildContractQuery groupBySellerAgentId() Group by the seller_agent_id column
 * @method     ChildContractQuery groupByCompletedTime() Group by the completed_time column
 * @method     ChildContractQuery groupByCompletedDate() Group by the completed_date column
 * @method     ChildContractQuery groupByCreatedAt() Group by the created_at column
 * @method     ChildContractQuery groupByUpdatedAt() Group by the updated_at column
 *
 * @method     ChildContractQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildContractQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildContractQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildContractQuery leftJoinLocation($relationAlias = null) Adds a LEFT JOIN clause to the query using the Location relation
 * @method     ChildContractQuery rightJoinLocation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Location relation
 * @method     ChildContractQuery innerJoinLocation($relationAlias = null) Adds a INNER JOIN clause to the query using the Location relation
 *
 * @method     ChildContractQuery leftJoinCustomerRelatedByBuyerCustomerId($relationAlias = null) Adds a LEFT JOIN clause to the query using the CustomerRelatedByBuyerCustomerId relation
 * @method     ChildContractQuery rightJoinCustomerRelatedByBuyerCustomerId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CustomerRelatedByBuyerCustomerId relation
 * @method     ChildContractQuery innerJoinCustomerRelatedByBuyerCustomerId($relationAlias = null) Adds a INNER JOIN clause to the query using the CustomerRelatedByBuyerCustomerId relation
 *
 * @method     ChildContractQuery leftJoinCustomerRelatedBySellerCustomerId($relationAlias = null) Adds a LEFT JOIN clause to the query using the CustomerRelatedBySellerCustomerId relation
 * @method     ChildContractQuery rightJoinCustomerRelatedBySellerCustomerId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CustomerRelatedBySellerCustomerId relation
 * @method     ChildContractQuery innerJoinCustomerRelatedBySellerCustomerId($relationAlias = null) Adds a INNER JOIN clause to the query using the CustomerRelatedBySellerCustomerId relation
 *
 * @method     ChildContractQuery leftJoinAgentRelatedByBuyerAgentId($relationAlias = null) Adds a LEFT JOIN clause to the query using the AgentRelatedByBuyerAgentId relation
 * @method     ChildContractQuery rightJoinAgentRelatedByBuyerAgentId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AgentRelatedByBuyerAgentId relation
 * @method     ChildContractQuery innerJoinAgentRelatedByBuyerAgentId($relationAlias = null) Adds a INNER JOIN clause to the query using the AgentRelatedByBuyerAgentId relation
 *
 * @method     ChildContractQuery leftJoinAgentRelatedBySellerAgentId($relationAlias = null) Adds a LEFT JOIN clause to the query using the AgentRelatedBySellerAgentId relation
 * @method     ChildContractQuery rightJoinAgentRelatedBySellerAgentId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AgentRelatedBySellerAgentId relation
 * @method     ChildContractQuery innerJoinAgentRelatedBySellerAgentId($relationAlias = null) Adds a INNER JOIN clause to the query using the AgentRelatedBySellerAgentId relation
 *
 * @method     ChildContractQuery leftJoinMoveRelatedByBuyerContractId($relationAlias = null) Adds a LEFT JOIN clause to the query using the MoveRelatedByBuyerContractId relation
 * @method     ChildContractQuery rightJoinMoveRelatedByBuyerContractId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MoveRelatedByBuyerContractId relation
 * @method     ChildContractQuery innerJoinMoveRelatedByBuyerContractId($relationAlias = null) Adds a INNER JOIN clause to the query using the MoveRelatedByBuyerContractId relation
 *
 * @method     ChildContractQuery leftJoinMoveRelatedBySellerContractId($relationAlias = null) Adds a LEFT JOIN clause to the query using the MoveRelatedBySellerContractId relation
 * @method     ChildContractQuery rightJoinMoveRelatedBySellerContractId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MoveRelatedBySellerContractId relation
 * @method     ChildContractQuery innerJoinMoveRelatedBySellerContractId($relationAlias = null) Adds a INNER JOIN clause to the query using the MoveRelatedBySellerContractId relation
 *
 * @method     ChildContractQuery leftJoinServiceContract($relationAlias = null) Adds a LEFT JOIN clause to the query using the ServiceContract relation
 * @method     ChildContractQuery rightJoinServiceContract($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ServiceContract relation
 * @method     ChildContractQuery innerJoinServiceContract($relationAlias = null) Adds a INNER JOIN clause to the query using the ServiceContract relation
 *
 * @method     \LocationQuery|\CustomerQuery|\AgentQuery|\MoveQuery|\ServiceContractQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildContract findOne(ConnectionInterface $con = null) Return the first ChildContract matching the query
 * @method     ChildContract findOneOrCreate(ConnectionInterface $con = null) Return the first ChildContract matching the query, or a new ChildContract object populated from the query conditions when no match is found
 *
 * @method     ChildContract findOneById(int $id) Return the first ChildContract filtered by the id column
 * @method     ChildContract findOneByLocationId(int $location_id) Return the first ChildContract filtered by the location_id column
 * @method     ChildContract findOneByBuyerCustomerId(int $buyer_customer_id) Return the first ChildContract filtered by the buyer_customer_id column
 * @method     ChildContract findOneBySellerCustomerId(int $seller_customer_id) Return the first ChildContract filtered by the seller_customer_id column
 * @method     ChildContract findOneByBuyerAgentId(int $buyer_agent_id) Return the first ChildContract filtered by the buyer_agent_id column
 * @method     ChildContract findOneBySellerAgentId(int $seller_agent_id) Return the first ChildContract filtered by the seller_agent_id column
 * @method     ChildContract findOneByCompletedTime(string $completed_time) Return the first ChildContract filtered by the completed_time column
 * @method     ChildContract findOneByCompletedDate(string $completed_date) Return the first ChildContract filtered by the completed_date column
 * @method     ChildContract findOneByCreatedAt(string $created_at) Return the first ChildContract filtered by the created_at column
 * @method     ChildContract findOneByUpdatedAt(string $updated_at) Return the first ChildContract filtered by the updated_at column
 *
 * @method     ChildContract[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildContract objects based on current ModelCriteria
 * @method     ChildContract[]|ObjectCollection findById(int $id) Return ChildContract objects filtered by the id column
 * @method     ChildContract[]|ObjectCollection findByLocationId(int $location_id) Return ChildContract objects filtered by the location_id column
 * @method     ChildContract[]|ObjectCollection findByBuyerCustomerId(int $buyer_customer_id) Return ChildContract objects filtered by the buyer_customer_id column
 * @method     ChildContract[]|ObjectCollection findBySellerCustomerId(int $seller_customer_id) Return ChildContract objects filtered by the seller_customer_id column
 * @method     ChildContract[]|ObjectCollection findByBuyerAgentId(int $buyer_agent_id) Return ChildContract objects filtered by the buyer_agent_id column
 * @method     ChildContract[]|ObjectCollection findBySellerAgentId(int $seller_agent_id) Return ChildContract objects filtered by the seller_agent_id column
 * @method     ChildContract[]|ObjectCollection findByCompletedTime(string $completed_time) Return ChildContract objects filtered by the completed_time column
 * @method     ChildContract[]|ObjectCollection findByCompletedDate(string $completed_date) Return ChildContract objects filtered by the completed_date column
 * @method     ChildContract[]|ObjectCollection findByCreatedAt(string $created_at) Return ChildContract objects filtered by the created_at column
 * @method     ChildContract[]|ObjectCollection findByUpdatedAt(string $updated_at) Return ChildContract objects filtered by the updated_at column
 * @method     ChildContract[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class ContractQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \Base\ContractQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Contract', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildContractQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildContractQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildContractQuery) {
            return $criteria;
        }
        $query = new ChildContractQuery();
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
     * @return ChildContract|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ContractTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ContractTableMap::DATABASE_NAME);
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
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildContract A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, location_id, buyer_customer_id, seller_customer_id, buyer_agent_id, seller_agent_id, completed_time, completed_date, created_at, updated_at FROM contract WHERE id = :p0';
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
            /** @var ChildContract $obj */
            $obj = new ChildContract();
            $obj->hydrate($row);
            ContractTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildContract|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ContractTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ContractTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the location_id column
     *
     * Example usage:
     * <code>
     * $query->filterByLocationId(1234); // WHERE location_id = 1234
     * $query->filterByLocationId(array(12, 34)); // WHERE location_id IN (12, 34)
     * $query->filterByLocationId(array('min' => 12)); // WHERE location_id > 12
     * </code>
     *
     * @see       filterByLocation()
     *
     * @param     mixed $locationId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByLocationId($locationId = null, $comparison = null)
    {
        if (is_array($locationId)) {
            $useMinMax = false;
            if (isset($locationId['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_LOCATION_ID, $locationId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($locationId['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_LOCATION_ID, $locationId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_LOCATION_ID, $locationId, $comparison);
    }

    /**
     * Filter the query on the buyer_customer_id column
     *
     * Example usage:
     * <code>
     * $query->filterByBuyerCustomerId(1234); // WHERE buyer_customer_id = 1234
     * $query->filterByBuyerCustomerId(array(12, 34)); // WHERE buyer_customer_id IN (12, 34)
     * $query->filterByBuyerCustomerId(array('min' => 12)); // WHERE buyer_customer_id > 12
     * </code>
     *
     * @see       filterByCustomerRelatedByBuyerCustomerId()
     *
     * @param     mixed $buyerCustomerId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByBuyerCustomerId($buyerCustomerId = null, $comparison = null)
    {
        if (is_array($buyerCustomerId)) {
            $useMinMax = false;
            if (isset($buyerCustomerId['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_BUYER_CUSTOMER_ID, $buyerCustomerId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($buyerCustomerId['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_BUYER_CUSTOMER_ID, $buyerCustomerId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_BUYER_CUSTOMER_ID, $buyerCustomerId, $comparison);
    }

    /**
     * Filter the query on the seller_customer_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySellerCustomerId(1234); // WHERE seller_customer_id = 1234
     * $query->filterBySellerCustomerId(array(12, 34)); // WHERE seller_customer_id IN (12, 34)
     * $query->filterBySellerCustomerId(array('min' => 12)); // WHERE seller_customer_id > 12
     * </code>
     *
     * @see       filterByCustomerRelatedBySellerCustomerId()
     *
     * @param     mixed $sellerCustomerId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterBySellerCustomerId($sellerCustomerId = null, $comparison = null)
    {
        if (is_array($sellerCustomerId)) {
            $useMinMax = false;
            if (isset($sellerCustomerId['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_SELLER_CUSTOMER_ID, $sellerCustomerId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($sellerCustomerId['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_SELLER_CUSTOMER_ID, $sellerCustomerId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_SELLER_CUSTOMER_ID, $sellerCustomerId, $comparison);
    }

    /**
     * Filter the query on the buyer_agent_id column
     *
     * Example usage:
     * <code>
     * $query->filterByBuyerAgentId(1234); // WHERE buyer_agent_id = 1234
     * $query->filterByBuyerAgentId(array(12, 34)); // WHERE buyer_agent_id IN (12, 34)
     * $query->filterByBuyerAgentId(array('min' => 12)); // WHERE buyer_agent_id > 12
     * </code>
     *
     * @see       filterByAgentRelatedByBuyerAgentId()
     *
     * @param     mixed $buyerAgentId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByBuyerAgentId($buyerAgentId = null, $comparison = null)
    {
        if (is_array($buyerAgentId)) {
            $useMinMax = false;
            if (isset($buyerAgentId['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_BUYER_AGENT_ID, $buyerAgentId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($buyerAgentId['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_BUYER_AGENT_ID, $buyerAgentId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_BUYER_AGENT_ID, $buyerAgentId, $comparison);
    }

    /**
     * Filter the query on the seller_agent_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySellerAgentId(1234); // WHERE seller_agent_id = 1234
     * $query->filterBySellerAgentId(array(12, 34)); // WHERE seller_agent_id IN (12, 34)
     * $query->filterBySellerAgentId(array('min' => 12)); // WHERE seller_agent_id > 12
     * </code>
     *
     * @see       filterByAgentRelatedBySellerAgentId()
     *
     * @param     mixed $sellerAgentId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterBySellerAgentId($sellerAgentId = null, $comparison = null)
    {
        if (is_array($sellerAgentId)) {
            $useMinMax = false;
            if (isset($sellerAgentId['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_SELLER_AGENT_ID, $sellerAgentId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($sellerAgentId['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_SELLER_AGENT_ID, $sellerAgentId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_SELLER_AGENT_ID, $sellerAgentId, $comparison);
    }

    /**
     * Filter the query on the completed_time column
     *
     * Example usage:
     * <code>
     * $query->filterByCompletedTime('2011-03-14'); // WHERE completed_time = '2011-03-14'
     * $query->filterByCompletedTime('now'); // WHERE completed_time = '2011-03-14'
     * $query->filterByCompletedTime(array('max' => 'yesterday')); // WHERE completed_time > '2011-03-13'
     * </code>
     *
     * @param     mixed $completedTime The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByCompletedTime($completedTime = null, $comparison = null)
    {
        if (is_array($completedTime)) {
            $useMinMax = false;
            if (isset($completedTime['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_COMPLETED_TIME, $completedTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($completedTime['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_COMPLETED_TIME, $completedTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_COMPLETED_TIME, $completedTime, $comparison);
    }

    /**
     * Filter the query on the completed_date column
     *
     * Example usage:
     * <code>
     * $query->filterByCompletedDate('2011-03-14'); // WHERE completed_date = '2011-03-14'
     * $query->filterByCompletedDate('now'); // WHERE completed_date = '2011-03-14'
     * $query->filterByCompletedDate(array('max' => 'yesterday')); // WHERE completed_date > '2011-03-13'
     * </code>
     *
     * @param     mixed $completedDate The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByCompletedDate($completedDate = null, $comparison = null)
    {
        if (is_array($completedDate)) {
            $useMinMax = false;
            if (isset($completedDate['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_COMPLETED_DATE, $completedDate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($completedDate['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_COMPLETED_DATE, $completedDate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_COMPLETED_DATE, $completedDate, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE updated_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(ContractTableMap::COL_UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(ContractTableMap::COL_UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ContractTableMap::COL_UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query by a related \Location object
     *
     * @param \Location|ObjectCollection $location The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByLocation($location, $comparison = null)
    {
        if ($location instanceof \Location) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_LOCATION_ID, $location->getId(), $comparison);
        } elseif ($location instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ContractTableMap::COL_LOCATION_ID, $location->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByLocation() only accepts arguments of type \Location or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Location relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinLocation($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Location');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Location');
        }

        return $this;
    }

    /**
     * Use the Location relation Location object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \LocationQuery A secondary query class using the current class as primary query
     */
    public function useLocationQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinLocation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Location', '\LocationQuery');
    }

    /**
     * Filter the query by a related \Customer object
     *
     * @param \Customer|ObjectCollection $customer The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByCustomerRelatedByBuyerCustomerId($customer, $comparison = null)
    {
        if ($customer instanceof \Customer) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_BUYER_CUSTOMER_ID, $customer->getId(), $comparison);
        } elseif ($customer instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ContractTableMap::COL_BUYER_CUSTOMER_ID, $customer->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByCustomerRelatedByBuyerCustomerId() only accepts arguments of type \Customer or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CustomerRelatedByBuyerCustomerId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinCustomerRelatedByBuyerCustomerId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CustomerRelatedByBuyerCustomerId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'CustomerRelatedByBuyerCustomerId');
        }

        return $this;
    }

    /**
     * Use the CustomerRelatedByBuyerCustomerId relation Customer object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \CustomerQuery A secondary query class using the current class as primary query
     */
    public function useCustomerRelatedByBuyerCustomerIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCustomerRelatedByBuyerCustomerId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CustomerRelatedByBuyerCustomerId', '\CustomerQuery');
    }

    /**
     * Filter the query by a related \Customer object
     *
     * @param \Customer|ObjectCollection $customer The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByCustomerRelatedBySellerCustomerId($customer, $comparison = null)
    {
        if ($customer instanceof \Customer) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_SELLER_CUSTOMER_ID, $customer->getId(), $comparison);
        } elseif ($customer instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ContractTableMap::COL_SELLER_CUSTOMER_ID, $customer->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByCustomerRelatedBySellerCustomerId() only accepts arguments of type \Customer or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CustomerRelatedBySellerCustomerId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinCustomerRelatedBySellerCustomerId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CustomerRelatedBySellerCustomerId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'CustomerRelatedBySellerCustomerId');
        }

        return $this;
    }

    /**
     * Use the CustomerRelatedBySellerCustomerId relation Customer object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \CustomerQuery A secondary query class using the current class as primary query
     */
    public function useCustomerRelatedBySellerCustomerIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinCustomerRelatedBySellerCustomerId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CustomerRelatedBySellerCustomerId', '\CustomerQuery');
    }

    /**
     * Filter the query by a related \Agent object
     *
     * @param \Agent|ObjectCollection $agent The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByAgentRelatedByBuyerAgentId($agent, $comparison = null)
    {
        if ($agent instanceof \Agent) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_BUYER_AGENT_ID, $agent->getId(), $comparison);
        } elseif ($agent instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ContractTableMap::COL_BUYER_AGENT_ID, $agent->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByAgentRelatedByBuyerAgentId() only accepts arguments of type \Agent or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AgentRelatedByBuyerAgentId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinAgentRelatedByBuyerAgentId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AgentRelatedByBuyerAgentId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AgentRelatedByBuyerAgentId');
        }

        return $this;
    }

    /**
     * Use the AgentRelatedByBuyerAgentId relation Agent object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \AgentQuery A secondary query class using the current class as primary query
     */
    public function useAgentRelatedByBuyerAgentIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAgentRelatedByBuyerAgentId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AgentRelatedByBuyerAgentId', '\AgentQuery');
    }

    /**
     * Filter the query by a related \Agent object
     *
     * @param \Agent|ObjectCollection $agent The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByAgentRelatedBySellerAgentId($agent, $comparison = null)
    {
        if ($agent instanceof \Agent) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_SELLER_AGENT_ID, $agent->getId(), $comparison);
        } elseif ($agent instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ContractTableMap::COL_SELLER_AGENT_ID, $agent->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByAgentRelatedBySellerAgentId() only accepts arguments of type \Agent or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AgentRelatedBySellerAgentId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinAgentRelatedBySellerAgentId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AgentRelatedBySellerAgentId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AgentRelatedBySellerAgentId');
        }

        return $this;
    }

    /**
     * Use the AgentRelatedBySellerAgentId relation Agent object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \AgentQuery A secondary query class using the current class as primary query
     */
    public function useAgentRelatedBySellerAgentIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAgentRelatedBySellerAgentId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AgentRelatedBySellerAgentId', '\AgentQuery');
    }

    /**
     * Filter the query by a related \Move object
     *
     * @param \Move|ObjectCollection $move  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByMoveRelatedByBuyerContractId($move, $comparison = null)
    {
        if ($move instanceof \Move) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_ID, $move->getBuyerContractId(), $comparison);
        } elseif ($move instanceof ObjectCollection) {
            return $this
                ->useMoveRelatedByBuyerContractIdQuery()
                ->filterByPrimaryKeys($move->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMoveRelatedByBuyerContractId() only accepts arguments of type \Move or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MoveRelatedByBuyerContractId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinMoveRelatedByBuyerContractId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MoveRelatedByBuyerContractId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MoveRelatedByBuyerContractId');
        }

        return $this;
    }

    /**
     * Use the MoveRelatedByBuyerContractId relation Move object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \MoveQuery A secondary query class using the current class as primary query
     */
    public function useMoveRelatedByBuyerContractIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinMoveRelatedByBuyerContractId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MoveRelatedByBuyerContractId', '\MoveQuery');
    }

    /**
     * Filter the query by a related \Move object
     *
     * @param \Move|ObjectCollection $move  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByMoveRelatedBySellerContractId($move, $comparison = null)
    {
        if ($move instanceof \Move) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_ID, $move->getSellerContractId(), $comparison);
        } elseif ($move instanceof ObjectCollection) {
            return $this
                ->useMoveRelatedBySellerContractIdQuery()
                ->filterByPrimaryKeys($move->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMoveRelatedBySellerContractId() only accepts arguments of type \Move or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MoveRelatedBySellerContractId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinMoveRelatedBySellerContractId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MoveRelatedBySellerContractId');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MoveRelatedBySellerContractId');
        }

        return $this;
    }

    /**
     * Use the MoveRelatedBySellerContractId relation Move object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \MoveQuery A secondary query class using the current class as primary query
     */
    public function useMoveRelatedBySellerContractIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinMoveRelatedBySellerContractId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MoveRelatedBySellerContractId', '\MoveQuery');
    }

    /**
     * Filter the query by a related \ServiceContract object
     *
     * @param \ServiceContract|ObjectCollection $serviceContract  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByServiceContract($serviceContract, $comparison = null)
    {
        if ($serviceContract instanceof \ServiceContract) {
            return $this
                ->addUsingAlias(ContractTableMap::COL_ID, $serviceContract->getContractId(), $comparison);
        } elseif ($serviceContract instanceof ObjectCollection) {
            return $this
                ->useServiceContractQuery()
                ->filterByPrimaryKeys($serviceContract->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByServiceContract() only accepts arguments of type \ServiceContract or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ServiceContract relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function joinServiceContract($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ServiceContract');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ServiceContract');
        }

        return $this;
    }

    /**
     * Use the ServiceContract relation ServiceContract object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \ServiceContractQuery A secondary query class using the current class as primary query
     */
    public function useServiceContractQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinServiceContract($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ServiceContract', '\ServiceContractQuery');
    }

    /**
     * Filter the query by a related Service object
     * using the service_contract table as cross reference
     *
     * @param Service $service the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildContractQuery The current query, for fluid interface
     */
    public function filterByService($service, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useServiceContractQuery()
            ->filterByService($service, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildContract $contract Object to remove from the list of results
     *
     * @return $this|ChildContractQuery The current query, for fluid interface
     */
    public function prune($contract = null)
    {
        if ($contract) {
            $this->addUsingAlias(ContractTableMap::COL_ID, $contract->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the contract table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ContractTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ContractTableMap::clearInstancePool();
            ContractTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(ContractTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ContractTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ContractTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ContractTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // ContractQuery
