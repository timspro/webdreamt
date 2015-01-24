<?php

namespace Base;

use \Move as ChildMove;
use \MoveQuery as ChildMoveQuery;
use \Exception;
use \PDO;
use Map\MoveTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'move' table.
 *
 *
 *
 * @method     ChildMoveQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildMoveQuery orderByBuyerContractId($order = Criteria::ASC) Order by the buyer_contract_id column
 * @method     ChildMoveQuery orderBySellerContractId($order = Criteria::ASC) Order by the seller_contract_id column
 *
 * @method     ChildMoveQuery groupById() Group by the id column
 * @method     ChildMoveQuery groupByBuyerContractId() Group by the buyer_contract_id column
 * @method     ChildMoveQuery groupBySellerContractId() Group by the seller_contract_id column
 *
 * @method     ChildMoveQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildMoveQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildMoveQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildMoveQuery leftJoinContractRelatedByBuyerContractId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ContractRelatedByBuyerContractId relation
 * @method     ChildMoveQuery rightJoinContractRelatedByBuyerContractId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ContractRelatedByBuyerContractId relation
 * @method     ChildMoveQuery innerJoinContractRelatedByBuyerContractId($relationAlias = null) Adds a INNER JOIN clause to the query using the ContractRelatedByBuyerContractId relation
 *
 * @method     ChildMoveQuery leftJoinContractRelatedBySellerContractId($relationAlias = null) Adds a LEFT JOIN clause to the query using the ContractRelatedBySellerContractId relation
 * @method     ChildMoveQuery rightJoinContractRelatedBySellerContractId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ContractRelatedBySellerContractId relation
 * @method     ChildMoveQuery innerJoinContractRelatedBySellerContractId($relationAlias = null) Adds a INNER JOIN clause to the query using the ContractRelatedBySellerContractId relation
 *
 * @method     \ContractQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildMove findOne(ConnectionInterface $con = null) Return the first ChildMove matching the query
 * @method     ChildMove findOneOrCreate(ConnectionInterface $con = null) Return the first ChildMove matching the query, or a new ChildMove object populated from the query conditions when no match is found
 *
 * @method     ChildMove findOneById(int $id) Return the first ChildMove filtered by the id column
 * @method     ChildMove findOneByBuyerContractId(int $buyer_contract_id) Return the first ChildMove filtered by the buyer_contract_id column
 * @method     ChildMove findOneBySellerContractId(int $seller_contract_id) Return the first ChildMove filtered by the seller_contract_id column
 *
 * @method     ChildMove[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildMove objects based on current ModelCriteria
 * @method     ChildMove[]|ObjectCollection findById(int $id) Return ChildMove objects filtered by the id column
 * @method     ChildMove[]|ObjectCollection findByBuyerContractId(int $buyer_contract_id) Return ChildMove objects filtered by the buyer_contract_id column
 * @method     ChildMove[]|ObjectCollection findBySellerContractId(int $seller_contract_id) Return ChildMove objects filtered by the seller_contract_id column
 * @method     ChildMove[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class MoveQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \Base\MoveQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\Move', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildMoveQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildMoveQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildMoveQuery) {
            return $criteria;
        }
        $query = new ChildMoveQuery();
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
     * @return ChildMove|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = MoveTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(MoveTableMap::DATABASE_NAME);
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
     * @return ChildMove A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, buyer_contract_id, seller_contract_id FROM move WHERE id = :p0';
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
            /** @var ChildMove $obj */
            $obj = new ChildMove();
            $obj->hydrate($row);
            MoveTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildMove|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MoveTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MoveTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(MoveTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(MoveTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MoveTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the buyer_contract_id column
     *
     * Example usage:
     * <code>
     * $query->filterByBuyerContractId(1234); // WHERE buyer_contract_id = 1234
     * $query->filterByBuyerContractId(array(12, 34)); // WHERE buyer_contract_id IN (12, 34)
     * $query->filterByBuyerContractId(array('min' => 12)); // WHERE buyer_contract_id > 12
     * </code>
     *
     * @see       filterByContractRelatedByBuyerContractId()
     *
     * @param     mixed $buyerContractId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function filterByBuyerContractId($buyerContractId = null, $comparison = null)
    {
        if (is_array($buyerContractId)) {
            $useMinMax = false;
            if (isset($buyerContractId['min'])) {
                $this->addUsingAlias(MoveTableMap::COL_BUYER_CONTRACT_ID, $buyerContractId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($buyerContractId['max'])) {
                $this->addUsingAlias(MoveTableMap::COL_BUYER_CONTRACT_ID, $buyerContractId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MoveTableMap::COL_BUYER_CONTRACT_ID, $buyerContractId, $comparison);
    }

    /**
     * Filter the query on the seller_contract_id column
     *
     * Example usage:
     * <code>
     * $query->filterBySellerContractId(1234); // WHERE seller_contract_id = 1234
     * $query->filterBySellerContractId(array(12, 34)); // WHERE seller_contract_id IN (12, 34)
     * $query->filterBySellerContractId(array('min' => 12)); // WHERE seller_contract_id > 12
     * </code>
     *
     * @see       filterByContractRelatedBySellerContractId()
     *
     * @param     mixed $sellerContractId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function filterBySellerContractId($sellerContractId = null, $comparison = null)
    {
        if (is_array($sellerContractId)) {
            $useMinMax = false;
            if (isset($sellerContractId['min'])) {
                $this->addUsingAlias(MoveTableMap::COL_SELLER_CONTRACT_ID, $sellerContractId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($sellerContractId['max'])) {
                $this->addUsingAlias(MoveTableMap::COL_SELLER_CONTRACT_ID, $sellerContractId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MoveTableMap::COL_SELLER_CONTRACT_ID, $sellerContractId, $comparison);
    }

    /**
     * Filter the query by a related \Contract object
     *
     * @param \Contract|ObjectCollection $contract The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildMoveQuery The current query, for fluid interface
     */
    public function filterByContractRelatedByBuyerContractId($contract, $comparison = null)
    {
        if ($contract instanceof \Contract) {
            return $this
                ->addUsingAlias(MoveTableMap::COL_BUYER_CONTRACT_ID, $contract->getId(), $comparison);
        } elseif ($contract instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MoveTableMap::COL_BUYER_CONTRACT_ID, $contract->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByContractRelatedByBuyerContractId() only accepts arguments of type \Contract or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ContractRelatedByBuyerContractId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function joinContractRelatedByBuyerContractId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ContractRelatedByBuyerContractId');

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
            $this->addJoinObject($join, 'ContractRelatedByBuyerContractId');
        }

        return $this;
    }

    /**
     * Use the ContractRelatedByBuyerContractId relation Contract object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \ContractQuery A secondary query class using the current class as primary query
     */
    public function useContractRelatedByBuyerContractIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinContractRelatedByBuyerContractId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ContractRelatedByBuyerContractId', '\ContractQuery');
    }

    /**
     * Filter the query by a related \Contract object
     *
     * @param \Contract|ObjectCollection $contract The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildMoveQuery The current query, for fluid interface
     */
    public function filterByContractRelatedBySellerContractId($contract, $comparison = null)
    {
        if ($contract instanceof \Contract) {
            return $this
                ->addUsingAlias(MoveTableMap::COL_SELLER_CONTRACT_ID, $contract->getId(), $comparison);
        } elseif ($contract instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MoveTableMap::COL_SELLER_CONTRACT_ID, $contract->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByContractRelatedBySellerContractId() only accepts arguments of type \Contract or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ContractRelatedBySellerContractId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function joinContractRelatedBySellerContractId($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ContractRelatedBySellerContractId');

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
            $this->addJoinObject($join, 'ContractRelatedBySellerContractId');
        }

        return $this;
    }

    /**
     * Use the ContractRelatedBySellerContractId relation Contract object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \ContractQuery A secondary query class using the current class as primary query
     */
    public function useContractRelatedBySellerContractIdQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinContractRelatedBySellerContractId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ContractRelatedBySellerContractId', '\ContractQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildMove $move Object to remove from the list of results
     *
     * @return $this|ChildMoveQuery The current query, for fluid interface
     */
    public function prune($move = null)
    {
        if ($move) {
            $this->addUsingAlias(MoveTableMap::COL_ID, $move->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the move table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(MoveTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            MoveTableMap::clearInstancePool();
            MoveTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(MoveTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(MoveTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            MoveTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            MoveTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // MoveQuery
