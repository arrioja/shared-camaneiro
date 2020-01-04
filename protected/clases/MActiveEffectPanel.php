<?php

/**
 * MActiveEffectPanel&#160; class file
 *
 * @author Leonardo Grimaldi Salcedo <leonardo.grimaldi@gmail.com>
 * @link http://www.e-strategica.com/
 * @version $Id: MActiveEffectPanel.php V_1 2008-06-10 17:35:00 based on MffectPanel by Michael H&#195;&#164;rtl <mh@m-h-it.de>
 * @package Web.UI.Controls
 */

/**
 * Class MActiveEffectPanel 
 * 
 * MActiveEffectPanel is an animated TPanel that uses PRADO's bundled
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
 * Configures which controls start the animation&#160; to open or 
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
 *&#160; <com:TRadioButton ID="A" GroupName="X" Text="Option A" Checked="true" /><br />
 *&#160; <com:TRadioButton ID="B" GroupName="X" Text="Option B" /><br />
 *&#160; <com:TRadioButton ID="C" GroupName="X" Text="Other Option" /><br />
 *
 *&#160; <MActiveEffectPanel 
 *&#160; &#160; &#160; OpenControls="C"
 *&#160; &#160; &#160; OpenEffect="Effect.SlideDown" 
 *&#160; &#160; &#160; OpenEffectOptions="{duration:.5,queue:'end'}"
 *&#160; &#160; &#160; CloseControls="A,B" 
 *&#160; &#160; &#160; CloseEffect="Effect.SlideUp" 
 *&#160; &#160; &#160; CloseEffectOptions="{duration:.5,queue:'end'}">
 *
 *&#160; &#160; &#160; <com:TTextBox ID="Other" />
 *&#160; &#160; &#160; <com:TLabel ForControl="Other" Text="Enter other option" />
 *
 *&#160; </MActiveEffectPanel>
 *
 * Example 2:
 *
 *&#160; <com:TCheckBox ID="A" Text="Yes, show me more!" />
 *
 *&#160; <MActiveEffectPanel
 *&#160; &#160; &#160; ToggleControls="A" 
 *&#160; &#160; &#160; OpenByDefault="False"
 *&#160; &#160; &#160; OpenEffect="Effect.SlideDown" 
 *&#160; &#160; &#160; OpenEffectOptions="{duration:.5,queue:'end'}"
 *&#160; &#160; &#160; CloseEffect="Effect.SlideUp" 
 *&#160; &#160; &#160; CloseEffectOptions="{duration:.5,queue:'end'}">
 *
 *&#160; &#160; &#160; Controls inside (including validators) will be toggled
 *&#160; &#160; &#160; if you check / uncheck the above Checkbox.
 *&#160; &#160; &#160; 
 *&#160; </MActiveEffectPanel>
 */
/**
 * MActiveEffectPanel class.
 *
 * MActiveEffectPanel 
 */

Prado::using('System.Web.UI.ActiveControls.TActiveControlAdapter');



class MActiveEffectPanel extends MEffectPanel implements IActiveControl

{

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

	 * @return TBaseActiveControl standard active control options.

	 */

	public function getActiveControl()

	{

		return $this->getAdapter()->getBaseActiveControl();

	}



	/**

	 * Renders and replaces the panel's content on the client-side.

	 * When render() is called before the OnPreRender event, such as when render()

	 * is called during a callback event handler, the rendering

	 * is defered until OnPreRender event is raised.

	 * @param THtmlWriter html writer

	 */

	public function render($writer)

	{

		if($this->getHasPreRendered())

		{

			parent::render($writer);

			if($this->getActiveControl()->canUpdateClientSide())

				$this->getPage()->getCallbackClient()->replaceContent($this,$writer);

		}

		else

		{

			$this->getPage()->getAdapter()->registerControlToRender($this,$writer);
			if ($this->getHasControls())
			{
				// If we update a MActiveEffectPanel on callback,
				// We shouldn't update all childs, because the whole content will be replaced by 
				// the parent
				foreach ($this->findControlsByType('IActiveControl', false) as $control)
				{
						$control->getActiveControl()->setEnableUpdate(false);
				}
			}

		}

	}

}



?>
