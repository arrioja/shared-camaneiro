<?php



/**

 * XActiveDataGrid related class files.

 * This file contains the definition of the following classes:

 * XActiveDataGrid, XActiveDataGridColumn, XActiveEditCommandColumn

 *

 * This control allows to create an ajax-enabled datagrid, being able to sort, page, edit, and

 * do all the same operations that the normal datagrid is.

 *

 * HOW TO USE

 * Working with this active datagrid is exactly the same that working with the standard one.

 * You can mix columns of type TBoundColumn for standard columns (it means with normal post-backs for sorting)

 * with XActiveBoundColumn for ajax sorting.

 * 

 *

 * @author Mauro Lewinzon <maurokun@gmail.com>

 * @link http://www.enigmastudio.com.ar/

 * @copyright Copyright &copy; 2007 EnigmaStudio

 * @license http://www.pradosoft.com/license/

 * @package Enigma.ActiveControls

 */

// <!-- WOJTEB's update

Prado::using('System.Web.UI.ActiveControls.TActiveControlAdapter');

// -->





class XActiveDataGrid extends TDataGrid implements  IActiveControl, ICallbackEventHandler

{



	/**

	 * This flag is used to determine if the TActivePanel has been created or not.

	 * @private boolean

	*/

	private $_isInit = false;

  

	// <!-- WOJTEB's copy from TActiveButton :)

  

  

    protected $CallbackClientSideActions =    

                 Array(/*'EnablePageStateUpdate',  'HasPriority',*/

                       'OnComplete', 'OnException', 'OnFailure', 'OnInteractive', 'OnLoaded', 'OnLoading', 'OnPreDispatch', 'OnSuccess', 'OnUninitialized', 'PostBackParameter', 'PostBackTarget', 'PostState', 'RequestTimeOut');

  /**

    * Copy callbacks (<com:clientside />) $from $to

    * @param TCallbackClientSide

    * @param TCallbackClientSide 

    * @author Wojciech Bajon <wojciech[dot]bajon+prado[at]gmail[dot]com>

    */

    public function copyClientSide(TCallbackClientSide &$from, TCallbackClientSide &$to)

    {

      foreach($this->CallbackClientSideActions AS $a)

      {

        $set = 'set'.$a;

        $get = 'get'.$a;

        $e = $from->$get();

        if($e)

          $to->$set($e);

      }

    }

    

  /**

   * @author Wei Zhuo <weizhuo[at]gamil[dot]com>

   */

   

    /**

     * Creates a new callback control, sets the adapter to

     * TActiveControlAdapter. If you override this class, be sure to set the

     * adapter appropriately by, for example, by calling this constructor.

     */

    public function __construct()

    {

        parent::__construct();

        $this->setAdapter(new TActiveControlAdapter($this));

    }

 

    /**

     * @return TBaseActiveCallbackControl standard callback control options.

     */

    public function getActiveControl()

    {

        return $this->getAdapter()->getBaseActiveControl();

    }

 

    

    /**

     * @return TCallbackClientSide client side request options.

     */

    public function getClientSide()

    {

        return $this->getAdapter()->getBaseActiveControl()->getClientSide();

    }

 

    

    /**

     * Raises the callback event. This method is required by {@link }

     * ICallbackEventHandler} interface. If {@link getCausesValidation}

     * CausesValidation} is true, it will invoke the page's {@link TPage::}

     * validate validate} method first. It will raise {@link onClick}

     * OnClick} event first and then the {@link onCallback OnCallback} event.

     * This method is mainly used by framework and control developers.

     * @param TCallbackEventParameter the event parameter

     */

     public function raiseCallbackEvent($param)

    {

        $this->raisePostBackEvent($param);

        $this->onCallback($param);

    }

 

    /**

     * This method is invoked when a callback is requested. The method raises

     * 'OnCallback' event to fire up the event handlers. If you override this

     * method, be sure to call the parent implementation so that the event

     * handler can be invoked.

     * @param TCallbackEventParameter event parameter to be passed to the event handlers

     */

    public function onCallback($param)

    {

        $this->raiseEvent('OnCallback', $this, $param);

    }





    

  // WOJTEB's -->

  

  

  

  

  

	

	// <!-- DURANDAL's updates

	/**

	 * @return current sort exprssion. Defaults to first column's SortExpression.

	 */

	public function getCurrentSortExpression()

	{

		// TODO : test if AllowSrting is true

		return $this->getViewState('CurrentSortExpression', $this->getDefaultSortExpression());

	}



	/**

	 * @param current sort exprssione

	 */

	public function setCurrentSortExpression($value)

	{

		// TODO : test if AllowSrting is true and if $value is correct

		$this->setViewState('CurrentSortExpression', $value, $this->getDefaultSortExpression());

	}

	

	/**

	 * @return first column's SortExpression.

	 */

	private function getDefaultSortExpression() {

		// TODO : test if AllowSrting is true

		

		foreach ($this->getColumns() as $column) {

			if ($column->getSortExpression()) return $column->getSortExpression();

		}

	}

	

	public static function initializeColumnHeaderCellForSorting($column, $cell, $columnIndex) {

		$sortExpression = $column->getSortExpression();

		$text = $column->getHeaderText();

		

		if(($url = $column->getHeaderImageUrl())!=='')

		{

			$button = Prado::createComponent('System.Web.UI.ActiveControls.TActiveImageButton');

			$button->setImageUrl($url);

			$button->setCommandName(TDataGrid::CMD_SORT);

			$button->setCommandParameter($sortExpression);

			if($text !== '')

				$button->setAlternateText($text);

      $button->setCausesValidation(false);

      // <!-- WOJTEB's copy from TActiveButton :)

      $column->getOwner()->copyClientSide($column->getOwner()->getClientSide(),$button->getClientSide());

      // -->

			$cell->getControls()->add($button);

		}

		else if ($text!=='')

		{

			$button = Prado::createComponent('System.Web.UI.ActiveControls.TActiveLinkButton');

			$button->setText($text);

			$button->setCommandName(TDataGrid::CMD_SORT);

			$button->setCommandParameter($sortExpression);

			$button->setCausesValidation(false);

      // <!-- WOJTEB's update

      $column->getOwner()->copyClientSide($column->getOwner()->getClientSide(),$button->getClientSide());

      // -->

			$cell->getControls()->add($button);

		}

		else

			$cell->setText('&nbsp;');		

	}

	

	// DURANDAL's updates -->

	

	

	/**

	 * Creates a TActivePanel and inserts it as the parent of this datagrid control.

	 * After the panel, this calls the initRecursive method of it to continue with the process.

	 *

	 * @param TControl the naming container control

	*/

	protected function initRecursive($namingContainer=null)

	{

		if(!$this->_isInit)

		{

			$t = new TActivePanel();

			$t->setID("ActivePanelFor".$this->getID());

			$ind = $this->getParent()->getControls()->indexOf($this);

			$this->getParent()->getControls()->insertAt($ind,$t);

			$t->getControls()->add($this);	

			$this->_isInit = true;

			$this->getParent()->initRecursive($namingContainer);	

		}

		else

			parent::initRecursive($namingContainer);	

	}





	/**

	 * Overwirtes parent implementation to create active controls instead regular controls.

	 *

	*/

	 

	protected function createPagerButton($buttonType,$enabled,$text,$commandName,$commandParameter)

	{

		if($buttonType==='LinkButton')

		{

			if($enabled)

				$button=new TActiveLinkButton();

			else

			{

				$button=new TLabel;

				$button->setText($text);

			

				return $button;

			}

		}

		else

		{

			$button=new TActiveLinkButton();

			if(!$enabled)

				$button->setEnabled(false);

		}

		$button->setText($text);

		$button->setCommandName($commandName);

		$button->setCommandParameter($commandParameter);

      

    $this->copyClientSide($this->getClientSide(),$button->getClientSide());

		return $button;

	}



	/**

	 * Performs the PreRender step for the control and all its child controls.

	 * Overwrites parent implementation forcing, if it is a callback, to update the TActivePanel container.

	 */



	public function preRenderRecursive()

	{

		parent::preRenderRecursive();

		if($this->getPage()->IsCallBack)

			$this->getParent()->renderControl($this->getPage()->getResponse()->createHtmlWriter());

	}

  

  protected function initializeItem(&$item, &$columns)

  {

    

    

    parent::initializeItem($item,$columns);

    

  }

}





Prado::using('System.Web.UI.WebControls.TDataGridColumn');



/**

 * XActiveBoundColumn class

 *

 * XActiveBoundColumn provides the implementation to create Active Controls instead regular ones.

 * It works equal to his parent, TBoundColumn.

 *

 * @author Enigma Studio <contacto@enigmastudio.com.ar>

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveBoundColumn extends TBoundColumn

{



	/**

	 * Initializes the header cell.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for sorting.

	 * If sorting is disabled it calls parent implementation.

	 *

	 * @param TTableCell the cell to be initialized

	 * @param integer the index to the Columns property that the cell resides in.

	 */

	protected function initializeHeaderCell($cell,$columnIndex)

	{

		if($this->getAllowSorting())

		{

			$this->getOwner()->initializeColumnHeaderCellForSorting($this, $cell, $columnIndex);

		}

		else

		{

			parent::initializeHeaderCell($cell,$columnIndex);

		}

	}





}



/**

 * TEditCommandColumn class file

 */

Prado::using('System.Web.UI.WebControls.TEditCommandColumn');



/**

 * XActiveEditCommandColumn class

 *

 * XActiveEditCommandColumn contains the Edit command buttons for editing data items in each row.

 *

 *

 * @author Enigma Studio <contacto@enigmastudio.com.ar>

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveEditCommandColumn extends TEditCommandColumn

{



	/**

	 * Creates a button and initializes its properties.

	 * The button type is determined by {@link getButtonType ButtonType}.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for editing.

	 *

	 * @param string command name associated with the button

	 * @param string button caption

	 * @param boolean whether the button should cause validation

	 * @param string the validation group that the button belongs to

	 * @return mixed the newly created button.

	 */

	protected function createButton($commandName,$text,$causesValidation,$validationGroup)

	{

		if($this->getButtonType()===TButtonColumnType::LinkButton)

			$button=Prado::createComponent('System.Web.UI.ActiveControls.TActiveLinkButton');

		else if($this->getButtonType()===TButtonColumnType::PushButton)

			$button=Prado::createComponent('System.Web.UI.ActiveControls.TActiveButton');

		else	// image buttons

		{

			$button=Prado::createComponent('System.Web.UI.ActiveControls.TActiveImageButton');

			if(strcasecmp($commandName,'Update')===0)

				$url=$this->getUpdateImageUrl();

			else if(strcasecmp($commandName,'Cancel')===0)

				$url=$this->getCancelImageUrl();

			else

				$url=$this->getEditImageUrl();

			$button->setImageUrl($url);

		}

		$button->setText($text);

		$button->setCommandName($commandName);

		$button->setCausesValidation($causesValidation);

		$button->setValidationGroup($validationGroup);

    $this->getOwner()->copyClientSide($this->getOwner()->getClientSide(),$button->getClientSide());

		return $button;

	}



}





// <!-- DURANDAL's updates





/**

 * XActiveButtonColumn class file

 */

Prado::using('System.Web.UI.WebControls.TButtonColumn');



/**

 * XActiveButtonColumn class

 *

 * XActiveButtonColumn contains the Edit command buttons for editing data items in each row.

 *

 *

 * @author Durandal

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveButtonColumn extends TButtonColumn{



	/**

	 * Initializes the specified cell to its initial values.

	 * This method overrides the parent implementation.

	 * It creates a command button within the cell.

	 * @param TTableCell the cell to be initialized.

	 * @param integer the index to the Columns property that the cell resides in.

	 * @param string the type of cell (Header,Footer,Item,AlternatingItem,EditItem,SelectedItem)

	 */

	public function initializeCell($cell,$columnIndex,$itemType)

	{

		if($itemType===TListItemType::Item || $itemType===TListItemType::AlternatingItem || $itemType===TListItemType::SelectedItem || $itemType===TListItemType::EditItem)

		{

			$buttonType=$this->getButtonType();

			if($buttonType===TButtonColumnType::LinkButton)

				$button=new TActiveLinkButton;

			else if($buttonType===TButtonColumnType::PushButton)

				$button=new TActiveButton;

			else // image button

			{

				$button=new TActiveImageButton;

				$button->setImageUrl($this->getImageUrl());

			}

			$button->setText($this->getText());

			$button->setCommandName($this->getCommandName());

			$button->setCausesValidation($this->getCausesValidation());

			$button->setValidationGroup($this->getValidationGroup());

      if($this->getDataTextField()!=='' || ($buttonType===TButtonColumnType::ImageButton && $this->getDataImageUrlField()!==''))

				$button->attachEventHandler('OnDataBinding',array($this,'dataBindColumn'));

			$cell->getControls()->add($button);

			$cell->registerObject('Button',$button);

		}

		else

			parent::initializeCell($cell,$columnIndex,$itemType);

	}

	

	/**

	 * Initializes the header cell.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for sorting.

	 * If sorting is disabled it calls parent implementation.

	 *

	 * @param TTableCell the cell to be initialized

	 * @param integer the index to the Columns property that the cell resides in.

	 */

	protected function initializeHeaderCell($cell,$columnIndex)

	{

		if($this->getAllowSorting())

		{

			$this->getOwner()->initializeColumnHeaderCellForSorting($this, $cell, $columnIndex);

		}

		else

		{

			parent::initializeHeaderCell($cell,$columnIndex);

		}

	}

}







/**

 * XActiveCheckBoxColumn class file

 */

Prado::using('System.Web.UI.WebControls.TCheckBoxColumn');



/**

 * XActiveCheckBoxColumn class

 *

 * XActiveCheckBoxColumn contains the Edit command buttons for editing data items in each row.

 *

 *

 * @author Durandal

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveCheckBoxColumn extends TCheckBoxColumn

{

	/**

	 * Initializes the header cell.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for sorting.

	 * If sorting is disabled it calls parent implementation.

	 *

	 * @param TTableCell the cell to be initialized

	 * @param integer the index to the Columns property that the cell resides in.

	 */

	protected function initializeHeaderCell($cell,$columnIndex)

	{

		if($this->getAllowSorting())

		{

			$this->getOwner()->initializeColumnHeaderCellForSorting($this, $cell, $columnIndex);

		}

		else

		{

			parent::initializeHeaderCell($cell,$columnIndex);

		}

	}

}









/**

 * XActiveDropDownListColumn class file

 */

Prado::using('System.Web.UI.WebControls.TDropDownListColumn');



/**

 * XActiveDropDownListColumn class

 *

 * XActiveDropDownListColumn contains the Edit command buttons for editing data items in each row.

 *

 *

 * @author Durandal

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveDropDownListColumn extends TDropDownListColumn

{

	/**

	 * Initializes the header cell.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for sorting.

	 * If sorting is disabled it calls parent implementation.

	 *

	 * @param TTableCell the cell to be initialized

	 * @param integer the index to the Columns property that the cell resides in.

	 */

	protected function initializeHeaderCell($cell,$columnIndex)

	{

		if($this->getAllowSorting())

		{

			$this->getOwner()->initializeColumnHeaderCellForSorting($this, $cell, $columnIndex);

		}

		else

		{

			parent::initializeHeaderCell($cell,$columnIndex);

		}

	}

}









/**

 * XActiveHyperLinkColumn class file

 */

Prado::using('System.Web.UI.WebControls.THyperLinkColumn');



/**

 * XActiveHyperLinkColumn class

 *

 * XActiveHyperLinkColumn contains the Edit command buttons for editing data items in each row.

 *

 *

 * @author Durandal

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveHyperLinkColumn extends THyperLinkColumn

{

	/**

	 * Initializes the header cell.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for sorting.

	 * If sorting is disabled it calls parent implementation.

	 *

	 * @param TTableCell the cell to be initialized

	 * @param integer the index to the Columns property that the cell resides in.

	 */

	protected function initializeHeaderCell($cell,$columnIndex)

	{

		if($this->getAllowSorting())

		{

			$this->getOwner()->initializeColumnHeaderCellForSorting($this, $cell, $columnIndex);

		}

		else

		{

			parent::initializeHeaderCell($cell,$columnIndex);

		}

	}

}









/**

 * XActiveTemplateColumn class file

 */

Prado::using('System.Web.UI.WebControls.TTemplateColumn');



/**

 * XActiveTemplateColumn class

 *

 * XActiveTemplateColumn contains the Edit command buttons for editing data items in each row.

 *

 *

 * @author Durandal

 * @package Enigma.ActiveControls

 * @since 3.1

 */



class XActiveTemplateColumn extends TTemplateColumn

{



	/**

	 * Initializes the header cell.

	 *

	 * Overwrites parent implementation to create active controls instead regular ones for sorting.

	 * If sorting is disabled it calls parent implementation.

	 *

	 * @param TTableCell the cell to be initialized

	 * @param integer the index to the Columns property that the cell resides in.

	 */

	protected function initializeHeaderCell($cell,$columnIndex)

	{

		if($this->getAllowSorting())

		{

			$this->getOwner()->initializeColumnHeaderCellForSorting($this, $cell, $columnIndex);

		}

		else

		{

			parent::initializeHeaderCell($cell,$columnIndex);

		}

	}

}





// DURANDAL's updates -->



?>