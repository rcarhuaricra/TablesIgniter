<?php namespace ricv;

/**
 * TablesIgniterRicv
 *
 * TablesIgniterRicv based on CodeIgniter4. This  library will help you use jQuery Datatables in server side mode.
 * @package    CodeIgniter4
 * @subpackage libraries
 * @category   library
 * @version    1.4.1
 * @author    rcarhuaricra <ronald@grupocarhua.com>
 * @link      https://github.com/rcarhuaricra/TablesIgniter
 */

use \CodeIgniter\Database\BaseBuilder;

class TablesIgniterRicv{
	
	protected $builder;
	protected $outputColumn;
	protected $defaultOrder = [];
	protected $searchLike = [];
	protected $searchFiltros = [];
	protected $order = [];
	protected $dataTables;

	public function __construct(array $init = [])
	{

		if (!empty($init)) {
			if (isset($init["setTable"])) {
				$this->setTable($init["setTable"]);
			}

			if (isset($init["setOutput"])) {
				$this->setOutput($init["setOutput"]);
			}

			if (isset($init["setDefaultOrder"])) {
				foreach ($init["setDefaultOrder"] as $value) {
					$this->setDefaultOrder($value[0], $value[1]);
				}
			}else{
				ver($request->getPost());exit;
			}
			if (isset($init["setSearch"])) {
				$this->setSearch($init["setSearch"]);
			}

			if (isset($init["setFilter"])) {
				$this->setFilter($init["setFilter"]);
			}

			if (isset($init["setOrder"])) {
				$this->setOrder($init["setOrder"]);
			}

		}
		$request = \Config\Services::request();
		if ($request->getPost("draw")) {
			$this->dataTables = $request->getPost();
		} else if ($request->getGet("draw")) {
			$this->dataTables = $request->getGet();
		} else {
			$this->dataTables = $request->getPost();
			// throw new \Exception("Must be requested by jQuery DataTables.", 1);
		}
	}

	/**
	 * Pass the BaseBuilder object.
	 *
	 * @param \CodeIgniter\Database\BaseBuilder $builder
	 * @return \monken\TablesIgniter
	 */
	public function setTable(BaseBuilder $builder)
	{
		$this->builder = clone $builder;
		return $this;
	}

	/**
	 * Set the Database field to participate in the search.
	 *
	 * @param array $like An array of strings.
	 * @return \monken\TablesIgniter
	 */
	public function setSearch(array $like)
	{
		$this->searchLike = $like;
		return $this;
	}

	public function setFilter($datos){
		$this->searchFiltros = $datos;
		return $this;

	}

	/**
	 * Set the gk4u field to participate in the order.Must be the same arrangement as the "setOutput" field.
	 *
	 * @param array $order An array of strings.
	 * @return \monken\TablesIgniter
	 */
	public function setOrder(array $order)
	{
		$this->order = $order;
		return $this;
	}

	/**
	 * Set the field to be sorted and the sorting method by default.
	 *
	 * @param string $item field name
	 * @param string $type sorting method
	 * @return \monken\TablesIgniter
	 */
	public function setDefaultOrder(string $item, string $type = "ASC")
	{
		$this->defaultOrder[] = array($item, $type);
		return $this;
	}

	/**
	 * Set the actual output sequence. A string array composed of field names.
	 *
	 * @param array $column An array of strings.
	 * @return \monken\TablesIgniter
	 */
	public function setOutput(array $column)
	{
		$this->outputColumn = $column;
		return $this;
	}

	/**
	 * Get a clone of the builder object.
	 *
	 * @return \CodeIgniter\Database\BaseBuilder
	 */
	private function getBuilder()
	{
		return clone $this->builder;
	}

	/**
	 * Get the number of searches.
	 *
	 * @return int
	 */
	private function getFiltered()
	{
		$bui = $this->extraConfig($this->getBuilder());
		$query = $bui->countAllResults();
		return $query;
	}

	/**
	 * Get the total number of data.
	 *
	 * @return void
	 */
	private function getTotal()
	{
		$bui = $this->getBuilder();
		$query = $bui->countAllResults();
		return $query;
	}

	/**
	 * Execute query
	 *
	 * @return \CodeIgniter\Database\ResultInterface
	 */
	private function getQuery()
	{
		$bui = $this->extraConfig($this->getBuilder());
		if (isset($this->dataTables["length"])) {
			if ($this->dataTables["length"] != -1) {
				$bui->limit($this->dataTables['length'], $this->dataTables['start']);
			}
		}
		$query = $bui->get();
		return $query;
	}

	/**
	 * Synthesize the content of each row of data.
	 *
	 * @param array $row
	 * @return array
	 */
	private function getOutputData($row, $name = false)
	{
		$subArray = array();
		foreach ($this->outputColumn as $colKey => $data) {
			if($name == 'name'){
				
				if (gettype($data) != "string") {
					$key = array_keys($data($row))[0];
					$subArray[$key] = $data($row)[$key];
				}else{
					$subArray[$data] = $row->$data;
				}
			}else{
				if (gettype($data) != "string") {
					$subArray[] = $data($row);
				} else {
					$subArray[] = $row[$data];
				}
			}
		}
		return $subArray;
	}

	/**
	 * Determine whether other sorting or searching is needed.
	 *
	 * @param \CodeIgniter\Database\BaseBuilder $bui
	 * @return \CodeIgniter\Database\BaseBuilder
	 */
	private function extraConfig($bui)
	{

		if($this->searchLike){
			$bui->groupStart();
			foreach ($this->searchLike as $key => $v) {
				if(is_array($v)){
					$bui->orLike($key, $v);
				}else{
					$bui->orLike($key, $v);
				}
			}
			$bui->groupEnd();
		}

		if($this->searchFiltros){
			$bui->groupStart();
			foreach ($this->searchFiltros as $key => $value) {
				if(is_array($value)){
					$bui->whereIn($key, $value);
				}else{
					$bui->where($key, $value);
				}
			}
			$bui->groupEnd();
		}

		if (!empty($this->dataTables["search"]["value"])) {
			$bui->groupStart();
			foreach ($this->searchLike as $key => $field) {
				if ($key == 0) {
					$bui->like($field, $this->dataTables["search"]["value"]);
				} else {
					$bui->orLike($field, $this->dataTables["search"]["value"]);
				}
			}
			$bui->groupEnd();
		}
		if (isset($this->dataTables["order"])) {
			if (!empty($this->order)) {
				if ($this->order[$this->dataTables['order']['0']['column']] != null) {
					$bui->orderby($this->order[$this->dataTables['order']['0']['column']], $this->dataTables['order']['0']['dir']);
				} else {
					if (count($this->defaultOrder) != 0) {
						foreach ($this->defaultOrder as $value) {
							$bui->orderby($value[0], $value[1]);
						}
					}
				}
			} else {
				if (count($this->defaultOrder) != 0) {
					foreach ($this->defaultOrder as $value) {
						$bui->orderby($value[0], $value[1]);
					}
				}
			}
		} else {
			if (count($this->defaultOrder) != 0) {
				foreach ($this->defaultOrder as $value) {
					$bui->orderby($value[0], $value[1]);
				}
			}
		}
		return $bui;
	}

	/**
	 * Get the complete Datatable Json string or data array.
	 *
	 * @param boolean $isJson return the json string.
	 * @return string|array
	 */
	public function getDatatable($name = false, $isJson = true)
	{
		if ($result = $this->getQuery()) {
			$data = array();
			foreach ($result->getResult() as $key => $row) {
				if($name=='name'){
					$data[] = $this->getOutputData($row, 'name');
				}else{
					$data[] = $this->getOutputData($row);
				}
			}

			if(isset($this->dataTables["draw"])){
				$draw = (int) $this->dataTables["draw"] ?? -1;
			}else{
				$draw = -1;
			}
			$output = array(
				"draw" => $draw,
				"recordsTotal" => $this->getTotal(),
				"recordsFiltered" => $this->getFiltered(),
				"data" => $data,
			);
			return $isJson ? json_encode($output) : $output;
		}
		return $data;
	}

}
