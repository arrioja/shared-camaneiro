<?php

class ProfitReport extends TReportDocument{
	private $_id='';
	private $_data;

	/**
	 * Initializes the report
	 * @param TXmlElement configurations specified in {@link TReportingService}.
	 */
	public function init($config){

	}

	/**
	 * @return string ID of this report
	 */
	public function getID(){
		return $this->_id;
	}

	/**
	 * @param string ID of this report
	 */
	public function setID($value){
		$this->_id=$value;
	}

	/**
	 * @return string filename of the new report
	 */
	public function getReportFile($startdate,$enddate){
		if ($this->Format != "xls") // we do not support anything else yet:)
			throw new TConfigurationException('reportservice_unsupported_format',$this->Format);
		return $this->generateExcelDocument($startdate,$enddate);
	}

	private function loadData($startdate,$enddate){
		// load data
		$this->_data = $loadedData;
	}

	public function generateExcelDocument($startdate,$enddate){
		$this->loadData($startdate,$enddate);

		// create excel document
		// write data to document
		// return path to document
		return $filename;
	}
}
?>