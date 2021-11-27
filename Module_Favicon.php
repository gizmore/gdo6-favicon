<?php
namespace GDO\Favicon;

use GDO\Core\GDO_Module;
use GDO\File\GDT_ImageFile;
use GDO\Core\Website;
use GDO\File\GDO_File;
use GDO\Core\GDT_Array;
use GDO\Core\GDOError;

/**
 * Upload a 192x192.PNG which is the default on most browsers.
 * Convert to ico as well.
 * @author gizmore
 * @version 6.10.2
 * @since 6.9.0
 */
final class Module_Favicon extends GDO_Module
{
	public function onLoadLanguage() { return $this->loadLanguage('lang/favicon'); }
	
	public function getConfig()
	{
	    return [
			GDT_ImageFile::make('favicon')->previewHREF(href('File', 'GetFile', '&file={id}'))->minHeight(196)->maxHeight(196)->minWidth(196)->maxWidth(196),
	    ];
	}
	
	/**
	 * @return GDO_File
	 */
	public function cfgFavicon() { return $this->getConfigValue('favicon'); }
	
	public function hookModuleVarsChanged(GDO_Module $module)
	{
		if ($module === $this)
		{
			$this->updateFavicon();
		}
	}
	
	public function updateFavicon()
	{
		# Copy as PNG
		$file = $this->cfgFavicon();
		if (!$file->isImageType())
		{
		    throw new GDOError('err_not_an_image', [$file->getPath(), $file->getType()]);
		}
		elseif (!$this->isIco($file))
		{
    		$this->convertToIco($file);
		}
		else
		{
		    copy($file->getPath(), 'favicon.ico');
		}
	}
	
	public function isIco(GDO_File $file)
	{
	    switch ($file->getType())
	    {
	        case 'image/vnd.microsoft.icon':
	            return true;
	    }
	    return false;
	}
	
	private function convertToIco(GDO_File $file)
	{
		require_once $this->filePath('php-ico/class-php-ico.php');
		$ico = new \PHP_ICO();
		$ico->add_image($file->getPath(), [32, 32]);
		$ico->save_ico('favicon.ico');
	}
	
	public function onIncludeScripts()
	{
		if ($image = $this->cfgFavicon())
		{
			$v = $image->getID();
			$root = GDO_WEB_ROOT;
			Website::addHead("<link rel=\"shortcut icon\" href=\"{$root}favicon.ico?v={$v}\" type=\"image/x-icon\" />");
			Website::addHead("<link rel=\"icon\" type=\"image/png\" href=\"{$root}favicon.png?v={$v}\" />");
		}
	}
	
	public function hookIgnoreDocsFiles(GDT_Array $ignore)
	{
	    $ignore->data[] = 'GDO/Favicon/php-ico/**/*';
	}
	
}
