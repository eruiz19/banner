<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Gradi_Adsense extends Module
{
	private $templateFile;

	public function __construct()
	{
	    $this->name = 'gradi_adsense';
	    $this->tab = 'front_office_features';
	    $this->version = '1.0.0';
	    $this->author = 'Ender Ruiz';
	    $this->need_instance = 0;
	    $this->ps_versions_compliancy = [
	    'min' => '1.7.1.0',
	    'max' => _PS_VERSION_
	    ];
	    $this->bootstrap = true;

	    parent::__construct();

	    $this->displayName = 'Gradi Adsense';
	    $this->description = 'Muestra un banner en tu sitio web.';

	    $this->confirmUninstall = $this->l('Estás seguro de que quieres desinstalar el módulo?');

	    
	    $this->templateFile = 'module:gradi_adsense/views/templates/hook/default.tpl';
	}

	public function install()
	{

	    if (!parent::install() ||
        !$this->registerHook('displayHome') ||
        !$this->registerHook('header') ||
        !Configuration::updateValue('hst_banner', 'local.png'))
        {
        return false;
        }
        return true;
	}

	public function uninstall()
	{

 	    if (!parent::uninstall() ||
        !Configuration::deleteByName('hst_banner') ||
  		!Configuration::deleteByName('hst_text') ||
  		!Configuration::deleteByName('hst_text1') ||
  		!Configuration::deleteByName('hst_text2') ||
  		!Configuration::deleteByName('hst_text3') ||
  		!Configuration::deleteByName('active')
  		) 
        {
        return false;
        }
        return true;
	}

	public function getContent()
	{
		$output = null;
 
 		if (Tools::isSubmit('submit'.$this->name)) {
			  $hstbanner = strval(Tools::getValue('hst_banner'));
			  $hsttext = strval(Tools::getValue('hst_text'));
			  $hsttext1 = strval(Tools::getValue('hst_text1'));
			  $hsttext2 = strval(Tools::getValue('hst_text2'));
			  $hsttext3 = strval(Tools::getValue('hst_text3'));

	        if (!$hstbanner ||  empty($hstbanner) || !Validate::isGenericName($hstbanner)){
	        $output .= $this->displayError($this->l('banner no actualizado'));
	        }else{     
	   
	           
	        if(isset($_FILES['hst_banner'])){
	   
	    
	        $imagen=$_FILES['hst_banner'];
	        $output .= $this->cargarImg($imagen, 'hst_banner', $hstbanner); 
	    

	        }
	       }
 		}

 		return $output.$this->postProcess().$this->getForm();
	}

	public function cargarImg($filee, $valor, $input)
	{
 		if ($filee != "") {
  			$allowed= array('image/gif','image/jpeg','image/jpg','image/png');
  			if (in_array($filee['type'],$allowed)) {
   
   			Configuration::updateValue($valor, $input); 
   			$destino='../modules/gradi_adsense/views/img/';

   			list($width,$height)= getimagesize($filee['tmp_name']);
   			$propor=400/$width;
   			$copy= ImageManager::resize($filee['tmp_name'],$destino.$filee['name'],400,400,$filee['type']);

  			$this->displayConfirmation($this->l('Banner Actualizado'));
   			if (!$copy) {
    		$salida .= $this->displayError($this->l('error moviendo archivo'));
   			}else{
   
    			$salida .= $this->displayConfirmation($this->l('Banner Actualizado'));
			}

  			}else{
   				$salida .= $this->displayError($this->l('formato de imagen no válido'));
  			}
 		}
 
		return $salida;
	}


	public function getForm()
	{
		$helper = new HelperForm();
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->identifier = $this->identifier;
		$helper->languages = $this->context->controller->getLanguages();
		$helper->default_form_language = $this->context->controller->default_form_language;
		$helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
		$helper->title = $this->displayName;

		$helper->submit_action = 'submit'.$this->name;

		$fieldsForm[0]['form'] = [
		  'legend' => [
		   'title' => $this->l('Configuraciones'),
		  ],
		  'input' => [
		   [   'type' => 'file',
		   'label' => $this->trans('Imagen de banner', array(), 'Modules.Banner.Admin'),
		   'desc' => $this->trans('Sube una imagen para tu banner. Las dimensiones recomendadas son 214px x 214px si está utilizando el tema predeterminado.', array(), 'Modules.Banner.Admin'),
		   'name' => 'hst_banner',
		   'size' => 20
		  ],
		  [
		   'type' => 'text',
		   'lang' => false,
		   'label' => $this->l('Título'),
		   'name' => 'hst_text',
		   'size' => 20,
		   'required' => true
		  ],
		 [
		  'type' => 'text',
		  'lang' => false,
		  'label' => $this->l('Descripción'),
		  'name' => 'hst_text1',
		  'size' => 20,
		  'required' => true
		 ],
		 [
		  'type' => 'text',
		  'label' => $this->l('CTA'),
		  'name' => 'hst_text2',
		  'size' => 20,
		  'required' => true
		 ],
		 [
		  'type' => 'text',
		  'lang' => false,
		  'label' => $this->l('Url'),
		  'name' => 'hst_text3',
		  'size' => 20,
		  'required' => true
		 ],
		 [
		   'type' => 'switch',
			'label' => $this->trans('Displayed', array(), 'Admin.Global'),
			'name' => 'active',
			'required' => false,
			'is_bool' => true,
			'values' => [
		    	[
		        'id' => 'active_on',
		        'value' => 1,
		        'label' => $this->trans('Enabled', array(), 'Admin.Global')
		    	],
		    	[
		        'id' => 'active_off',
		        'value' => 0,
		        'label' => $this->trans('Disabled', array(), 'Admin.Global')
		    	],
			]
		 ]
		],
		'submit' => [
		 'title' => $this->l('Guardar'),
		 'class' => 'btn btn-default pull-right'
		]
		];

		$helper->fields_value['hst_banner'] = Configuration::get('hst_banner');
    	$helper->fields_value['hst_text'] = Configuration::get('hst_text');
    	$helper->fields_value['hst_text1'] = Configuration::get('hst_text1');
    	$helper->fields_value['hst_text2'] = Configuration::get('hst_text2');
    	$helper->fields_value['hst_text3'] = Configuration::get('hst_text3');
    	$helper->fields_value['active'] = Configuration::get('active');

		return $helper->generateForm($fieldsForm);
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submit'.$this->name)) {
			
			$hst_text = Tools::getValue('hst_text');
   			$hst_text1 = Tools::getValue('hst_text1');
   			$hst_text2 = Tools::getValue('hst_text2');
   			$hst_text3 = Tools::getValue('hst_text3');
   			$active = Tools::getValue('active');

   			Configuration::updateValue('hst_text', $hst_text);
  			Configuration::updateValue('hst_text1', $hst_text1);
  			Configuration::updateValue('hst_text2', $hst_text2);
  			Configuration::updateValue('hst_text3', $hst_text3);
  			Configuration::updateValue('active', $active);

  			return $this->displayConfirmation($this->l('Proceso completado'));
		}
	}

	public function hookDisplayHome()
    { 

	    $valor=Configuration::get('hst_banner');
	    $text = Configuration::get('hst_text');
	    $text1 = Configuration::get('hst_text1');
	    $text2 = Configuration::get('hst_text2');
	    $text3 = Configuration::get('hst_text3');

	    $this->smarty->assign('text',$text);
	    /*$this->smarty->assign('text1', $text1);
	    $this->smarty->assign('text2', $text2);
	    $this->smarty->assign( 'text3',$text3);*/


	    
	    if ($valor!= null){  
	     $this->smarty->assign('valor',$valor);

	     return $this->display(__FILE__,'default.tpl');
	    }
    }

    public function hookHeader()
    {
	    //$this->context->controller->addJS($this->_path.'/views/js/front.js');
	    $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}