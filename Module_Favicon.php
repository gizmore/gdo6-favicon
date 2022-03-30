<?php
namespace GDO\Favicon;

use GDO\Core\GDO_Module;
use GDO\File\GDT_ImageFile;
use GDO\Core\Website;
use GDO\File\GDO_File;
use GDO\UI\GDT_Image;
use GDO\Core\GDT_Array;
use GDO\Core\GDOError;

/**
 * Upload a bigger image, which gets converted to various formats.
 * Embed these favicons into the html header.
 * Convert to favicon.ico as well, which is not referenced in the html.
 * 
 * @author gizmore
 * @version 6.11.4
 * @since 6.9.0
 */
final class Module_Favicon extends GDO_Module
{
	const FAVICON_WIDTH = 48;
	const FAVICON_HEIGHT = 48;
	
	const APPLE_TOUCH_WIDTH = 180;
	const APPLE_TOUCH_HEIGHT = 180;
	
	public function onLoadLanguage()
	{
		return $this->loadLanguage('lang/favicon');
	}
	
	public function getConfig()
	{
	    return [
			GDT_ImageFile::make('favicon')->
	    		previewHREF(href('Favicon', 'Image', '&variant=favicon'))->
	    		minWidth(16)->maxWidth(512)->
	    		minHeight(16)->maxHeight(512)->
		    	scaledVersion('favicon', self::FAVICON_WIDTH, self::FAVICON_HEIGHT, GDT_Image::PNG)->
		    	scaledVersion('appletouch', self::APPLE_TOUCH_WIDTH, self::APPLE_TOUCH_HEIGHT, GDT_Image::PNG),
	    ];
	}
	
	/**
	 * @return GDO_File
	 */
	public function cfgFavicon() { return $this->getConfigValue('favicon'); }
	
	######################
	### Convert to ICO ###
	######################
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
		if (!($file = $this->cfgFavicon()))
		{
			throw new GDOError('err_file_not_found');
		}
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
		$ico->add_image($file->getPath(), [self::FAVICON_WIDTH, self::FAVICON_HEIGHT]);
		$ico->save_ico('favicon.ico');
	}
	
	###############
	### Include ###
	###############
	public function onIncludeScripts()
	{
		if ($image = $this->cfgFavicon())
		{
			$v = $image->getID();
			$root = GDO_WEB_ROOT;
			$href = href('Favicon', 'Image');
			Website::addHead(sprintf('<link rel="shortcut icon" href="%sfavicon.ico?v=%s" type="image/x-icon" />', $root, $v));
			Website::addHead(sprintf('<link rel="icon" type="image/png" href="%s" />', $href.'?variant=favicon&v='.$v));
			Website::addHead(sprintf('<link rel="apple-touch-icon" href="%s" />', $href.'?variant=appletouch&v='.$v));
			Website::addHead(sprintf('<meta property="og:image" content="%s" />', $href."?v={$v}"));
		}
	}
	
	public function hookIgnoreDocsFiles(GDT_Array $ignore)
	{
	    $ignore->data[] = 'GDO/Favicon/php-ico/**/*';
	}
	
}
