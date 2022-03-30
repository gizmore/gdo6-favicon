<?php
namespace GDO\Favicon\Method;

use GDO\Core\Method;
use GDO\Favicon\Module_Favicon;
use GDO\File\Method\GetFile;
use GDO\Form\GDT_Select;

/**
 * Render a favicon image.
 * @author gizmore
 */
final class Image extends Method
{
	public function gdoParameters()
	{
		$choices = ['favicon', 'appletouch'];
		$choices = array_combine($choices, $choices);
		return [
			GDT_Select::make('variant')->choices($choices),
		];
	}
	
	public function execute()
	{
		$image = Module_Favicon::instance()->cfgFavicon();
		$variant = $this->gdoParameterVar('variant');
		return GetFile::make()->executeWithId($image->getID(), $variant, true);
	}
	
}
