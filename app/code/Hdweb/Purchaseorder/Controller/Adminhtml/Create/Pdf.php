<?php
 
namespace Hdweb\Purchaseorder\Controller\Adminhtml\Create;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Action;
use Magento\Store\Model\Store;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Pdf extends Action
{
    const XML_PATH_EMAIL_ADMIN_QUOTE_SENDER = 'emailcustom/general/sender';
    const XML_PATH_EMAIL_ADMIN_QUOTE_NOTIFICATION = 'emailcustom/general/template';
    const XML_PATH_EMAIL_ADMIN_EMAIL = 'emailcustom/general/reciver';


    protected $scopeConfig;
    protected $_modelStoreManagerInterface;
    protected $inlineTranslation;
    protected $_logLoggerInterface;
    protected $_transportBuilder;
    protected $_mediaDirectory;
    protected $fileFactory;
    protected $_rootDirectory;
    

    public function __construct(
        Context $context,
        StoreManagerInterface $modelStoreManagerInterface,
        ScopeConfigInterface $configScopeConfigInterface,
        StateInterface $inlineTranslation,
        LoggerInterface $logLoggerInterface,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        $this->scopeConfig = $configScopeConfigInterface;
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->inlineTranslation = $inlineTranslation;
        $this->_logLoggerInterface = $logLoggerInterface;
        $this->fileFactory = $fileFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
          $this->_rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        parent::__construct($context);
    }
    public function execute()
    {
        
        $pdf = new \Zend_Pdf();
        $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
        $page = $pdf->pages[0]; // this will get reference to the first page.
        $style = new \Zend_Pdf_Style();
        $style->setLineColor(new \Zend_Pdf_Color_Rgb(0,0,0));
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font,15);
        $page->setStyle($style);
        $width = $page->getWidth();
        $hight = $page->getHeight();
        $x = 30;
        $pageTopalign = 850; //default PDF page height
        $this->y = 850 - 150; //print table row from page top – 100px
        //Draw table header row’s
        $style->setFont($font,16);
        $page->setStyle($style);
        $page->drawRectangle(30, $this->y - 20, $page->getWidth()-30, $this->y +90, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $style->setFont($font,15);
        $page->setStyle($style);

      
         $imagePath = 'logo.png';
         $image="";
          if ($this->_mediaDirectory->isFile($imagePath)) {  
                $image = \Zend_Pdf_Image::imageWithPath($this->_mediaDirectory->getAbsolutePath($imagePath)); 

           }     
                   
                $y1 = 800;
                $y2 = 830;
                $x1 = 400;
                $x2 = 530;
                // echo   $x1.'--'.$x2.'--'.$y1.'--'.$y2;exit;
                //coordinates after transformation are rounded by Zend
                $page->drawImage($image, $x1, $y1, $x2, $y2);


        $page->drawText(__("Tyres Vision"), $x + 5, $this->y+70, 'UTF-8');
        $style->setFont($font,12);
        $page->setStyle($style);
        $page->drawText(__("Aspin Commercial Tower,"), $x + 5, $this->y+55, 'UTF-8');
        $page->drawText(__("20th floor, Sheikh Zayed Road,"), $x + 5, $this->y+40, 'UTF-8');
        $page->drawText(__("Dubai, UAE"), $x + 5, $this->y+40, 'UTF-8');
        $page->drawText(__("TRN: 0123456789"), $x + 5, $this->y+40, 'UTF-8');
        $page->drawText(__("Phone: 01 234 5678"), $x + 5, $this->y+25, 'UTF-8');
        $page->drawText(__("Website: www.tyresvision.com "), $x + 5, $this->y+10, 'UTF-8');

         //$page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        //$page->setLineWidth(0.5);
     

        // Purchase order
        $page->drawText(__("PURCHASE ORDER"), $x + 350, $this->y+70, 'UTF-8');
        $page->drawText(__("DATE"), $x + 350, $this->y+50, 'UTF-8');
        $page->drawText(__("PO/ORDER #"), $x + 350, $this->y+30, 'UTF-8');
        
        //Po value
        $page->drawText(__("6/9/12"), $x + 430, $this->y+50, 'UTF-8');
        $page->drawText(__("#232323"), $x + 430, $this->y+30, 'UTF-8');
        $page->drawText(__("23232323"), $x + 430, $this->y+10, 'UTF-8');
        
        // Vendor Detail
         $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
         //$page->setFillColor(\Zend_Pdf_Color_Html::color('#990000'));
         $page->drawRectangle(30, $this->y , $page->getWidth()-30, $this->y -30);
        //$page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        //$page->setLineWidth(0.5);
       
      //  $this->_setFontBold($page, 12);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font,14);
        $page->drawText(__('VENDOR'), 40,$this->y - 18, 'UTF-8');        
        $page->drawText(__('SHIP TO:'), 300, $this->y - 18, 'UTF-8');

         $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
         $page->drawRectangle(30, $this->y , $page->getWidth()-30, $this->y -140,\Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font,12);

        $page->drawText(__('Devvedra patel'), 40,$this->y - 50, 'UTF-8');        
        $page->drawText(__('T:34343434243'), 40,$this->y - 70, 'UTF-8');        
        $page->drawText(__('Email:test@gmail.com'), 40,$this->y - 90, 'UTF-8');        

        $page->drawText(__('Devvedra patel'), 300, $this->y -50, 'UTF-8');
        $page->drawText(__('Installer Address'), 300, $this->y -70, 'UTF-8');
        $page->drawText(__('Installer Contact Person'), 300, $this->y -90, 'UTF-8');
        $page->drawText(__('T:34343434243'), 300, $this->y -110, 'UTF-8');

        $page->drawText(__('Comment:'), 40, $this->y -170, 'UTF-8');
        $page->drawText(__('Test comment'), 100, $this->y -170, 'UTF-8');

        // iTems

        // Vendor Detail
         $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
         $page->drawRectangle(30, $this->y-200 , $page->getWidth()-30, $this->y-220);
         $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
         $style->setFont($font,14);
         $page->drawText(__('ITEM'), 40,$this->y - 215, 'UTF-8');        
         $page->drawText(__('DESCRIPTION'), 130, $this->y - 215, 'UTF-8');
         $page->drawText(__('QTY'), 320,$this->y - 215, 'UTF-8');        
         $page->drawText(__('UNIT PRICE'), 370, $this->y - 215, 'UTF-8');
         $page->drawText(__('TOTAL'), 500, $this->y - 215, 'UTF-8');

         //ITEM VALUE
         $style->setFont($font,12);
         $page->drawText(__('1'), 40,$this->y - 245, 'UTF-8');        
         $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 245, 'UTF-8');
         $page->drawText(__('2'), 330,$this->y - 245, 'UTF-8');        
         $page->drawText(__('AED 1200'), 370, $this->y - 245, 'UTF-8');
         $page->drawText(__('AED 3600'), 500, $this->y - 245, 'UTF-8');

        
         $page->drawText(__('2'), 40,$this->y - 270, 'UTF-8');        
         $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 270, 'UTF-8');
         $page->drawText(__('2'), 330,$this->y - 270, 'UTF-8');        
         $page->drawText(__('AED 1200'), 370, $this->y - 270, 'UTF-8');
         $page->drawText(__('AED 3600'), 500, $this->y - 270, 'UTF-8');

        
         $page->drawText(__('3'), 40,$this->y - 300, 'UTF-8');        
         $page->drawText(__('SP Sport Maxx 050+ '), 130, $this->y - 300, 'UTF-8');
         $page->drawText(__('2'), 330,$this->y - 300, 'UTF-8');        
         $page->drawText(__('AED 1200'), 370, $this->y - 300, 'UTF-8');
         $page->drawText(__('AED 3600'), 500, $this->y - 300, 'UTF-8');

         //subtotal

         $page->drawText(__('SUBTOTAL'), 400,$this->y - 330, 'UTF-8');        
         $page->drawText(__('AED 3600'), 500, $this->y - 330, 'UTF-8');

         $page->drawText(__('TAX'), 400,$this->y - 360, 'UTF-8');        
         $page->drawText(__('AED 3600'), 500, $this->y - 360, 'UTF-8');

         $page->drawText(__('GRAND TOTAL'), 400,$this->y - 390, 'UTF-8');        
         $page->drawText(__('AED 3600'), 500, $this->y - 390, 'UTF-8');

        // commment and instruction

         $page->drawRectangle(30, $this->y , $page->getWidth()-30, $this->y -140,\Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $style->setFont($font,12);
          
          $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
          $page->drawRectangle(30, $this->y-420 , $page->getWidth()-30, $this->y-440);
          $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
          $style->setFont($font,14);
          $page->drawText(__('COMMMENT OR SPECIAL INSTRUCTIONS'), 40,$this->y - 435, 'UTF-8');  

          $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
          $page->drawRectangle(30, $this->y-440 , $page->getWidth()-30, $this->y-510,\Zend_Pdf_Page::SHAPE_DRAW_STROKE);
          $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
          $style->setFont($font,12);

          $page->drawText(__('1. Please mention this order number in your invoices for this order.'), 40,$this->y - 460, 'UTF-8');        
          
          $page->drawText(__('2. Please notify us immediately if you are unable to ship as specified.'), 40,$this->y - 475, 'UTF-8');        
               
          $page->drawText(__('3. The tyre/s to be supplied under this purchase order must comply with UAE law and with standards  
            '), 40,$this->y - 490, 'UTF-8');

          $page->drawText(__('approved as Gulf Technical Regulations by GSO.'), 50,$this->y - 505, 'UTF-8');
          
          // footer

          $page->drawText(__('If you have any questions about this purchase order, please contact'), 150,$this->y - 540, 'UTF-8');
          $page->drawText(__('[Mr. Devendra, 0543473401 or Email: devendra.it@live.com]'), 130,$this->y - 560, 'UTF-8');


        $fileName = 'example.pdf';

        $this->fileFactory->create(
           $fileName,
           $pdf->render(),
           \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name example.pdf
           'application/pdf'
        );
   

    }
    

}