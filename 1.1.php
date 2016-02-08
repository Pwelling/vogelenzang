<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');

/**
 * @classDescription  Class om de data uit het cms weer te geven in de site
 * @author Patrick
 * @version 1.1
 * @name pwcms
 * 
 */

include 'config.php';
class pwcms{
	var $versie = '1.1';
	var $db;
	var $pw;
	var $user;
	var $conn;
	var $eol = "\n";
  var $siteonderdeel = 1;
  var $onderdeelnaam = '';
	/*
	 * de paginavariabelen
	 */
	var $pagina_id;
	var $paginagroep_id;
	var $paginagroep_tpl = false;
	
	/*
	 * de nieuwsvariabelen
	 */
	var $nieuws_id;
	var $nieuwsgroep_id;
	var $nieuwsgroep_tpl = false;
	/*
	 * De menu variabelen
	 */
	var $menu_tpl = false;
	var $menu_id = '';
  /*
   * algemene setters
   */
   
   /**
    * \brief Zet het siteonderdeel
    */
   function set_siteonderdeel($val){
     $this->siteonderdeel = $val;
     $this->select_db();
     $query_rs_onderdeel = sprintf("SELECT onderdeelnaam FROM cms_onderdelen WHERE onderdeel_id=%s",
                        $this->GetSQLValueString($val,"int"));
     $rs_onderdeel = mysql_query($query_rs_onderdeel,$this->conn);
     $row_rs_onderdeel = mysql_fetch_assoc($rs_onderdeel);
     $totalRows_rs_onderdeel = mysql_num_rows($rs_onderdeel);
     if($totalRows_rs_onderdeel == 1){
       $this->onderdeelnaam = $row_rs_onderdeel['onderdeelnaam'];
     }
     mysql_free_result($rs_onderdeel);
   }
  
  
	/*
	 * de paginasetters
	 */
	
	/**
	 * \brief zet het pagina_id
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * @param $val
	 */
	function set_pagina_id($val){
		$this->pagina_id = $val;
	}
	
	/**
	 * \brief zet de paginagroep
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * @param $val
	 */
	function set_paginagroep_id($val){
		$this->paginagroep_id = $val;
	}
	
	/**
	 * \brief zet de template voor het overzicht van een paginagroep
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * @param $val
	 */
	function set_paginagroep_tpl($val){
		$this->paginagroep_tpl = $val;
	}
	
	/**
	 * \brief zet de groep en pagina
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::set_paginagroep_id()
	 * - pwcms::set_pagina_id()
	 */
	function get_pagina_id_fromDB(){
		$reeturn = 0;
		if(!isset($_GET['groep_id'])){
			$this->select_db();
			$query_rs_pagina_groep = "SELECT groep_id FROM cms_pagina_groepen Order BY volgnr ASC LIMIT 1";
			$rs_pagina_groep = mysql_query($query_rs_pagina_groep,$this->conn);
			$row_rs_pagina_groep = mysql_fetch_assoc($rs_pagina_groep);
			$totalRows_rs_pagina_groep = mysql_num_rows($rs_pagina_groep);
			if($totalRows_rs_pagina_groep == 1){
				$this->set_paginagroep_id($row_rs_pagina_groep['groep_id']);
			} else {
				$this->set_paginagroep_id(0);
			}
			mysql_free_result($rs_pagina_groep);
		} else {
			$this->set_paginagroep_id($_GET['groep_id']);
		}
		if(!isset($_GET['pagina_id'])){
			$this->select_db();
			$query_rs_pagina_id = sprintf("SELECT pagina_id FROM cms_pagina_inhoud WHERE groep_id=%s ORDER BY volgnr ASC LIMIT 1",
							$this->GetSQLValueString($this->paginagroep_id,"int"));
			$rs_pagina_id = mysql_query($query_rs_pagina_id,$this->conn);
			$row_rs_pagina_id = mysql_fetch_assoc($rs_pagina_id);
			$totalRows_rs_pagina_id = mysql_num_rows($rs_pagina_id);
			if($totalRows_rs_pagina_id == 1){
				$this->set_pagina_id($row_rs_pagina_id['pagina_id']);
			} else {
				$this->set_pagina_id(0);
			}
		} else {
			$this->set_pagina_id($_GET['pagina_id']);
		}
	}
	
	/*
	 * de nieuws_setters
	 */
	
	
	/**
	 * \brief Zet de gebruikte nieuwsgroep
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * @param $val
	 */
	function set_nieuwsgroep_id($val){
		$this->nieuwsgroep_id = $val;
	}
	
	/**
	 * \brief Zet het nieuwsitem_id
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * @param $val
	 */
	function set_nieuws_id($val){
		$this->nieuws_id = $val;
	}
	
	function set_nieuwsgroep_tpl($val){
		$this->nieuwsgroep_tpl = $val;
	}
	/*
	 * De menu setters
	 */
	/**
   * \brief Zet de template gebruikt voor het sitemenu
   * 
   * @author Patrick Welling
   * 
   * @since 1.1
   */
	function set_menu_tpl($val){
		$this->menu_tpl = $val;
	}
	
  /**
   * \brief zet het menu_id dat actief moet zijn
   * 
   * @author Patrick Welling
   * 
   * @since 1.1
   */
  function set_menu_id($val){
    $this->menu_id = $val;
  }
  
	/*
	 * algemene functies
	 */
	
	/**
	 * \brief maakt de verbinding met de database
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 */
	function connect_db(){
		$this->db = set_db();
		$this->pw = set_pw();
		$this->user = set_user();
		$this->conn = mysql_pconnect('localhost', $this->user, $this->pw) or trigger_error(mysql_error(),E_USER_ERROR);
	}
	
	/**
	 * \brief selecteert de database
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::connect_db()
	 */
	function select_db(){
		$this->connect_db();
		mysql_select_db($this->db,$this->conn);
	}
	
	/**
	 * de welbekende dreamweaver functie....
	 */
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
		$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);
		switch ($theType) {
			case "text":
				$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				break;    
			case "long":
			case "int":
				$theValue = ($theValue != "") ? intval($theValue) : "NULL";
				break;
			case "double":
				$theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
				break;
			case "date":
				$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				break;
			case "defined":
				$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
				break;
		}
		return $theValue;
	}
	
	/**
	 * \brief Geeft een array leesbaar terug
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 */
	function pre($val){
		$return = '<pre>'.$this->eol;
		$return .= print_r($val,true);
		$return .= '</pre>'.$this->eol;
		return $return;
	}
	
	/*
	 * de paginafuncties
	 */
	
	/**
	 * \brief haalt de paginatitel op uit de database
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * @uses select_db()
	 * @uses GetSQLValueString()
	 * @return text
	 */
	function pagina_titel(){
		$this->select_db();
		$query_rs_titel = sprintf("SELECT titel FROM cms_pagina_inhoud WHERE pagina_id=%s AND groep_id=%s",
								$this->GetSQLValueString($this->pagina_id,"int"),
								$this->GetSQLValueString($this->paginagroep_id,"int"));
		$rs_titel = mysql_query($query_rs_titel,$this->conn);
		$row_rs_titel = mysql_fetch_assoc($rs_titel);
		$totaRows_rs_titel = mysql_num_rows($rs_titel);
		if($totaRows_rs_titel == 1){
			return $row_rs_titel['titel'];
		} else {
			return 'Pagina niet gevonden';
		}
		mysql_free_result($rs_titel);
	}

	/**
	 * \brief Haalt de inleiding van de pagina uit de database en geeft deze terug.
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function pagina_inleiding(){
		$this->select_db();
		$query_rs_inleiding = sprintf("SELECT inleiding FROM cms_pagina_inhoud WHERE pagina_id=%s AND groep_id=%s",
								$this->GetSQLValueString($this->pagina_id,"int"),
								$this->GetSQLValueString($this->paginagroep_id,"int"));
		$rs_inleiding = mysql_query($query_rs_inleiding,$this->conn);
		$row_rs_inleiding = mysql_fetch_assoc($rs_inleiding);
		$totaRows_rs_inleiding = mysql_num_rows($rs_inleiding);
		if($totaRows_rs_inleiding == 1){
			return $row_rs_inleiding['inleiding'];
		}
		mysql_free_result($rs_inleiding);
	}

	/**
	 * \brief Haalt de tekst van de pagina uit de database en geeft deze terug.
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function pagina_tekst(){
		$this->select_db();
		$query_rs_tekst = sprintf("SELECT tekst FROM cms_pagina_inhoud WHERE pagina_id=%s AND groep_id=%s",
								$this->GetSQLValueString($this->pagina_id,"int"),
								$this->GetSQLValueString($this->paginagroep_id,"int"));
		$rs_tekst = mysql_query($query_rs_tekst,$this->conn);
		$row_rs_tekst = mysql_fetch_assoc($rs_tekst);
		$totaRows_rs_tekst = mysql_num_rows($rs_tekst);
		if($totaRows_rs_tekst == 1){
			return $row_rs_tekst['tekst'];
		}
		mysql_free_result($rs_tekst);
	}
	
	/**
	 * \brief Toont het overzicht van een paginagroep
	 * 
	 * @author P.welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 */
	function paginagroep_overzicht(){
		$return = '';
		
		$this->select_db();
		$query_rs_groep = sprintf("SELECT groepsnaam,omschrijving FROM cms_pagina_groepen WHERE groep_id=%s LIMIT 1",
					$this->GetSQLValueString($this->paginagroep_id,"int"));
		$rs_groep = mysql_query($query_rs_groep,$this->conn);
		$row_rs_groep = mysql_fetch_assoc($rs_groep);
		$totalRows_rs_groep = mysql_num_rows($rs_groep);

		
		$this->select_db();
		$query_rs_paginas = sprintf("SELECT * FROM cms_pagina_inhoud where groep_id=%s AND actief=1 ORDER BY volgnr ASC",
					$this->GetSQLValueString($this->paginagroep_id,"int"));
		$rs_paginas = mysql_query($query_rs_paginas,$this->conn);
		$row_rs_paginas = mysql_fetch_assoc($rs_paginas);
		$totalRows_rs_paginas = mysql_num_rows($rs_paginas);
		
		if($this->paginagroep_tpl != false){
			$template = file_get_contents($this->paginagroep_tpl);
			$arr_template = explode('<--break-->',$template);
			$header = $arr_template[0];
			$content = $arr_template[1];
			$footer = $arr_template[2];
			
			$header = str_replace('[groepsnaam]',$row_rs_groep['groepsnaam'],$header);
			$header = str_replace('[omschrijving]',$row_rs_groep['omschrijving'],$header);
			$return .= $header;
			
			if($totalRows_rs_paginas>0){
				do {
					$tmp = str_replace('[titel]',$row_rs_paginas['titel'],$content);
					$titel = str_replace(' ','_',$row_rs_paginas['titel']);
					$url = '/pagina/'.$titel.'/'.$this->paginagroep_id.'/'.$row_rs_paginas['pagina_id'].'/'.$this->menu_id.'/';
					$tmp = str_replace('[url]',$url,$tmp);
					$tmp = str_replace('[korte_inleiding]',nl2br(substr(strip_tags($row_rs_paginas['inleiding']),0,70)).'...',$tmp);
					
					$return .= $tmp;
				}while(($row_rs_paginas = mysql_fetch_assoc($rs_paginas))!=false);
			} else {
				$return .= '<tr>'.$this->eol;
				$return .= '<td>Geen paginas beschikbaar</td>'.$this->eol;
				$return .= '</tr>'.$this->eol;
				
			}
			
			$return .= $footer;
		}
		mysql_free_result($rs_paginas);
		mysql_free_result($rs_groep);
		return $return;
	}

	/*
	 * Nieuws
	 */
	
	/**
	 * \brief Maakt het overzicht van de actieve nieuwsberichten aan
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function nieuws_overzicht(){
		$return = '';
		$vandaag = date('Y-m-d 00:00:01');
		$vandaag2 = date('Y-m-d 23:59:59');
		$groepen = array();
		/*
		 * De nieuwsgroepen ophalen
		 */
		$this->select_db();
		$query_rs_nieuwsgroepen = "SELECT * FROM cms_nieuws_groepen WHERE actief='1' ORDER BY volgnr ASC";
		$rs_nieuwsgroepen = mysql_query($query_rs_nieuwsgroepen,$this->conn);
		$row_rs_nieuwsgroepen = mysql_fetch_assoc($rs_nieuwsgroepen);
		$totalRows_rs_nieuwsgroepen = mysql_num_rows($rs_nieuwsgroepen);
		if($totalRows_rs_nieuwsgroepen > 0){
			do{
				/*
				 * Per groep kijken of er actieve items zijn
				 */
				$this->select_db();
				$query_rs_nieuws_items = sprintf("SELECT titel,nieuws_id FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s AND op_site_van <= %s AND op_site_tot >= %s",
										$this->GetSQLValueString($row_rs_nieuwsgroepen['nieuwsgroep_id'],"int"),
										$this->GetSQLValueString($vandaag,"date"),
										$this->GetSQLValueString($vandaag2,"date"));
				$rs_nieuws_items = mysql_query($query_rs_nieuws_items,$this->conn) or die(mysql_error());
				$row_rs_nieuws_items = mysql_fetch_assoc($rs_nieuws_items);
				$totalRows_rs_nieuws_items = mysql_num_rows($rs_nieuws_items);
				if($totalRows_rs_nieuws_items > 0){
					$groep = $row_rs_nieuwsgroepen['nieuwsgroep_id'];
					$groepen[$groep] = array();
					$groepen[$groep]['groepsnaam'] = $row_rs_nieuwsgroepen['groepsnaam'];
					$groepen[$groep]['items'] = array();
					do {
						$groepen[$groep]['items'][$row_rs_nieuws_items['nieuws_id']] = $row_rs_nieuws_items['titel'];
					} while(($row_rs_nieuws_items = mysql_fetch_assoc($rs_nieuws_items))!=false);
					mysql_free_result($rs_nieuws_items);
				}
			}while(($row_rs_nieuwsgroepen = mysql_fetch_assoc($rs_nieuwsgroepen))!=false);
		}
		mysql_free_result($rs_nieuwsgroepen);
		if(count($groepen) == 0){
			/*
			 * Als er geen (actieve-)berichten zijn gevonden, dan een melding tonen dat er geen berichten zijn
			 */
			$return .= 'Er zijn geen nieuwsberichten gevonden';
		} else {
			$return .= '<ul class="nieuwsoverzicht_groepen">'.$this->eol;
			foreach($groepen AS $key=>$value){
				$return .= '<li class="overzicht_nieuwsgroeptitel">'.$this->eol;
				$return .= '<div>'.$groepen[$key]['groepsnaam'].'</div>'.$this->eol;
				$return .= '<ul class="nieuwsoverzicht_items">'.$this->eol;
				foreach($groepen[$key]['items'] as $item_id=>$item_titel){
					$return .= '<li class="overzicht_nieuwsitem_titel">';
					$return .= '<a href="/nieuwsbericht/'.$item_titel.'/'.$key.'/'.$item_id.'/'.$this->menu_id.'/" class="nieuwsitem_link">'.$item_titel.'</a>';
					$return .= '</li>'.$this->eol;
				}
				$return .= '</ul>'.$this->eol;
				$return .= '</li>'.$this->eol;
			}
			$return .= '</ul>'.$this->eol;
		}
		return $return;
	}
	
	/**
	 * \brief Bouwt het nieuwsblok op
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 */
	function nieuws_blok(){
		$return = '';
		$vandaag = date('Y-m-d 00:00:01');
		$vandaag2 = date('Y-m-d 23:59:59');
		
		$this->select_db();
		$query_rs_nieuwsitems = sprintf("SELECT titel, nieuws_id,nieuwsgroep_id FROM cms_nieuws_inhoud WHERE op_site_van <= %s AND op_site_tot >= %s ORDER BY nieuws_id DESC",
										$this->GetSQLValueString($vandaag,"date"),
										$this->GetSQLValueString($vandaag2,"date"));
		$rs_nieuwsitems = mysql_query($query_rs_nieuwsitems,$this->conn);
		$row_rs_nieuwsitems = mysql_fetch_assoc($rs_nieuwsitems);
		$totalRows_rs_nieuwsitems = mysql_num_rows($rs_nieuwsitems);
		
		if($totalRows_rs_nieuwsitems > 0){
			$arr_items = array();
			do {
				$arr_items[] = $row_rs_nieuwsitems;
				
			} while(($row_rs_nieuwsitems = mysql_fetch_assoc($rs_nieuwsitems))!= false);
			$aantal = count($arr_items);
			$arr_return = array();
			for($i=0;$i<$aantal;$i++){
				$arr_return[$i] = '<div class="nieuws_titel">'.$this->eol;
				$arr_return[$i] .= $arr_items[$i]['titel'].'<br />'.$this->eol;
				$arr_return[$i] .= '<a href="/nieuwsbericht/'.str_replace(' ','_',$arr_items[$i]['titel']).'/'.$arr_items[$i]['nieuwsgroep_id'].'/'.$arr_items[$i]['nieuws_id'].'/" class="meerlink">Lees meer....</a>'.$this->eol;
				$arr_return[$i] .= '</div>'.$this->eol;
			}
			$return .= implode('<div class="scheidingslijn"></div>',$arr_return);
		} else {
			$return = 'Geen nieuws gevonden';
		}
		return $return;
		mysql_free_result($rs_nieuwsitems);
	}
	
	/**
	 * \brief Haalt de titel van het gegeven nieuwsitem op uit de tabel en geeft deze terug
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function nieuws_item_titel(){
		$this->select_db();
		$query_rs_titel = sprintf("SELECT titel FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s AND nieuws_id=%s",
									$this->GetSQLValueString($this->nieuwsgroep_id,"int"),
									$this->GetSQLValueString($this->nieuws_id,"int"));
		$rs_titel = mysql_query($query_rs_titel,$this->conn);
		$row_rs_titel = mysql_fetch_assoc($rs_titel);
		$totalRows_rs_titel = mysql_num_rows($rs_titel);
		if($totalRows_rs_titel == 1){
			return $row_rs_titel['titel'];
		} else {
			return 'Het gezochte nieuwsitem is helaas niet gevonden';
		}
		mysql_fre_result($rs_titel);
	}
	
	/**
	 * \brief Haalt de inleiding van het gegeven nieuwsitem op uit de tabel en geeft deze terug
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function nieuws_item_inleiding(){
		$this->select_db();
		$query_rs_titel = sprintf("SELECT inleiding FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s AND nieuws_id=%s",
									$this->GetSQLValueString($this->nieuwsgroep_id,"int"),
									$this->GetSQLValueString($this->nieuws_id,"int"));
		$rs_titel = mysql_query($query_rs_titel,$this->conn);
		$row_rs_titel = mysql_fetch_assoc($rs_titel);
		$totalRows_rs_titel = mysql_num_rows($rs_titel);
		if($totalRows_rs_titel == 1){
			return $row_rs_titel['inleiding'];
		}
		mysql_free_result($rs_titel);
	}
	
	/**
	 * \brief Haalt de tekst van het gegeven nieuwsitem op uit de tabel en geeft deze terug
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function nieuws_item_tekst(){
		$this->select_db();
		$query_rs_titel = sprintf("SELECT tekst FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s AND nieuws_id=%s",
									$this->GetSQLValueString($this->nieuwsgroep_id,"int"),
									$this->GetSQLValueString($this->nieuws_id,"int"));
		$rs_titel = mysql_query($query_rs_titel,$this->conn);
		$row_rs_titel = mysql_fetch_assoc($rs_titel);
		$totalRows_rs_titel = mysql_num_rows($rs_titel);
		if($totalRows_rs_titel == 1){
			return $row_rs_titel['tekst'];
		}
		mysql_free_result($rs_titel);
	}
	
	/**
	 * \brief Haalt de bron van het gegeven nieuwsitem op uit de tabel en geeft deze terug
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function nieuws_item_bron(){
		$this->select_db();
		$query_rs_titel = sprintf("SELECT bron FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s AND nieuws_id=%s",
									$this->GetSQLValueString($this->nieuwsgroep_id,"int"),
									$this->GetSQLValueString($this->nieuws_id,"int"));
		$rs_titel = mysql_query($query_rs_titel,$this->conn);
		$row_rs_titel = mysql_fetch_assoc($rs_titel);
		$totalRows_rs_titel = mysql_num_rows($rs_titel);
		if($totalRows_rs_titel == 1){
			return $row_rs_titel['bron'];
		}
		mysql_free_result($rs_titel);
	}
	
	/**
	 * \brief Genereert het overzicht van de actieve nieuwsitems binnen de gegeven groep
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::GetSQLValueString()
	 */
	function nieuwsgroep_overzicht(){
		$return = '';
		$this->select_db();
		$query_rs_nieuwsgroep = sprintf("SELECT groepsnaam,omschrijving FROM cms_nieuws_groepen WHERE nieuwsgroep_id=%s LIMIT 1",
							$this->GetSQLValueString($this->nieuwsgroep_id,"int"));
		$rs_nieuwsgroep = mysql_query($query_rs_nieuwsgroep,$this->conn);
		$row_rs_nieuwsgroep = mysql_fetch_assoc($rs_nieuwsgroep);
		$totalRows_rs_nieuwsgroep = mysql_num_rows($rs_nieuwsgroep);
		
		if($totalRows_rs_nieuwsgroep == 1){
			$template = file_get_contents($this->nieuwsgroep_tpl);
			$arr_template = explode('<--break-->',$template);
			$header = $arr_template[0];
			$content = $arr_template[1];
			$footer = $arr_template[2];
			
			$header = str_replace('[titel]',$row_rs_nieuwsgroep['groepsnaam'],$header);
			$header = str_replace('[omschrijving]',nl2br($row_rs_nieuwsgroep['omschrijving']),$header);
			
			$this->select_db();
			$query_rs_items = "SELECT * FROM cms_nieuws_inhoud WHERE nieuwsgroep_id='".$this->nieuwsgroep_id."' AND op_site_van<=CURDATE() AND op_site_tot>=CURDATE() ORDER BY aanmaak_datum DESC";
			$rs_items = mysql_query($query_rs_items,$this->conn);
			$row_rs_items = mysql_fetch_assoc($rs_items);
			$totalRows_rs_items = mysql_num_rows($rs_items);
			$return .= $header;
		
			if($totalRows_rs_items > 0){
				do {
					$titel = str_replace(' ','_',$row_rs_items['titel']);
					$url = '/nieuwsbericht/'.$titel.'/'.$row_rs_items['nieuwsgroep_id'].'/'.$row_rs_items['nieuws_id'].'/'.$this->menu_id.'/';
					$tmp = str_replace('[url]',$url,$content);
					$tmp = str_replace('[titel]',$row_rs_items['titel'],$tmp);
					$tmp = str_replace('[korte_inleiding]',nl2br(substr($row_rs_items['inleiding'],0,70)).'...',$tmp);
					$return .= $tmp;
				} while(($row_rs_items = mysql_fetch_assoc($rs_items))!=false);
			} else {
				$return .= '<tr>'.$this->eol;
				$return .= '<td>Geen nieuwsitems beschikbaar</td>'.$this->eol;
				$return .= '</tr>'.$this->eol;
			}
			mysql_free_result($rs_items);
			$return .= $footer;
		} else {
			$return = 'Nieuwsgroep niet gevonden';
		}
		mysql_free_result($rs_nieuwsgroep);
		return $return;
	}
	
	/*
	 * het menu
	 */
	
	/**
	 * \brief Bouwt het menu op
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.1
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::menu_item_link()
	 */
	function menu(){
		$return = '';
		$template = file_get_contents($this->menu_tpl);
		$arr_template = explode('<--hoofdmenu-->',$template);
		$header = $arr_template[0];
		$content = $arr_template[1];
		$footer = $arr_template[2];
		/*
		 * Eerst de hoofditems ophalen
		 */
		$this->select_db();
		$query_rs_hoofditems = "SELECT * FROM cms_menu WHERE parent_id='-1' ORDER BY volgnr ASC";
		$rs_hoofditems = mysql_query($query_rs_hoofditems,$this->conn);
		$row_rs_hoofditems = mysql_fetch_assoc($rs_hoofditems);
		$totalRows_rs_hoofditems = mysql_num_rows($rs_hoofditems);
		
		if($totalRows_rs_hoofditems > 0){
			$return .= $header;
			do {
				/*
				 * de content opsplitsen voor de submenu items
				 */
				$menu_id = $row_rs_hoofditems['menu_id'];
				$arr_content = explode('<--submenu-->',$content);
				$hoofditem_header = $arr_content[0];
				$hoofditem_content = $arr_content[1];
				$hoofditem_footer = $arr_content[2];
				$tmp_hoofditem_header = str_replace('[url]',$this->menu_item_link($row_rs_hoofditems,$menu_id),$hoofditem_header);
				$tmp_hoofditem_header = str_replace('[titel]',$row_rs_hoofditems['titel'],$tmp_hoofditem_header);
        $tmp_hoofditem_header = str_replace('[menu_id]',$menu_id,$tmp_hoofditem_header);
				
				/*
				 * Kijken of er subitems zijn
				 */
				$this->select_db();
				$query_rs_subitems = "SELECT * FROM cms_menu WHERE parent_id='".$row_rs_hoofditems['menu_id']."' ORDER BY volgnr ASC";
				$rs_sub_items = mysql_query($query_rs_subitems,$this->conn);
				$row_rs_sub_items = mysql_fetch_assoc($rs_sub_items);
				$totalRows_rs_sub_items = mysql_num_rows($rs_sub_items);
        if($totalRows_rs_sub_items > 0){
          $tmp_hoofditem_header = str_replace('[class]','smenu',$tmp_hoofditem_header);
        } else {
          $tmp_hoofditem_header = str_replace('[class]','smenu2',$tmp_hoofditem_header);
        }
        
        $return .= $tmp_hoofditem_header;
				if($totalRows_rs_sub_items > 0){
					$arr_submenu = explode('<--submenuitems-->',$hoofditem_content);
					$subitem_header = $arr_submenu[0];
					$subitem_content = $arr_submenu[1];
					$subitem_footer = $arr_submenu[2];
					$subitem_header = str_replace('[volgnr]',$row_rs_hoofditems['volgnr'],$subitem_header);
          $subitem_header = str_replace('[menu_id]',$menu_id,$subitem_header);
					$return .= $subitem_header;
					do {
						$tmp_subitem_content = str_replace('[url]',$this->menu_item_link($row_rs_sub_items,$menu_id),$subitem_content);
						$tmp_subitem_content = str_replace('[titel]',$row_rs_sub_items['titel'],$tmp_subitem_content);
						$return .= $tmp_subitem_content;
					} while(($row_rs_sub_items = mysql_fetch_assoc($rs_sub_items))!=false);
					$return .= $subitem_footer;
				} 
				mysql_free_result($rs_sub_items);
				$return .= $hoofditem_footer;
			} while(($row_rs_hoofditems = mysql_fetch_assoc($rs_hoofditems))!=false);
			$return .= $footer;
		}
		mysql_free_result($rs_hoofditems);
		return $return;
	}
	
	/**
	 * \brief geeft de url van het menuitem terug
	 * 
	 * @author P.Welling
	 *
	 * @since 1.1
	 */
	function menu_item_link($data,$menu_id){
		switch($data['soort_item']){
			case 1: //paginagroep
				$this->select_db();
				$query_rs_paginagroep = "SELECT groepsnaam FROM cms_pagina_groepen WHERE groep_id='".$data['item_id']."'";
				$rs_paginagroep = mysql_query($query_rs_paginagroep,$this->conn);
				$row_rs_paginagroep = mysql_fetch_assoc($rs_paginagroep);
				$totalRows_rs_paginagroep = mysql_num_rows($rs_paginagroep);
				if($totalRows_rs_paginagroep == 1){
					$titel = str_replace(' ','_',$row_rs_paginagroep['groepsnaam']);
					$url = ' href="/paginaoverzicht/'.$titel.'/'.$data['item_id'].'/'.$menu_id.'/"';
				}
				mysql_free_result($rs_paginagroep);
				break;
			case 2: //paginaitem
				$this->select_db();
				$query_rs_pagina_item = "SELECT titel,groep_id FROM cms_pagina_inhoud WHERE pagina_id='".$data['item_id']."'";
				$rs_pagina_item = mysql_query($query_rs_pagina_item,$this->conn) or die(mysql_error());
				$row_rs_pagina_item = mysql_fetch_assoc($rs_pagina_item);
				$totalRows_rs_pagina_item = mysql_num_rows($rs_pagina_item);
				if($totalRows_rs_pagina_item == 1){
					$titel = str_replace(' ','_',$row_rs_pagina_item['titel']);
					$url = ' href="/pagina/'.$titel.'/'.$row_rs_pagina_item['groep_id'].'/'.$data['item_id'].'/'.$menu_id.'/"';
				}
				mysql_free_result($rs_pagina_item);
				break;
			case 3: //nieuwsgroep
				$this->select_db();
				$query_rs_nieuwsgroep = "SELECT groepsnaam FROM cms_nieuws_groepen WHERE nieuwsgroep_id='".$data['item_id']."'";
				$rs_nieuwsgroep = mysql_query($query_rs_nieuwsgroep,$this->conn);
				$row_rs_nieuwsgroep = mysql_fetch_assoc($rs_nieuwsgroep);
				$totalRows_rs_nieuwsgroep = mysql_num_rows($rs_nieuwsgroep);
				if($totalRows_rs_nieuwsgroep == 1){
					$titel = str_replace(' ','_',$row_rs_nieuwsgroep['groepsnaam']);
					$url = ' href="/nieuwsoverzicht/'.$titel.'/'.$data['item_id'].'/'.$menu_id.'/"';
				}
				mysql_free_result($rs_nieuwsgroep);
				break;
			case 4: //nieuwsitem
				$this->select_db();
				$query_rs_nieuwsitem = "SELECT titel,nieuwsgroep_id FROM cms_nieuws_inhoud WHERE nieuws_id='".$data['item_id']."'";
				$rs_nieuwsitem = mysql_query($query_rs_nieuwsitem,$this->conn);
				$row_rs_nieuwsitem = mysql_fetch_assoc($rs_nieuwsitem);
				$totalRows_rs_nieuwsitem = mysql_num_rows($rs_nieuwsitem);
				
				if($totalRows_rs_nieuwsitem == 1){
					$titel = str_replace(' ','_',$row_rs_nieuwsitem['titel']);
          $titel = str_replace('&','+',$row_rs_nieuwsitem['titel']);
					$url = ' href="/1nieuwsbericht/'.$titel.'/'.$row_rs_nieuwsitem['nieuwsgroep_id'].'/'.$data['item_id'].'/'.$menu_id.'/"';
				}
				mysql_free_result($rs_nieuwsitem);
				
				break;
			case 5: //eigen url
				$url = ' href="'.$data['url'].'"';
				break;
			case 6: //item zonder koppeling
				$url = ''; //leeg laten
				break;
		}
		return $url;
	}
	
	
	
	/**
	 * \brief hoofdfunctie voor het menu
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 *
	 * @deprecated 1.1
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::menu_item_details()
	 */
	function menu_items(){
		$return = '';
		$template = file_get_contents($this->menu_tpl);
		$arr_template = explode('<--break-->',$template);
		$header = $arr_template[0];
		$content = $arr_template[1];
		$footer = $arr_template[2];
		/*
		 * Eerst de hoofditems ophalen
		 */
		$this->select_db();
		$query_rs_hoofditems = "SELECT * FROM cms_menu WHERE parent_id='-1' ORDER BY volgnr ASC";
		$rs_hoofditems = mysql_query($query_rs_hoofditems,$this->conn);
		$row_rs_hoofditems = mysql_fetch_assoc($rs_hoofditems);
		$totalRows_rs_hoofditems = mysql_num_rows($rs_hoofditems);
		
		if($totalRows_rs_hoofditems > 0){
			$return .= $header;
			do {
				$return .= $this->menu_item_details($row_rs_hoofditems,$content,2,false);
			} while(($row_rs_hoofditems = mysql_fetch_assoc($rs_hoofditems))!=false);
			
			$return .= $footer;
		}
		mysql_free_result($rs_hoofditems);
		return $return;
	}
	
	/**
	 * \brief Maakt de details aan van het menu met evt subitems
	 * 
	 * @author Patrick Welling
	 * 
	 * @since 1.0
	 * 
	 * @deprecated 1.1
	 * 
	 * \b Gebruikt:
	 * - pwcms::select_db()
	 * - pwcms::menu_item_details()
	 */
	function menu_item_details($data,$template,$nr,$content_url){
		$return = '';
		
		$return .= $nr.'<br />';
		$return .= $template;
		$arr_template = explode('<--break'.$nr.'-->',$template);
		$header = $arr_template[0];
		$content = $arr_template[1];
		$footer = $arr_template[2];
		
		/*
		 * Eerst het item zelf opbouwen
		 */
		$url = '';
		switch($data['soort_item']){
			case 1: //paginagroep
				$this->select_db();
				$query_rs_paginagroep = "SELECT groepsnaam FROM cms_pagina_groepen WHERE groep_id='".$data['item_id']."'";
				$rs_paginagroep = mysql_query($query_rs_paginagroep,$this->conn);
				$row_rs_paginagroep = mysql_fetch_assoc($rs_paginagroep);
				$totalRows_rs_paginagroep = mysql_num_rows($rs_paginagroep);
				if($totalRows_rs_paginagroep == 1){
					$titel = str_replace(' ','_',$row_rs_paginagroep['groepsnaam']);
					$url = ' href="/paginaoverzicht/'.$titel.'/'.$data['item_id'].'/"';
				}
				mysql_free_result($rs_paginagroep);
				break;
			case 2: //paginaitem
				$this->select_db();
				$query_rs_pagina_item = "SELECT titel,groep_id FROM cms_pagina_inhoud WHERE pagina_id='".$data['item_id']."'";
				$rs_pagina_item = mysql_query($query_rs_pagina_item,$this->conn) or die(mysql_error());
				$row_rs_pagina_item = mysql_fetch_assoc($rs_pagina_item);
				$totalRows_rs_pagina_item = mysql_num_rows($rs_pagina_item);
				if($totalRows_rs_pagina_item == 1){
					$titel = str_replace(' ','_',$row_rs_pagina_item['titel']);
					$url = ' href="/pagina/'.$titel.'/'.$row_rs_pagina_item['groep_id'].'/'.$data['item_id'].'/"';
				}
				mysql_free_result($rs_pagina_item);
				break;
			case 3: //nieuwsgroep
				$this->select_db();
				$query_rs_nieuwsgroep = "SELECT groepsnaam FROM cms_nieuws_groepen WHERE nieuwsgroep_id='".$data['item_id']."'";
				$rs_nieuwsgroep = mysql_query($query_rs_nieuwsgroep,$this->conn);
				$row_rs_nieuwsgroep = mysql_fetch_assoc($rs_nieuwsgroep);
				$totalRows_rs_nieuwsgroep = mysql_num_rows($rs_nieuwsgroep);
				if($totalRows_rs_nieuwsgroep == 1){
					$titel = str_replace(' ','_',$row_rs_nieuwsgroep['groepsnaam']);
					$url = ' href="/nieuwsoverzicht/'.$titel.'/'.$data['item_id'].'/"';
				}
				mysql_free_result($rs_nieuwsgroep);
				break;
			case 4: //nieuwsitem
				$this->select_db();
				$query_rs_nieuwsitem = "SELECT titel,nieuwsgroep_id FROM cms_nieuws_inhoud WHERE nieuws_id='".$data['item_id']."'";
				$rs_nieuwsitem = mysql_query($query_rs_nieuwsitem,$this->conn);
				$row_rs_nieuwsitem = mysql_fetch_assoc($rs_nieuwsitem);
				$totalRows_rs_nieuwsitem = mysql_num_rows($rs_nieuwsitem);
				
				if($totalRows_rs_nieuwsitem == 1){
					$titel = str_replace(' ','_',$row_rs_nieuwsitem['titel']);
					$url = ' href="/nieuwsbericht/'.$titel.'/'.$row_rs_nieuwsitem['nieuwsgroep_id'].'/'.$data['item_id'].'/"';
				}
				mysql_free_result($rs_nieuwsitem);
				
				break;
			case 5: //eigen url
				$url = ' href="'.$data['url'].'"';
				break;
			case 6: //item zonder koppeling
				$url = ''; //leeg laten
				break;
		}
		$header = str_replace('[url]',$url,$header);
		$header = str_replace('[titel]',$data['titel'],$header);
		$return .= $header;
		/*
		 * Kijken of er subitems zijn
		 */
		$this->select_db();
		$query_rs_subitems = "SELECT * FROM cms_menu WHERE parent_id='".$data['menu_id']."' ORDER BY volgnr ASC";
		$rs_sub_items = mysql_query($query_rs_subitems,$this->conn);
		$row_rs_sub_items = mysql_fetch_assoc($rs_sub_items);
		$totalRows_rs_sub_items = mysql_num_rows($rs_sub_items);
		if($totalRows_rs_sub_items > 0){
			$content = str_replace('[volgnr]',$data['volgnr'],$content);
			do {
				$return .= $this->menu_item_details($row_rs_sub_items,$content,($nr+1),true);
			} while(($row_rs_sub_items = mysql_fetch_assoc($rs_sub_items))!=false);
		}
		if($content_url === true){
			$content = str_replace('[url]',$url,$content);
			$content = str_replace('[titel]',$data['titel'],$content);
			$return .= $content;
		}
		$return .= $footer;
		
		return $return;
	}

}
?>