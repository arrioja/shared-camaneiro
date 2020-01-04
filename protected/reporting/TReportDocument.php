<?php

abstract class TReportDocument extends TApplicationComponent
{
	private $_id='';
	private $_format='';

	/**
	 * Initializes the feed.
	 * @param TXmlElement configurations specified in {@link TFeedService}.
	 */
	public function init($config)
	{
	}

	/**
	 * @return string ID of this feed
	 */
	public function getID()
	{
		return $this->_id;
	}

	/**
	 * @param string ID of this feed
	 */
	public function setID($value)
	{
		$this->_id=$value;
	}

	public function setFormat($value){
		$this->_format = $value;
	}
	public function getFormat(){
		return $this->_format;
	}
	/**
	 * @return string an XML string representing the feed content
	 */
	public function getReportFile($startdate,$enddate)
	{
		return '';
	}
}

?>