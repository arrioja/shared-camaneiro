<?php



/**

 * MEffectPanel

 *

 * @author Michael Härtl <haertl.mike@googlemail.com>

 * @link http://www.pradosoft.com/

 * @license http://www.pradosoft.com/license/

 * @version $Id: MEffectPanel.php 84 2008-02-21 17:51:21Z mikl $

 */



Prado::using('System.Web.UI.WebControls.TPanel');



/**

 * Class MEffectPanel 

 * 

 * MEffectPanel is an animated TPanel that uses PRADO's bundled

 * script.aculo.us effect library. There are two configurable 

 * animations to open and close the panel that can be triggered 

 * by other controls.

 * 

 * Any validators inside the panel will be enabled/disabled 

 * according to the current state of the panel. The panel also

 * recovers it's last state on postpack.

 *

 *

 * Configurable Properties:

 *

 * OpenControls, CloseControls, ToggleControls

 *

 * Configures which controls start the animation  to open or 

 * close the panel. Can either be a control ID or a comma separatet 

 * list of ID's.

 *

 * OpenEffect, CloseEffect

 *

 * Defines the scriptacolous effect for the opening and closing 

 * animation.

 *

 * OpenEffectOptions, CloseEffectOptions

 * 

 * Set scriptacolous' effect options.

 *

 * OpenByDefault

 *

 * If true panel will be opened on first page load. Defaults to true.

 *

 *

 * Example 1:

 *

 *  <com:TRadioButton ID="A" GroupName="X" Text="Option A" Checked="true" /><br />

 *  <com:TRadioButton ID="B" GroupName="X" Text="Option B" /><br />

 *  <com:TRadioButton ID="C" GroupName="X" Text="Other Option" /><br />

 *

 *  <MEffectPanel 

 *      OpenControls="C"

 *      OpenEffect="Effect.SlideDown" 

 *      OpenEffectOptions="{duration:.5,queue:'end'}"

 *      CloseControls="A,B" 

 *      CloseEffect="Effect.SlideUp" 

 *      CloseEffectOptions="{duration:.5,queue:'end'}">

 *

 *      <com:TTextBox ID="Other" />

 *      <com:TLabel ForControl="Other" Text="Enter other option" />

 *

 *  </MEffectPanel>

 *

 * Example 2:

 *

 *  <com:TCheckBox ID="A" Text="Yes, show me more!" />

 *

 *  <MEffectPanel

 *      ToggleControls="A" 

 *      OpenByDefault="False"

 *      OpenEffect="Effect.SlideDown" 

 *      OpenEffectOptions="{duration:.5,queue:'end'}"

 *      CloseEffect="Effect.SlideUp" 

 *      CloseEffectOptions="{duration:.5,queue:'end'}">

 *

 *      Controls inside (including validators) will be toggled

 *      if you check / uncheck the above Checkbox.

 *      

 *  </MEffectPanel>

 */

class MEffectPanel extends TPanel {



    /**

     * @var list of validators inside this panel

     */

    private $_validators=null;

    private $_state=null;



    /**

     * Add hidden field to preserve panel state on postback

     * @param mixed event parameter

     */

    public function onInit($param) {

        $this->_state=new THiddenField;

        if (!$this->getPage()->getIsPostBack()) 

            $state->Value=$this->getOpenByDefault()?1:0;

        $this->getControls()->add($this->_state);

        // Needed to have an id-attribute in the hidden field

        $this->_state->setID($this->_state->getID(false));

    }



    /**

     * Find trigger controls and prepare observer attachment

     * @param mixed event parameter

     */

    public function onLoad($param) {



        // Find OpenControls
foreach (explode(',',$this->getOpenControls()) as $id) {
        //foreach (split(',',$this->getOpenControls()) as $id) {

            if (!count($c=$this->getPage()->findControlsByID(trim($id)))) continue;

            $c[0]->attachEventHandler('OnPreRender', array($this,'attachOpenObserver'));

        }



        // Find CloseControls

        foreach (explode(',',$this->getCloseControls()) as $id) {

            if (!count($c=$this->getPage()->findControlsByID($id))) continue;

            $c[0]->attachEventHandler('OnPreRender', array($this,'attachCloseObserver'));

        }



        // Find ToggleControls

        foreach (explode(',',$this->getToggleControls()) as $id) {

            if (!count($c=$this->getPage()->findControlsByID($id))) continue;

            $c[0]->attachEventHandler('OnPreRender', array($this,'attachToggleObserver'));

        }



        // Set default visibility via display style field

        if ($this->_state->Value)

            $this->getStyle()->clearStyleField('display');

        else

            $this->getStyle()->setStyleField('display','none');



        // Attach js handler to all Validator's ClientSide.OnValidate in this panel

        foreach ($this->getValidators() as $validator) {

            $validator->getClientSide()->setOnValidate(

                "sender.isValid=true;sender.enabled=($('".$this->ClientID."').style.display=='')?true:false;");

            $validator->attachEventHandler('OnValidate',array($this,'checkValidatorVisibility'));

        }

    }

        

    /**

     * Re-Enable all contained validators even if they where disabled

     * by checkValidatorVisibility() to ensure inclusion of validator's js. 

     * Otherwise clientside validation wouldn't work if page was submitted

     * with panel in "hidden" state. 

     * @param mixed event parameter

     */

    public function onPreRender($param) {

        $this->getPage()->getClientScript()->registerPradoScript("effects");

        foreach ($this->getValidators() as $validator)

            $validator->setEnabled(true);

    }



    /**

     * Always add id attribute

     * @param THtmlWriter the writer used for the rendering purpose

     */

    protected function addAttributesToRender($writer) {

        parent::addAttributesToRender($writer);

        $writer->addAttribute('id',$this->getClientID());

    }



    /**

     * Add another <div>..</div> for correct working of scriptaculous effects

     * @param THtmlWriter the writer used for the rendering purpose

     */

    public function renderContents($writer) {

        $writer->write('<div>');

        parent::renderContents($writer);

        $writer->write('</div>');

    }



    /**

     * Attach Event.observer to OpenControls

     */

    public function attachOpenObserver($sender) {

        $senderID=$sender->getClientID();

        $thisID=$this->getClientID();

        $stateID=$this->_state->getClientID();

        $this->getPage()->getClientScript()->registerEndScript("meffect_$senderID",

            "Event.observe('$senderID','click',function(event) {" .

            "if($('$stateID').value!=1) new ".$this->getOpenEffect().

            "('$thisID',".$this->getOpenEffectOptions().");$('$stateID').value=1;});");

    }



    /**

     * Attach Event.observer to CloseControls

     */

    public function attachCloseObserver($sender) {

        $senderID=$sender->getClientID();

        $thisID=$this->getClientID();

        $stateID=$this->_state->getClientID();

        $this->getPage()->getClientScript()->registerEndScript("meffect_$senderID",

            "Event.observe('$senderID','click',function(event) {" .

            "if($('$stateID').value==1) new ".$this->getCloseEffect().

            "('$thisID',".$this->getCloseEffectOptions().");$('$stateID').value=0;});");

    }



    /**

     * Attach Event.observer to ToggleControls

     */

    public function attachToggleObserver($sender) {

        $senderID=$sender->getClientID();

        $thisID=$this->getClientID();

        $stateID=$this->_state->getClientID();

        $this->getPage()->getClientScript()->registerEndScript("meffect_$senderID",

            "Event.observe('$senderID','click',function(event) {" .

            "if($('$stateID').value==1){new ".$this->getCloseEffect().

            "('$thisID',".$this->getCloseEffectOptions().");$('$stateID').value=0;".

            "}else{ new ".$this->getOpenEffect().

            "('$thisID',".$this->getOpenEffectOptions().");$('$stateID').value=1;}});");

    }





    /**

     * Disables validation for current validator if the panel is in 

     * "hidden" state. (Attached to OnValidate)

     */

    public function checkValidatorVisibility($sender,$param) {

        $sender->Enabled=$this->_state->Value;

    }





    /**

     * Find all validators inside this panel

     * @return array of validators

     */

    protected function getValidators() {

        if ($this->_validators===null) {

            $this->_validators=array();

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TRequiredFieldValidator'));

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TRegularExpressionValidator'));

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TEmailAddressValidator'));

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TCompareValidator'));

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TDataTypeValidator'));

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TRangeValidator'));

            $this->_validators=array_merge($this->_validators,$this->findControlsByType('TCustomValidator'));

        }

        return $this->_validators;

    }





    /**

     * Getter/setter OpenByDefault

     */

    public function setOpenByDefault($value) {

        $this->setViewState('OpenByDefault',TPropertyValue::ensureBoolean($value),true);

    }

    public function getOpenByDefault() {

        return $this->getViewState('OpenByDefault',true);

    }





    /**

     * Getter/setter OpenControls/-Effect/-EffectOptions

     */

    public function setOpenControls($value) {

        $this->setViewState('OpenControls',$value,'');

    }

    public function getOpenControls() {

        return $this->getViewState('OpenControls','');

    }

    public function setOpenEffect($value) {

        $this->setViewState('OpenEffect',$value,'');

    }

    public function getOpenEffect() {

        return $this->getViewState('OpenEffect','');

    }

    public function setOpenEffectOptions($value) {

        $this->setViewState('OpenEffectOptions',$value,'');

    }

    public function getOpenEffectOptions() {

        return $this->getViewState('OpenEffectOptions','');

    }



    /**

     * Getter/setter CloseControls/-Effect/-EffectOptions

     */

    public function setCloseControls($value) {

        $this->setViewState('CloseControls',$value,'');

    }

    public function getCloseControls() {

        return $this->getViewState('CloseControls','');

    }

    public function setCloseEffect($value) {

        $this->setViewState('CloseEffect',$value,'');

    }

    public function getCloseEffect() {

        return $this->getViewState('CloseEffect','');

    }

    public function setCloseEffectOptions($value) {

        $this->setViewState('CloseEffectOptions',$value,'');

    }

    public function getCloseEffectOptions() {

        return $this->getViewState('CloseEffectOptions','');

    }



    /**

     * Getter/setter ToggleControls/-Effect/-EffectOptions

     */

    public function setToggleControls($value) {

        $this->setViewState('ToggleControls',$value,'');

    }

    public function getToggleControls() {

        return $this->getViewState('ToggleControls','');

    }

    public function setToggleEffect($value) {

        $this->setViewState('ToggleEffect',$value,'');

    }

    public function getToggleEffect() {

        return $this->getViewState('ToggleEffect','');

    }

    public function setToggleEffectOptions($value) {

        $this->setViewState('ToggleEffectOptions',$value,'');

    }

    public function getToggleEffectOptions() {

        return $this->getViewState('ToggleEffectOptions','');

    }

}

