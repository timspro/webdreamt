<?php

namespace WebDreamt\X;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Propel;
use WebDreamt\Cache\Resource;
use WebDreamt\Hyper\Columned;
use WebDreamt\Hyper\Form;
use WebDreamt\Hyper\Group;
use WebDreamt\Hyper\Select;
use WebDreamt\Hyper\Table;

class Cache {

	protected $cache;

	/**
	 * Constructs the Cache.
	 * @param Box $box
	 */
	function __construct(Box $box) {
		$this->cache = $box->VendorDirectory . '/../cache/';
	}

	/**
	 * Gets a form.
	 * @param string $filename Usually __FILE__
	 * @param string $key Usually __LINE__
	 * @param string $tableName The table to use to make the form.
	 * @param array|ActiveRecordInterface|ActiveRecordInterface[] $values
	 * @return Form
	 */
	function form($filename, $key, $tableName, $values = []) {
		return $this->find(__FUNCTION__, $filename, $key, $tableName, $values);
	}

	/**
	 * Gets a group.
	 * @param string $filename Usually __FILE__
	 * @param string $key Usually __LINE__
	 * @param string $tableName The table to use to make the form.
	 * @param array|ActiveRecordInterface|ActiveRecordInterface[] $values
	 * @return Group
	 */
	function group($filename, $key, $tableName, $values = []) {
		return $this->find(__FUNCTION__, $filename, $key, $tableName, $values);
	}

	/**
	 * Gets a table.
	 * @param string $filename Usually __FILE__
	 * @param string $key Usually __LINE__
	 * @param string $tableName The table to use to make the form.
	 * @param array|ActiveRecordInterface|ActiveRecordInterface[] $values
	 * @return Table
	 */
	function table($filename, $key, $tableName, $values = []) {
		return $this->find(__FUNCTION__, $filename, $key, $tableName, $values);
	}

	/**
	 * Gets a select.
	 * @param string $filename Usually __FILE__
	 * @param string $key Usually __LINE__
	 * @param string $tableName The table to use to make the form.
	 * @param array|ActiveRecordInterface|ActiveRecordInterface[] $values
	 * @return Select
	 */
	function select($filename, $key, $tableName, $values = []) {
		return $this->find(__FUNCTION__, $filename, $key, $tableName, $values);
	}

	/**
	 * Using the parameters provided, tries to find the component in the cache.
	 * @param string $type
	 * @param string $filename
	 * @param string $key
	 * @param string $tableName
	 * @param array $values
	 * @return Cache
	 */
	protected function find($type, $filename, $key, $tableName, $values = []) {
		$type = ucwords($type);
		$hash = md5($filename);
		$cachedFilename = $this->cache . "$type-$key-$hash-$tableName.php";
		$check = filemtime($cachedFilename);
		if ($check && filemtime($filename) > $cachedFilename) {
			return new Resource($cachedFilename, $values);
		} else {
			$tableMap = Propel::getDatabaseMap()->getTable($tableName);
			$component = new $type($tableMap, $values);
			$component->cachedFilename = $cachedFilename;
			return $component;
		}
	}

	/**
	 * Caches the component and returns the rendered template as a string.
	 * @param Columned $component
	 * @return string
	 */
	static function add(Data $component) {
		$template = $component->getTemplate();
		$tokens = token_get_all($template);
		$tokens = self::portTags($tokens);
		$template = self::tokensToCode($tokens);
		file_put_contents($component->cachedFilename, $template);
		$resource = new Resource($component->cachedFilename, $component->getValues());
		return $resource->__toString();
	}

	/**
	 * Used to fix ASP tags. From https://gist.github.com/nikic/74769d74dad8b9ef221b
	 * @param array $tokens
	 * @return array
	 */
	protected static function portTags(array $tokens) {
		foreach ($tokens as $i => &$token) {
			if ($token[0] === T_OPEN_TAG) {
				if (strpos($token[1], '<?') === 0) {
					continue;
				}

				$token[1] = '<?php';
				if (!isset($tokens[$i]) || $tokens[$i][0] !== T_WHITESPACE) {
					$token[1] .= ' ';
				}
			} else if ($token[0] === T_OPEN_TAG_WITH_ECHO) {
				if ($token[1] === '<?=') {
					continue;
				}

				$token[1] = '<?=';
			} else if ($token[0] === T_CLOSE_TAG) {
				if (strpos($token[1], '?>') === 0) {
					continue;
				}

				if (preg_match('~^(?:%>|</script\s*>)(\s*)$~', $token[1], $matches)) {
					$token[1] = '?>' . $matches[1];
				}
			}
		}

		return $tokens;
	}

	/**
	 * Used to fix ASP tags. From https://gist.github.com/nikic/74769d74dad8b9ef221b
	 * @param array $tokens
	 * @return array
	 */
	protected static function tokensToCode(array $tokens) {
		$code = '';
		foreach ($tokens as $token) {
			if (is_array($token)) {
				$code .= $token[1];
			} else {
				$code .= $token;
			}
		}
		return $code;
	}

}
