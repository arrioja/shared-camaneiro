<?php
/**
 * File DMessagesPanel.php
 * @package Misc
 * @author Joao Marques <joao@jjmf.com>
 */

/**
 * DMessagesPanel Component
 *
 * This component can be used to display messages to the user.
 * It is basicaly a message bar that starts hidden and by calling
 * setSuccessMessage() or setErrorMessage() methods one can set the message
 * to be displayed and show the message panel. Clicking on it while it
 * is visible will make it dissapear.
 *
 * Showing and hidding the message panel is done using scriptaculous
 * effects by calling methods of TCallbackClientScript. To hide the Panel
 * just click anywhere on it. One can set the effects used to show and
 * hide the message panel by calling  setDefaultAppearEffect() and
 * setDefaultHideEffect() respectively.
 * The default values are 'slideDown' and 'SlideUp' respectively.
 *
 * NOTE: the values used to specify the appear effect is the method name
 * as in TCallbackClientScript while the values used to specify the
 * hide effect are the function name as in scriptaculous documentation.
 *
 * Here are some examples on how to use it in .page files:
 * <code>
 * <com:DMessagesPanel ID="MessagesPanel"
 *      DefaultAppearEffect="slideDown"
 *      DefaultHideEffect="SlideUp"/>
 * </code>
 *
 * And in .php files:
 * <code>
 * $this->MessagesPanel->setSuccessMessage($sender, 'Data updated!');
 * $this->MessagesPanel->setErrorMessage($sender, 'E-mail invalid!');
 * $this->MessagesPanel->setSuccessMessage($sender, 'Success!', 'appear');
 * </code>
 *
 * Where $sender is the component that was clicked on the page.
 *
 * @package Misc
 */
class DMessagesPanel extends TTemplateControl
{
    /**
     * Sets up the default css style (bulit-in) for the message bar
     * Initialy sets the message bar invisible
     * @param mixed $param
     */
    public function onInit($param)
    {
        parent::onInit($param);

        # Sets the default css classes (built-in)
    	$this->setPanelCssClass('MessagePanel');
    	$this->setErrorMessageCssClass('ErrorMessage');
    	$this->setSuccessMessageCssClass('SuccessMessage');

    	# Hide the message panel
        $this->getPage()->getCallbackClient()->hide($this->MessagesPanel);

        # Publish the css file
        $cs = $this->getPage()->getClientScript();
        $CssFile = $this->publishAsset('DMessagesPanel.css');
        if(!$cs->isStyleSheetFileRegistered('DMessagesPanel')) {
			$cs->registerStyleSheetFile('DMessagesPanel', $CssFile);
		}
    }

    /**
     * Basicaly sets the default hide effect
     * @param mixed $param
     */
    public function onLoad($param)
    {
    	$this->ensureChildControls();

    	$effect = $this->getViewState('hideEffect', 'SlideUp');
    	$this->HideFunction->Text = "<script type=\"text/javascript\"> /*
    	<![CDATA[*/function hideMessagePanel(messagepanel){
    	new Effect.$effect(messagepanel); } /*]]>*/</script>";
    }

    /**
     * Use this method to show a success message
     * It uses the default error css style
     * @param mixed $sender is the object that was clicked on the screen
     * @param string $value is the message to show
     * @param string $effect is the effect to use (optional)
     */
    public function setSuccessMessage($sender, $value, $effect = null)
    {
    	$this->ensureChildControls();
        $this->Message->CssClass = $this->getViewState('cssSuccess');
        $this->setViewState('message', $value);
        $this->setViewState('effect', $effect);
        if (stristr(get_class($sender), 'Active') === false) $this->showMessage(true);
        else $this->showMessage(false);
    }

    /**
     * Use this method to show an error message
     * It uses the default error css style
     * @param mixed $sender is the object that was clicked on the screen
     * @param string $value is the message to show
     * @param string $effect is the effect to use (optional)
     */
    public function setErrorMessage($sender, $value, $effect = null)
    {
        $this->ensureChildControls();
        $this->Message->CssClass = $this->getViewState('cssError');
        $this->setViewState('message', $value);
        $this->setViewState('effect', $effect);
        if (stristr(get_class($sender), 'Active') === false) $this->showMessage(true);
        else $this->showMessage(false);
    }

    /**
     * Show the message panel that will be displayed
     * @param string $value is the message to be displayed
     * @param string $effect is the effect to used when showing the message bar
     */
    private function showMessage($postBack = false)
    {
        if (($msg = $this->getViewState('message', '')) != '') {
            $this->ensureChildControls();
            $this->Message->Text = $msg;
            $effect = $this->getViewState('effect', null) !== null ? $this->getViewState('effect') : $this->getViewState('appearEffect', 'slideDown');
            if (!$postBack)
                $this->getPage()->getCallbackClient()->$effect($this->MessagesPanel);
            else {
                $this->PostBackUse->Text = "<script type=\"text/javascript\">/*<![CDATA[*/new Effect.SlideDown(document.getElementById('{$this->MessagesPanel->ClientId}'))/*]]>*/</script>";
            }
        } else {
            $this->setViewState('message', '');
        }
    }

    /**
     * Specify a css class for the panel
     * @param string $value is a css class name
     */
    public function setPanelCssClass($value)
    {
        $this->ensureChildControls();
    	$this->MessagesPanel->CssClass = $value;
    }

    /**
     * Specify a css class for a success message
     * @param string $value is a css class name
     */
    public function setSuccessMessageCssClass($value)
    {
        $this->setViewState('cssSuccess', $value);
    }

    /**
     * Specify a css class for an error message
     * @param string $value is a css class name
     */
    public function setErrorMessageCssClass($value)
    {
        $this->setViewState('cssError', $value);
    }

    /**
     * Sets the default effect to be used when showing the message bar.
     * @param string $value is the method name as in TCallbackClientScript
     * Examples: appear, blindUp, toggle
     */
    public function setDefaultAppearEffect($value)
    {
    	$this->setViewState('appearEffect', $value);
    }

    /**
     * Sets the default effect to be used when hidding the message bar.
     * @param string $value is the effect name as shown in the scriptaculous demos
     * at http://wiki.script.aculo.us/scriptaculous/show/CombinationEffectsDemo
     * Examples: Fade, BlindDown, SwitchOff
     */
    public function setDefaultHideEffect($value)
    {
    	$this->setViewState('hideEffect', $value);
    }
}

?>