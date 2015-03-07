<?php

class Linko_Image
{
    private $_sFile;

    private $_iHeight = 0;

    private $_iWidth = 0;

    private $_sType;

    private $_sMime;

    private $_aInfo = array();

    private $_rImage;

    public function __construct($sFile = null)
    {
        if($sFile)
        {
            $this->load($sFile);
        }
    }

    public function load($sFile)
    {
        $this->_sFile = realpath($sFile);

        $aInfo = @getimagesize($sFile);

        if(!count($aInfo))
        {
            return false;
        }

        $this->_iWidth = $aInfo[0];

        $this->_iHeight = $aInfo[1];

        $this->_sMime = $aInfo['mime'];

        $this->_rImage = $this->_create();

        return $this;
    }

    public function setWidth($iWidth)
    {
        $this->_iWidth = $iWidth;

        return $this;
    }

    public function getWidth()
    {
        $this->_iWidth;
    }

    public function setHeight($iHeigth)
    {
        $this->_iHeight = $iHeigth;

        return $this;
    }

    public function getHeight()
    {
        return $this->_iHeight;
    }

    /**
     * Creates a new image
     *
     * @param int $iWidth
     * @param int $iHeight
     * @param string $sColor
     *
     * @return resource
     */
    public function createImage($iWidth, $iHeight, $sColor = 'ffffff')
    {
        $rImage = imagecreatetruecolor($iWidth, $iHeight);

        imagesavealpha($rImage, true);

        $iColor = imagecolorallocatealpha($rImage, 0, 0, 0, 127);

        imagefill($rImage, 0, 0, $iColor);

        return $rImage;
    }

    /**
     * Crops an image
     *
     * @param int $iOffsetX start point from x-axis
     * @param int $iOffsetY start point from y-axis
     * @param int $iWidth width of cropped image starting from x axis
     * @param int $iHeight height of cropped image starting from y axis
     * @return Linko_Image
     */
    public function crop($iOffsetX, $iOffsetY, $iWidth, $iHeight)
    {
        if($this->_rImage)
        {
            $rTmp = $this->createImage($iWidth, $iHeight, $this->_sMime);

            imagecopy($rTmp, $this->_rImage, 0, 0, $iOffsetX, $iOffsetY, $this->_iWidth, $this->_iHeight);

            imagedestroy($this->_rImage);

            $this->_rImage = $rTmp;

            $this->setWidth(imagesx($this->_rImage));

            $this->setHeight(imagesy($this->_rImage));
        }

        return $this;
    }

    /**
     * Resizes an image
     *
     * @param int $iWidth New width
     * @param int $iHeight New Height
     * @param bool $bRatio Maintain Ratio
     * @return Linko_Image
     */
    public function resize($iWidth, $iHeight, $bRatio = true)
    {
        if($bRatio)
        {
            $iRatio = max($iWidth/$this->_iWidth, $iHeight/$this->_iHeight);

            $iWidth = $this->_iWidth * $iRatio;

            $iHeight = $this->_iHeight * $iRatio;
        }

        $rImage = $this->createImage($iWidth, $iHeight, $this->_sMime);

        if(imagecopyresampled($rImage, $this->_rImage, 0, 0, 0, 0, $iWidth, $iHeight, $this->_iWidth, $this->_iHeight))
        {
            imagedestroy($this->_rImage);

            $this->_rImage = $rImage;

            $this->setWidth($iWidth);

            $this->setHeight($iHeight);
        }

        return $this;
    }

    public function rotate($iAngle)
    {
        $iAngle = $iAngle < 0 ? 0 : ($iAngle > 360 ? 360 : $iAngle);

        // if the angle is 0 or 360, keep the old image resource
        if($iAngle == 0 || $iAngle == 360)
        {
            $rImage = null;
        }
        else
        {
            if(function_exists('imagerotate'))
            {
                $rImage = imagerotate($this->_rImage, -$iAngle, -1);
            }
            else
            {

            }
        }

        if($rImage)
        {
            imagedestroy($this->_rImage);

            $this->_rImage = $rImage;

            $this->setWidth(imagesx($this->_rImage));

            $this->setHeight(imagesy($this->_rImage));
        }

        return $this;
    }

    /**
     * Flips an image vertically or horizontally
     *
     * @param string $sType Flip type (vertical|horizontal)
     *
     * @return Linko_Image
     */
    public function flip($sType = 'horizontal')
    {
        $rImage = $this->createImage($this->_iWidth, $this->_iHeight, $this->_sMime);

        if($sType == 'horizontal')
        {
            imagecopyresampled($rImage, $this->_rImage, 0, 0, $this->_iWidth - 1, 0, $this->_iWidth, $this->_iHeight, -$this->_iWidth, $this->_iHeight);
        }
        else
        {
            imagecopyresampled($rImage, $this->_rImage, 0, 0, 0, $this->_iHeight - 1, $this->_iWidth, $this->_iHeight, $this->_iWidth, -$this->_iHeight);
        }


        if($rImage)
        {
            imagedestroy($this->_rImage);

            $this->_rImage = $rImage;

            $this->setWidth(imagesx($this->_rImage));

            $this->setHeight(imagesy($this->_rImage));
        }

        return $this;
    }

    public function watermark($sImage, $sPosition = 'bottom_left')
    {
        return $this;
    }

    /**
     * Saves/Outputs an image
     * If not destination is passed, the image is outputed on the browser.
     * You may have to set proper headers to display the image on the browser.
     *
     * @param null $sDestination save directory
     * @param int $iQuality image quality (0 - 100)
     */
    public function save($sDestination = null, $iQuality = 100)
    {
        if($this->_rImage)
        {
            switch($this->_sMime)
            {
                case 'image/png':
                    imagepng($this->_rImage, $sDestination, ceil($iQuality * 0.09));
                    break;
                case 'image/jpg':
                case 'image/jpeg':
                    imagejpeg($this->_rImage, $sDestination, $iQuality);
                    break;
                case 'image/gif':
                    imagegif($this->_rImage, $sDestination);
                    break;
            }

            imagedestroy($this->_rImage);
        }
    }

    /**
     * Reset all manipulation on image/Rebuilds the image from source
     *
     * @return Linko_Image
     */
    public function reset()
    {
        if(is_resource($this->_rImage))
        {
            imagedestroy($this->_rImage);
        }

        $this->_sType =
        $this->_sMime =
        $this->_rImage = null;
        $this->_iHeight =
        $this->_iWidth = 0;
        $this->_aInfo = array();

        $this->load($this->_sFile);

        return $this;
    }

    /**
     * Returns the source image resource
     *
     * @return resource
     */
    protected function _create()
    {
        switch($this->_sMime)
        {
            case 'image/png':
                return imagecreatefrompng($this->_sFile);
                break;
            case 'image/gif':
                return imagecreatefromgif($this->_sFile);
                break;
            case 'image/jpeg':
                return imagecreatefromjpeg($this->_sFile);
                break;
            default:
                break;
        }
    }
}