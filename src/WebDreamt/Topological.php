<?php

namespace WebDreamt;

/**
 * Enables one to perform a topological sort.
 * Updates: http://blog.calcatraz.com/php-topological-sort-function-384
 * @license None - use it as you see fit.
 * @author Dan (http://www.calcatraz.com)
 */
class Topological {

	/**
	 * Do a topological sort.
	 * @param array $nodeids An array of node IDs
	 * @param array $edges An array of arrays (2-tuples) where the first value is a dependency of the
	 * second value and therefore the first one must come before the second one in the sort.
	 * @return array A sorted array of node IDs
	 * @throws Exception Thrown if there is a circular dependence.
	 */
	static function sort($nodeids, $edges) {

		// Initialize variables.
		$L = $S = $nodes = array();

		// Remove duplicate nodes.
		$nodeids = array_unique($nodeids);

		// Remove duplicate edges.
		$hashes = array();
		foreach ($edges as $k => $e) {
			$hash = md5(serialize($e));
			if (in_array($hash, $hashes)) {
				unset($edges[$k]);
			} else {
				$hashes[] = $hash;
			}
		}

		// Build a lookup table of each node's edges.
		foreach ($nodeids as $id) {
			$nodes[$id] = array('in' => array(), 'out' => array());
			foreach ($edges as $e) {
				if ($id == $e[0]) {
					$nodes[$id]['out'][] = $e[1];
				}
				if ($id == $e[1]) {
					$nodes[$id]['in'][] = $e[0];
				}
			}
		}

		// While we have nodes left, we pick a node with no inbound edges,
		// remove it and its edges from the graph, and add it to the end
		// of the sorted list.
		foreach ($nodes as $id => $n) {
			if (empty($n['in'])) {
				$S[] = $id;
			}
		}
		while (!empty($S)) {
			$L[] = $id = array_shift($S);
			foreach ($nodes[$id]['out'] as $m) {
				$nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
				if (empty($nodes[$m]['in'])) {
					$S[] = $m;
				}
			}
			$nodes[$id]['out'] = array();
		}

		// Check if we have any edges left unprocessed.
		foreach ($nodes as $n) {
			if (!empty($n['in']) or ! empty($n['out'])) {
				throw new Exception("There is a circular dependency.");
			}
		}
		return $L;
	}

}
