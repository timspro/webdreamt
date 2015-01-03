<?php

namespace Base;

use \Agent as ChildAgent;
use \AgentQuery as ChildAgentQuery;
use \Exception;
use \PDO;
use Map\AgentTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'agent' table.
 *
 *
 *
 * @method     ChildAgentQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildAgentQuery orderByFirstName($order = Criteria::ASC) Order by the first_name column
 * @method     ChildAgentQuery orderByLastName($order = Criteria::ASC) Order by the last_name column
 * @method     ChildAgentQuery orderByAgency($order = Criteria::ASC) Order by the agency column
 * @method     ChildAgentQuery orderBySalary($order = Criteria::ASC) Order by the salary column
 *
 * @method     ChildAgentQuery groupById() Group by the id column
 * @method     ChildAgentQuery groupByFirstName() Group by the first_name column
 * @method     ChildAgentQuery groupByLastName() Group by the last_name column
 * @method     ChildAgentQuery groupByAgency() Group by the agency column
 * @method     ChildAgentQuery groupBySalary() Group by the salary column
 *
 * @method     ChildAgentQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildAgentQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildAgentQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildAgentQuery leftJoinContractRelatedByBuyerAgentId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ContractRelatedByBuyerAgentId relation
 * @method     ChildAgentQuery rightJoinContractRelatedByBuyerAgentId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ContractRelatedByBuyerAgentId relation
 * @method     ChildAgentQuery innerJoinContractRelatedByBuyerAgentId($relationAlias = null) Adds a INNER JOIN clause to the query using the ContractRelatedByBuyerAgentId relation
 *
 * @method     ChildAgentQuery leftJoinContractRelatedBySellerAgentId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ContractRelatedBySellerAgentId relation
 * @method     ChildAgentQuery rightJoinContractRelatedBySellerAgentId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ContractRelatedBySellerAgentId relation
 * @method     ChildAgentQuery innerJoinContractRelatedBySellerAgentId($relationAlias = null) Adds a INNER JOIN clause to the query using the ContractRelatedBySellerAgentId relation
 *
 * @method     \ContractQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildAgent findOne(ConnectionInterface $con = null) Return the first ChildAgent matching the query
 * @method     ChildAgent findOneOrCreate(ConnectionInterface $con = null) Return the first ChildAgent matching the query, or a new ChildAgent object populated from the query conditions when no match is found
 *
 * @method     ChildAgent findOneById(int $id) Return the first ChildAgent filtered by the id column
 * @method     ChildAgent findOneByFirstName(string $first_name) Return the first ChildAgent filtered by the first_name column
 * @method     ChildAgent findOneByLastName(string $last_name) Return the first ChildAgent filtered by the last_name column
 * @method     ChildAgent findOneByAgency(string $agency) Return the first ChildAgent filtered by the agency column
 * @method     ChildAgent findOneBySalary(string $salary) Return the first ChildAgent filtered by the salary column
 *
 * @method     ChildAgent[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildAgent objects based on current ModelCriteria
 * @method     ChildAgent[]|ObjectCollection findById(int $id) Return ChildAgent objects filtered by the id column
 * @method     ChildAgent[]|ObjectCollection findByFirstName(string $first_name) Return ChildAgent objects filtered by the first_name column
 * @method     ChildAgent[]|ObjectCollection findByLastName(string $last_name) Return ChildAgent objects filtered by the last_name column
 * @method     ChildAgent[]|ObjectCollection findByAgency(string $agency) Return ChildAgent objects filtered by the agency column
 * @method     ChildAgent[]|ObjectCollection findBySalary(string $salary) Return ChildAgent objects filtered by the salary column
 * @method     ChildAgent[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class AgentQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \Base\AgentQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Agent', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildAgentQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildAgentQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildAgentQuery) {
            return $criteria;
        }
        $query = new ChildAgentQuery();
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
     * @return ChildAgent|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = AgentTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(AgentTableMap::DATABASE_NAME);
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
     * @return ChildAgent A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, first_name, last_name, agency, salary FROM agent WHERE id = :p0';
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
            /** @var ChildAgent $obj */
            $obj = new ChildAgent();
            $obj->hydrate($row);
            AgentTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildAgent|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(AgentTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(AgentTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(AgentTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(AgentTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AgentTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the first_name column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstName('fooValue');   // WHERE first_name = 'fooValue'
     * $query->filterByFirstName('%fooValue%'); // WHERE first_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $firstName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterByFirstName($firstName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $firstName)) {
                $firstName = str_replace('*', '%', $firstName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AgentTableMap::COL_FIRST_NAME, $firstName, $comparison);
    }

    /**
     * Filter the query on the last_name column
     *
     * Example usage:
     * <code>
     * $query->filterByLastName('fooValue');   // WHERE last_name = 'fooValue'
     * $query->filterByLastName('%fooValue%'); // WHERE last_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterByLastName($lastName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $lastName)) {
                $lastName = str_replace('*', '%', $lastName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AgentTableMap::COL_LAST_NAME, $lastName, $comparison);
    }

    /**
     * Filter the query on the agency column
     *
     * Example usage:
     * <code>
     * $query->filterByAgency('fooValue');   // WHERE agency = 'fooValue'
     * $query->filterByAgency('%fooValue%'); // WHERE agency LIKE '%fooValue%'
     * </code>
     *
     * @param     string $agency The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterByAgency($agency = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($agency)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $agency)) {
                $agency = str_replace('*', '%', $agency);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AgentTableMap::COL_AGENCY, $agency, $comparison);
    }

    /**
     * Filter the query on the salary column
     *
     * Example usage:
     * <code>
     * $query->filterBySalary(1234); // WHERE salary = 1234
     * $query->filterBySalary(array(12, 34)); // WHERE salary IN (12, 34)
     * $query->filterBySalary(array('min' => 12)); // WHERE salary > 12
     * </code>
     *
     * @param     mixed $salary The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function filterBySalary($salary = null, $comparison = null)
    {
        if (is_array($salary)) {
            $useMinMax = false;
            if (isset($salary['min'])) {
                $this->addUsingAlias(AgentTableMap::COL_SALARY, $salary['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($salary['max'])) {
                $this->addUsingAlias(AgentTableMap::COL_SALARY, $salary['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AgentTableMap::COL_SALARY, $salary, $comparison);
    }

    /**
     * Filter the query by a related \Contract object
     *
     * @param \Contract|ObjectCollection $contract  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildAgentQuery The current query, for fluid interface
     */
    public function filterByContractRelatedByBuyerAgentId($contract, $comparison = null)
    {
        if ($contract instanceof \Contract) {
            return $this
                ->addUsingAlias(AgentTableMap::COL_ID, $contract->getBuyerAgentId(), $comparison);
        } elseif ($contract instanceof ObjectCollection) {
            return $this
                ->useContractRelatedByBuyerAgentIdQuery()
                ->filterByPrimaryKeys($contract->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByContractRelatedByBuyerAgentId() only accepts arguments of type \Contract or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ContractRelatedByBuyerAgentId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function joinContractRelatedByBuyerAgentId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ContractRelatedByBuyerAgentId');

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
            $this->addJoinObject($join, 'ContractRelatedByBuyerAgentId');
        }

        return $this;
    }

    /**
     * Use the ContractRelatedByBuyerAgentId relation Contract object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \ContractQuery A secondary query class using the current class as primary query
     */
    public function useContractRelatedByBuyerAgentIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinContractRelatedByBuyerAgentId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ContractRelatedByBuyerAgentId', '\ContractQuery');
    }

    /**
     * Filter the query by a related \Contract object
     *
     * @param \Contract|ObjectCollection $contract  the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildAgentQuery The current query, for fluid interface
     */
    public function filterByContractRelatedBySellerAgentId($contract, $comparison = null)
    {
        if ($contract instanceof \Contract) {
            return $this
                ->addUsingAlias(AgentTableMap::COL_ID, $contract->getSellerAgentId(), $comparison);
        } elseif ($contract instanceof ObjectCollection) {
            return $this
                ->useContractRelatedBySellerAgentIdQuery()
                ->filterByPrimaryKeys($contract->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByContractRelatedBySellerAgentId() only accepts arguments of type \Contract or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ContractRelatedBySellerAgentId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function joinContractRelatedBySellerAgentId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ContractRelatedBySellerAgentId');

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
            $this->addJoinObject($join, 'ContractRelatedBySellerAgentId');
        }

        return $this;
    }

    /**
     * Use the ContractRelatedBySellerAgentId relation Contract object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \ContractQuery A secondary query class using the current class as primary query
     */
    public function useContractRelatedBySellerAgentIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinContractRelatedBySellerAgentId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ContractRelatedBySellerAgentId', '\ContractQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildAgent $agent Object to remove from the list of results
     *
     * @return $this|ChildAgentQuery The current query, for fluid interface
     */
    public function prune($agent = null)
    {
        if ($agent) {
            $this->addUsingAlias(AgentTableMap::COL_ID, $agent->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the agent table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(AgentTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            AgentTableMap::clearInstancePool();
            AgentTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(AgentTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(AgentTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            AgentTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            AgentTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // AgentQuery
