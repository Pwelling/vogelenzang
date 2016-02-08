<?php
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 'On');

/**
 * @classDescription  Class om de data uit het cms weer te geven in de site
 * @author P.Welling
 * @version 1.2
 * @name pwcms
 * 
 */

include 'config.php';
class pwcms{
	var $versie = '1.3';
	var $db;
	var $pw;
	var $user;
	var $conn;
	var $eol = "\n";
	var $siteonderdeel = 1;
	var $onderdeelnaam = '';
	var $maand;
	var $jaar;
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
	var $nieuws_agendering = false;
	/*
	 * De menu variabelen
	 */
	var $menu_tpl = false;
	var $menu_id = '';
	
	/*
	 * De agenda variabelen
	 */
	var $agenda_datum = '-1';
	var $agenda_item_overzicht_template = false;
	var $agenda_item = false;
	 
	/*
	 * De link-variabelen
	 */
	var $link_tpl = false;
	
	/*
	 * De advertentie-variabelen
	 */
	var $advertentie_template = '';
	var $advertentie_max = 1;
	
	/*
	 * De Album variabelen
	 */
	var $album_id;
	var $album = false;
	var $album_inhoud = array();
	var $album_template = false;
	/*
	 * De Album setters
	 */
	
	/**
	 * \brief Zet het albumid van het te tonen album
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 */
	function set_album_id($val){
		$this->album_id = $val;
	}
	
	/**
	 * \brief Haalt de gegevens van het gekozen album op en zet deze in de class-variable album
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 * 
	 * \b Gebruikt:
	 * - pwcsm::GetSQLValueString()
	 * - pwcms::select_db()
	 * - pwcms::set_album_inhoud()
	 */
	function set_album_data(){
		if($this->album === false){
			$this->select_db();
			$query_rs_album = sprintf("SELECT * FROM `cms_album` WHERE album_id=%s",
						$this->GetSQLValueString($this->album_id, 'int'));
			$rs_album = mysql_query($query_rs_album,$this->conn);
			$row_rs_album = mysql_fetch_assoc($rs_album);
			$totalRows_rs_album = mysql_num_rows($rs_album);
			if($totalRows_rs_album == 1){
				$this->album = $row_rs_album;
			}
			mysql_free_result($rs_album);
			$this->set_album_inhoud();
		}
	}
	
	/**
	 * \brief Plaatst de inhoud van het album in een array
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 * 
	 * \b Gebruikt:
	 * - pwcms::GetSQLValueString()
	 * - pwcms::select_klant_db()
	 */
	function set_album_inhoud(){
		if($this->album !== false){
			$this->select_db();
			$query_rs_album_inhoud = sprintf("SELECT * FROM cms_album_inhoud WHERE album_id=%s ORDER BY volgnr ASC",
						$this->GetSQLValueString($this->album_id, 'int'));
			$rs_album_inhoud = mysql_query($query_rs_album_inhoud,$this->conn);
			$row_rs_album_inhoud = mysql_fetch_assoc($rs_album_inhoud);
			$totalRows_rs_album_inhoud = mysql_num_rows($rs_album_inhoud);
			if($totalRows_rs_album_inhoud > 0){
				do {
					$this->album_inhoud[] = $row_rs_album_inhoud;
				} while(($row_rs_album_inhoud = mysql_fetch_assoc($rs_album_inhoud))!=false);
			}
			mysql_free_result($rs_album_inhoud);
		}
	}
	
	/**
	 * \brief Zewt de template te gebruiken voor het albumoverzicht
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 */
	function set_album_template($val){
		$this->album_template = $val;
	}
	
	/*
	 * algemene setters
	 */
	function pwcms(){
		$this->set_maand(date('n'));
		$this->set_jaar(date('Y'));
	}
	
	function set_maand($val){
		$this->maand = $val;
	}
	
	function set_jaar($val){
		$this->jaar = $val;
	}
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
	 * 
	 * @since 1.0
	 * 
	 * @param $val
	 */
	function set_nieuws_id($val){
		$this->nieuws_id = $val;
	}
	
	/**
	 * \brief Zet de template die gebruikt wordt voor hjet nieuwsoverzicht
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 */
	function set_nieuwsgroep_tpl($val){
		$this->nieuwsgroep_tpl = $val;
	}
  
	/**
	 * \brief Zet de agendering van het nieuwsoverzicht
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 */
	function set_nieuws_agendering($val){
		$this->nieuws_agendering = $val;
	}
	/*
	 * De menu setters
	 */
	/**
	 * \brief Zet de template gebruikt voor het sitemenu
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.1
	 */
	function set_menu_tpl($val){
		$this->menu_tpl = $val;
	}
	
	/**
	 * \brief zet het menu_id dat actief moet zijn
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.1
	 */
	function set_menu_id($val){
		$this->menu_id = $val;
	}
	
	/*
	 * Agenda setters
	 */
	 function set_agenda_datum($val){
	 	$this->agenda_datum = $val;
	 }
	 
	 function set_agenda_item_overzicht_template($val){
	 	$this->agenda_item_overzicht_template = $val;
	 }
	 
	 /**
	  * \brief Haalt de gegevens van het agenda-item op en plaatst deze in een algemene variabele. Bij geen resultaat, geeft deze false terug
	  * 
	  * @author P.Welling
	  * 
	  * @since 1.2
	  * 
	  * @param $val
	  */
	 function set_agenda_item($val){
	 	$this->select_db();
		$query_rs_agenda_item = sprintf("SELECT *,TIME_FORMAT(vanaf,'%%H:%%i') AS aanvang, TIME_FORMAT(tot,'%%H:%%i') AS eind FROM cms_agenda_items WHERE item_id=%s LIMIT 1",
							$this->GetSQLValueString($val,"int"));
		$rs_agenda_item = mysql_query($query_rs_agenda_item,$this->conn);
		$row_rs_agenda_item = mysql_fetch_assoc($rs_agenda_item);
		$totalRows_rs_agenda_item = mysql_num_rows($rs_agenda_item);
		if($totalRows_rs_agenda_item == 1){
			$this->agenda_item = $row_rs_agenda_item;
		} else {
			$this->agenda_item = false;
		}
		mysql_free_result($rs_agenda_item);
	 }
	/* 
	 * algemene functies
	 */
	
	/**
	 * \brief maakt de verbinding met de database
	 * 
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
			return $row_rs_tekst['tekst'].'<br clear="all" />';
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
	 * @author P.Welling
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
		$vandaag_tot = date('Y-m-d 23:59:59');
		$groepen = array();
    
		if($this->nieuws_agendering === false){
			$extend = sprintf(" AND op_site_van <= %s AND op_site_tot >= %s ",$this->GetSQLValueString($vandaag,"date"),$this->GetSQLValueString($vandaag_tot,"date"));
		} else {
			$extend = sprintf("AND op_site_tot < %s ",$this->GetSQLValueStringf($vandaag,"date"));
		}
    
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
				$query_rs_nieuws_items = sprintf("SELECT titel,nieuws_id FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s $extend",
										$this->GetSQLValueString($row_rs_nieuwsgroepen['nieuwsgroep_id'],"int"));
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
	 * @author P.Welling
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
			
			$extend = ($this->nieuws_agendering === true) ? "AND op_site_tot < CURDATE() " : " AND op_site_van<=CURDATE() AND op_site_tot>=CURDATE() ";
			
			$this->select_db();
			$query_rs_items = sprintf("SELECT * FROM cms_nieuws_inhoud WHERE nieuwsgroep_id=%s $extend ORDER BY aanmaak_datum DESC",
										$this->GetSQLValueString($this->nieuwsgroep_id,"int"));
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
					$tmp = str_replace('[korte_inleiding]',nl2br(substr(strip_tags($row_rs_items['inleiding']),0,70)).'...',$tmp);
					$return .= $tmp;
				} while(($row_rs_items = mysql_fetch_assoc($rs_items))!=false);
			} else {
				$return .= '<tr>'.$this->eol;
				
				$return .= ($this->nieuws_agendering === false) ? '<td>Er zijn geen actuele nieuwsberichten gevonden</td>'.$this->eol : '<td>Er zijn nog geen archiefitems beschikbaar</td>'.$this->eol;
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
				$titel = str_replace('&','&amp;',$row_rs_hoofditems['titel']);
				$tmp_hoofditem_header = str_replace('[titel]',$titel,$tmp_hoofditem_header);
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
						$titel_sub = str_replace('&','$amp;',$row_rs_sub_items['titel']);
						$tmp_subitem_content = str_replace('[titel]',$titel_sub,$tmp_subitem_content);
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
					$titel = str_replace('&','+',$titel);
					$titel = str_replace("'",'+',$titel);
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
					$titel = str_replace('&','+',$titel);
					$titel = str_replace("'",'+',$titel);
					$url = ' href="/pagina/'.$titel.'/'.$row_rs_pagina_item['groep_id'].'/'.$data['item_id'].'/'.$menu_id.'/"';
				}
				mysql_free_result($rs_pagina_item);
				break;
			case 3: //nieuwsgroep
			case 8: //nieuwsarchief
				$this->select_db();
				$query_rs_nieuwsgroep = "SELECT groepsnaam FROM cms_nieuws_groepen WHERE nieuwsgroep_id='".$data['item_id']."'";
				$rs_nieuwsgroep = mysql_query($query_rs_nieuwsgroep,$this->conn);
				$row_rs_nieuwsgroep = mysql_fetch_assoc($rs_nieuwsgroep);
				$totalRows_rs_nieuwsgroep = mysql_num_rows($rs_nieuwsgroep);
				if($totalRows_rs_nieuwsgroep == 1){
					$titel = str_replace(' ','_',$row_rs_nieuwsgroep['groepsnaam']);
					$titel = str_replace('&','+',$titel);
					$titel = str_replace("'",'+',$titel);
					$soort = ($data['soort_item'] == 3) ? 'nieuwsoverzicht' : 'nieuwsarchief';
					$url = ' href="/'.$soort.'/'.$titel.'/'.$data['item_id'].'/'.$menu_id.'/"';
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
					$titel = str_replace('&','+',$titel);
					$titel = str_replace("'",'+',$titel);
					$url = ' href="/nieuwsbericht/'.$titel.'/'.$row_rs_nieuwsitem['nieuwsgroep_id'].'/'.$data['item_id'].'/'.$menu_id.'/"';
				}
				mysql_free_result($rs_nieuwsitem);
				break;
			case 5: //eigen url
				$url = ' href="'.$data['url'].'"';
				break;
			case 6: //item zonder koppeling
				$url = ''; //leeg laten
				break;
			case 7:
				$url = ' href="/links/'.$menu_id.'/"';
				break;
			case 9: //album overzicht
				$url = ' href="/album/'.$menu_id.'/"';
				break;
		}
		return $url;
	}
	
	
	
	/**
	 * \brief hoofdfunctie voor het menu
	 * 
	 * @author P.Welling
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
	 * @author P.Welling
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

	/**
	 * \brief Berekend de eerste dag van de gegeven week
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 * 
	 * @param $year
	 * @param $week
	 */
	function StartOfWeek($year, $week){
		$Jan1 = mktime(1,1,1,1,1,$year);
		$MondayOffset = (11-date('w',$Jan1))%7-3;
		$desiredMonday = strtotime(($week-1) . ' weeks '.$MondayOffset.' days', $Jan1);
		return $desiredMonday;
	}
	
	/**
	 * \brief Bouwt het agendablokje van de maand op
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 * 
	 * \b Gebruikt:
	 * - pwcms::maandnaam()
	 * - pwcms::get_agenda_items()
	 */
	function agenda_blok(){
		$return = '';
		$maand = $this->maand;
		$jaar = $this->jaar;
		/*
		 * De eerste dag van de week bepalen aan de hand van de datum
		 */
		$eerste_maanddag = mktime(1,1,1,$maand,1,$jaar);
		$offset = 1-(date('w',$eerste_maanddag));
		$start = ($offset == 0) ? $eerste_maanddag : strtotime('last Monday', $eerste_maanddag );
		/*
		 * De laatste dag van de laatste week van de maand berekenen
		 */
		$aantal_maanddagen = date('t');
		$laatste_maanddag = mktime(1,1,1,$maand,$aantal_maanddagen,$jaar);
		
		$dagnr_laatse_dag = date('w',$laatste_maanddag);
		$eind_offset = 7 - $dagnr_laatse_dag;
		$eind_dag = ($eind_offset == 7) ? $laatste_maanddag : strtotime('+'.$eind_offset.' DAY',$laatste_maanddag);
		
		$datum = $start;
		$cnt = 0;
		/*
		 * De kalender opbouwen
		 */
		$return .= '<div class="calendar_div">'.$this->eol;
		$return .= '<table border="0" cellpadding="0" cellspacing="0" class="calendar">'.$this->eol;
		/*
		 * de maandnaam
		 */
		$return .= '<tr class="maand">'.$this->eol;
		$return .= '<td colspan="7">'.$this->maandnaam($maand).'</td>'.$this->eol;
		$return .= '</tr>'.$this->eol;
		/*
		 * De dagen
		 */
		$return .= '<tr class="dagnamen">'.$this->eol;
		$return .= '<td>Ma</td>'.$this->eol;
		$return .= '<td>Di</td>'.$this->eol;
		$return .= '<td>Wo</td>'.$this->eol;
		$return .= '<td>Do</td>'.$this->eol;
		$return .= '<td>Vr</td>'.$this->eol;
		$return .= '<td>Za</td>'.$this->eol;
		$return .= '<td>Zo</td>'.$this->eol;
		$return .= '</tr>'.$this->eol;
		while($start < $eind_dag){
			if($cnt == 0){
				$return .= '<tr class="dagen">'.$this->eol;
			}
			$td_class = (date('n',$start) == $maand) ? 'huidige_maand' : 'andere_maand';
			$td_click = '';
			if(date('n',$start) == $maand){
				$items = $this->get_agenda_items($start);
				$de_datum = date('j',$start);
				$td_class .= ($items > 0) ? ' agenda_active' : '';
				$td_click = ($items > 0) ? ' onclick="agenda_items(\''.$start.'\')"' : $td_click;
			} else {
				$de_datum = '';
			}
			$return .= '<td class="'.$td_class.'"'.$td_click.'>';
			$return .= $de_datum;
			$return .= '</td>'.$this->eol;
			$start = strtotime('+1 DAY', $start);
			$cnt++;
			if($cnt == 7){
				$cnt = 0;
				$return .= '</tr>'.$this->eol;
			}
		}
		$return .= '</table>'.$this->eol;
		$return .= '</div>'.$this->eol;
		return $return;
	}
	
	function maandnaam($maand){
		$return = '';
		$arr_maanden = array(1 => 'Januari',2 => 'Februari',3 => 'Maart',4 => 'April',5 => 'Mei',6 => 'Juni',7 => 'Juli',8 => 'Augustus',9 => 'September',10 => 'Oktober',11 => 'November',12 => 'December');			
		$return = (isset($arr_maanden[$maand])) ? $arr_maanden[$maand] : 'Onbekend';
		return $return;
	}
	
	/**
	 * \brief Berekent het aantal agendaitems voor de opgegeven dag
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 * 
	 * \b Gebruikt:
	 * - pwcms::GetSQLValueString()
	 * 
	 * @param $dag
	 */
	function get_agenda_items($dag){
		$return = '';
		$datum = date('Y-m-d',$dag);
		$this->select_db();
		$query_rs_agenda_items = sprintf("SELECT * FROM cms_agenda_items WHERE datum=%s",
					$this->GetSQLValueString($datum, "date"));
		$rs_agenda_items = mysql_query($query_rs_agenda_items,$this->conn);
		$totalRows_rs_agenda_items = mysql_num_rows($rs_agenda_items);
		$return .= $totalRows_rs_agenda_items;
		return $return;
		mysql_free_result($rs_agenda_items);
	}
	
	/**
	 * \brief Bouwt het overzicht van de agendaitems van de gegeven dag op
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 * 
	 * \b Gebruikt:
	 * - pwcms::dagnaam()
	 * - pwcms::maandnaam()
	 * 
	 * \b Vervangvelden:
	 * - Header:
	 * 	- [datum]
	 * - Content:
	 * 	- [titel]
	 * 	- [locatie]
	 * 	- [vanaf]
	 *  - [tot]
	 *  - [url]
	 */
	function agenda_item_overzicht(){
		$return = '';
		
		$template = file_get_contents($this->agenda_item_overzicht_template);
		$arr_template = explode('<--break-->',$template);
		$header = $arr_template[0];
		$content = $arr_template[1];
		$footer = $arr_template[2];
		
		$dagnaam = $this->dagnaam(date('w', $this->agenda_datum));
		$maand = $this->maandnaam(date('n',$this->agenda_datum));
		$dag = $dagnaam.' '.date('j',$this->agenda_datum).' '.$maand;
		
		/*
		 * De gegevens ophalen
		 */
		$this->select_db();
		$query_rs_agenda_items = sprintf("SELECT *, TIME_FORMAT(vanaf,'%%H:%%i') AS aanvang, TIME_FORMAT(tot,'%%H:%%i') AS eind FROM cms_agenda_items WHERE datum=%s",
						$this->GetSQLValueString(date('Y-m-d',$this->agenda_datum),"date"));
		$rs_agenda_items = mysql_query($query_rs_agenda_items,$this->conn);
		$row_rs_agenda_items = mysql_fetch_assoc($rs_agenda_items);
		$totalRows_rs_agenda_items = mysql_num_rows($rs_agenda_items);
		
		if($totalRows_rs_agenda_items > 0) {
			$header = str_replace('[datum]',$dag,$header);
			$return .= $header;
			$i = 0;
			do {
				$row_class = ($i++%2) ? 'even_rij' : 'oneven_rij';
				$tmp = str_replace('[titel]',substr($row_rs_agenda_items['titel'],0,16).'...',$content);
				$tmp = str_replace('[locatie]',$row_rs_agenda_items['locatie'],$tmp);
				$tmp = str_replace('[vanaf]',$row_rs_agenda_items['aanvang'],$tmp);
				$tmp = str_replace('[tot]',$row_rs_agenda_items['eind'],$tmp);
				$tmp = str_replace('[class]',$row_class,$tmp);
				$titel = $row_rs_agenda_items['titel'];
				$titel = str_replace(' ','_',$titel);
				$titel = str_replace('&','-',$titel);
				$titel = str_replace('?','-',$titel);
				$titel = str_replace("'",'+',$titel);
				$url = '/agenda/'.$titel.'/'.$row_rs_agenda_items['groep_id'].'/'.$row_rs_agenda_items['item_id'].'/';
				$tmp = str_replace('[url]',$url,$tmp);
				$return .= $tmp;
			} while(($row_rs_agenda_items = mysql_fetch_assoc($rs_agenda_items))!=false);
			$return .= $footer;
		} else {
			$return .= 'Geen items gevonden'.$this->eol;
		}
		mysql_free_result($rs_agenda_items);
		return $return;
	}
	
	/**
	 * \brief Geeft de dagnaam terug op basis van het doorgegeven nummer
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 * 
	 * @param $dagnr
	 */
	function dagnaam($dagnr){
		$return = '';
		switch($dagnr){
			case 0:
				$return .= 'Zondag';
				break;
			case 1:
				$return .= 'Maandag';
				break;
			case 2:
				$return .= 'Dinsdag';
				break;
			case 3:
				$return .= 'Woensdag';
				break;
			case 4:
				$return .= 'Donderdag';
				break;
			case 5:
				$return .= 'Vrijdag';
				break;
			case 6:
				$return .= 'Zaterdag';
				break;
			default:
				$return .= 'Onbekend';
				break;
		}
		return $return;
	}
	
	/**
	 * \brief Haalt de titel van het agendaitem uit da array en geeft deze terug
	 */
	function agenda_item_titel(){
		$return = '';
		if($this->agenda_item !== false){
			$return = $this->agenda_item['titel'];
		} else {
			$return = 'Agendaitem niet gevonden';
		}
		return $return;
	}
	
	function agenda_item_omschrijving(){
		$return = '';
		if($this->agenda_item !== false){
			$return = $this->agenda_item['omschrijving'];
		}
		return $return;
	}
	
	function agenda_item_locatie(){
		$return = '';
		if($this->agenda_item !== false){
			$return = $this->agenda_item['locatie'];
		}
		return $return;
	}

	function agenda_item_datum(){
		$return = '';
		if($this->agenda_item !== false){
			$datum = strtotime($this->agenda_item['datum']);
			$dagnaam = $this->dagnaam(date('w', $datum));
			$maand = $this->maandnaam(date('n',$datum));
			$dag = $dagnaam.' '.date('j',$datum).' '.$maand.', '.date('Y',$datum);
			$return = $dag;
		}
		return $return;
	}
	
	function agenda_item_aanvang(){
		$return = '';
		if($this->agenda_item !== false){
			$return = $this->agenda_item['aanvang'];
		}
		return $return;
	}
	
	function agenda_item_eind(){
		$return = '';
		if($this->agenda_item !== false){
			$return = $this->agenda_item['eind'];
		}
		return $return;
	}
	
	/**
	 * Zet de template voor het link-overzicht
	 */
	function set_link_tpl($val){
		$this->link_tpl = $val;
	}
	
	/**
	 * \brief Genereert het overzicht van de links
	 */
	function link_overzicht(){
		$return = '';
		/*
		 * De groepen uit de tabel ophalen
		 */
		$this->select_db();
		$query_rs_groepen = "SELECT * FROM cms_links_groepen WHERE status=1 ORDER BY volgnr ASC";
		$rs_groepen = mysql_query($query_rs_groepen,$this->conn);
		$row_rs_groepen = mysql_fetch_assoc($rs_groepen);
		$totalRows_rs_groepen = mysql_num_rows($rs_groepen);
		$arr_groepen = array();
		if($totalRows_rs_groepen > 0) {
			$i=0;
			do {
				$arr_groepen[$i] = array();
				$arr_groepen[$i]['links'] = array();
				$arr_groepen[$i]['groep_id'] = $row_rs_groepen['groep_id'];
				$arr_groepen[$i]['groepsnaam'] = $row_rs_groepen['groepsnaam'];
				$arr_groepen[$i]['omschrijving'] = $row_rs_groepen['groepsnaam'];
				/*
				 * De items van de groep ophalen
				 */
				$query_rs_links = sprintf("SELECT * FROM cms_links_inhoud WHERE groep_id=%s AND status=1 ORDER BY titel ASC",
										$this->GetSQLValueString($row_rs_groepen['groep_id'],'int'));
				$rs_links = mysql_query($query_rs_links,$this->conn);
				$row_rs_links = mysql_fetch_assoc($rs_links);
				$totalRows_rs_links = mysql_num_rows($rs_links);
				
				if($totalRows_rs_links > 0) {
					do {
						$arr_groepen[$i]['links'][] = $row_rs_links;
					} while(($row_rs_links = mysql_fetch_assoc($rs_links))!=false);
				}
				mysql_free_result($rs_links);
				$i++;
			} while(($row_rs_groepen = mysql_fetch_assoc($rs_groepen))!=false);
		}
		mysql_free_result($rs_groepen);
		
		if(count($arr_groepen) > 0) {
			/*
			 * Het overzicht opbouwen
			 */
			$template = file_get_contents($this->link_tpl);
			$arr_template = explode('<--break-->',$template);
			$header = $arr_template[0];
			$content = $arr_template[1];
			$footer = $arr_template[2];
			
			$return .= $header;
			$arr_links = array();
			$arr_rechts = array();
			$aantal = count($arr_groepen);
			$helft = ceil($aantal/2);
			if($aantal > 1) {
				for($i=0,$il=count($arr_groepen);$i<$il;$i++){
					if($i < $helft) {
						$arr_links[] = $arr_groepen[$i];
					} else {
						$arr_rechts[] = $arr_groepen[$i];
					}
				}
			} else {
				$arr_links[] = $arr_groepen[0];
			}
			$arr_content = explode('<--break2-->',$content);
			$header_content = $arr_content[0];
			$content_content = $arr_content[1];
			$footer_content = $arr_content[2];

			$arr_content_content = explode('<--break3-->',$content_content);
			$header_links = $arr_content_content[0];
			$content_links = $arr_content_content[1];
			$footer_links = $arr_content_content[2];
			if(count($arr_links) > 0) {
				$tmp = str_replace('[div_class]','links_links',$header_content);
				$return .= $tmp;
				for($i=0,$il=count($arr_links);$i<$il;$i++){
					$tmp = str_replace('[groep_id]',$arr_links[$i]['groep_id'],$header_links);
					$tmp = str_replace('[groepsnaam]',$arr_links[$i]['groepsnaam'],$tmp);
					$tmp = str_replace('[omschrijving]',$arr_links[$i]['omschrijving'],$tmp);
					$return .= $tmp;
					if(count($arr_links[$i]['links']) > 0){
						foreach($arr_links[$i]['links'] AS $key => $value){
							$tmp = str_replace('[titel]',$value['titel'],$content_links);
							$tmp = str_replace('[url]',$value['url'],$tmp);
							$tmp = str_replace('[link_id]',$value['link_id'],$tmp);
							$tmp = str_replace('[weergegeven_tekst]',$value['weergegeven_tekst'],$tmp);
							$tmp = str_replace('[omschrijving]',$value['omschrijving'],$tmp);
							$return .= $tmp;
						}
						
					} else {
						$return .= '<tr><td colspan="2">Helaas geen links in deze groep</td>'.$this->eol;
					}
					$return .= $footer_links;
				}
				$return .= $footer_content;
			}
			if(count($arr_rechts) > 0) {
				$tmp = str_replace('[div_class]','links_rechts',$header_content);
				$return .= $tmp;
				for($i=0,$il=count($arr_rechts);$i<$il;$i++){
					$tmp = str_replace('[groep_id]',$arr_rechts[$i]['groep_id'],$header_links);
					$tmp = str_replace('[groepsnaam]',$arr_rechts[$i]['groepsnaam'],$tmp);
					$tmp = str_replace('[omschrijving]',$arr_rechts[$i]['omschrijving'],$tmp);
					$return .= $tmp;
					if(count($arr_rechts[$i]['links']) > 0){
						foreach($arr_rechts[$i]['links'] AS $key => $value){
							$tmp = str_replace('[titel]',$value['titel'],$content_links);
							$tmp = str_replace('[url]',$value['url'],$tmp);
							$tmp = str_replace('[link_id]',$value['link_id'],$tmp);
							$tmp = str_replace('[weergegeven_tekst]',$value['weergegeven_tekst'],$tmp);
							$tmp = str_replace('[omschrijving]',$value['omschrijving'],$tmp);
							$return .= $tmp;
						}
					} else {
						$return .= '<tr><td colspan="2">Helaas geen links in deze groep</td>'.$this->eol;
					}
					$return .= $footer_links;
				}
				$return .= $footer_content;
			}
			$return .= $footer;
		} else {
			$return .= 'Er zijn helaas geen links gevonden';
		}		
		return $return;
	}

	################
	#####     ADVERTENTIES
	################
	/**
	 * \brief Zet de template voor de advertenties
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 */
	function set_advertentie_template($val){
		$this->advertentie_template = $val;
	}
	
	/**
	 * \brief Zet het aantal weer te geven ad vertenties in
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 */
	function set_advertentie_max($val){
		$this->advertentie_max = $val;
	}
	
	/**
	 * \brief Bouwt de advertenties op voor weergave in willekeurige volgorde
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.2
	 */
	function toon_advertenties(){
		$return = '';
		/*
		 * De template ophalen
		 */
		$template = file_get_contents($this->advertentie_template);
		$arr_template = explode('<--break-->',$template);
		$header = $arr_template[0];
		$content = $arr_template[1];
		$footer = $arr_template[2];
		
		$return .= $header;
		
		/*
		 * De advertenties ophalen
		 */
		$limit = $this->advertentie_max;

		$this->select_db();
		$query_rs_advertenties = sprintf("SELECT * FROM cms_advertenties WHERE status=1 ORDER BY RAND() LIMIT $limit");
		$rs_advertenties = mysql_query($query_rs_advertenties,$this->conn);
		$row_rs_advertenties = mysql_fetch_assoc($rs_advertenties);
		$totalRows_rs_advertenties = mysql_num_rows($rs_advertenties);
		$display = ' style="display:none;"';
		if($totalRows_rs_advertenties > 0){
			do {
				$afbeelding = ($row_rs_advertenties['afbeelding'] != '') ? '<img src="/inc/photo.php?file='.$row_rs_advertenties['afbeelding'].'&amp;width=100" class="advertentie_afbeelding" alt="'.$row_rs_advertenties['afbeelding'].'" />' : '';
				$tmp = str_replace('[afbeelding]',$afbeelding,$content);
				$tmp = str_replace('[titel]',$row_rs_advertenties['titel'],$tmp);
				$tmp = str_replace('[tekst]',nl2br($row_rs_advertenties['tekst']),$tmp);
				$tmp = str_replace('[bedrijfsnaam]',$row_rs_advertenties['bedrijfsnaam'],$tmp);
				$straat = $row_rs_advertenties['straat'];
				$straat_display = (trim($straat) == '') ? $display : '';
				$tmp = str_replace('[straat]',$straat,$tmp);
				$tmp = str_replace('[straat_display]',$straat_display,$tmp);
				$postcode = $row_rs_advertenties['postcode'];
				$plaats = $row_rs_advertenties['plaats'];
				$postcode_display = (trim($postcode) == '' && trim($plaats) == '') ? $display : '';
				$tmp = str_replace('[postcode_display]',$postcode_display,$tmp);
				$tmp = str_replace('[postcode]',$postcode,$tmp);
				$tmp = str_replace('[plaats]',$plaats,$tmp);
				$telefoon = $row_rs_advertenties['telefoon'];
				$telefoon_display = (trim($telefoon) == '') ? $display : '';
				$tmp = str_replace('[telefoon]',$telefoon,$tmp);
				$tmp = str_replace('[telefoon_display]',$telefoon_display,$tmp);
				$fax = $row_rs_advertenties['fax'];
				$fax_display = (trim($fax) == '') ? $display : '';
				$tmp = str_replace('[fax]',$fax,$tmp);
				$tmp = str_replace('[fax_display]',$fax_display,$tmp);
				$email = (trim($row_rs_advertenties['email']) == '') ? '' : '<a href="mailto:'.trim($row_rs_advertenties['email']).'">'.trim($row_rs_advertenties['email']).'</a>' ;
				$email_display = ($email == '') ? $display : '';
				$tmp = str_replace('[email]',$email,$tmp);
				$tmp = str_replace('[email_display]',$email_display,$tmp);
				$www = (trim($row_rs_advertenties['www']) == '') ? '' : '<a href="http://'.trim($row_rs_advertenties['www']).'" target="_blank">'.trim($row_rs_advertenties['www']).'</a>';
				$www_display = ($www == '') ? $display : '';
				$tmp = str_replace('[www]',$www,$tmp);
				$tmp = str_replace('[www_display]',$www_display,$tmp);
				$return .= $tmp;
			} while(($row_rs_advertenties = mysql_fetch_assoc($rs_advertenties))!=false);
		} else {
			$return .= 'Er zijnh elaas nog geen advertenties geplaatst';
		}
		mysql_free_result($rs_advertenties);
		$return .= $footer;
		return $return;
	}

	/**
	 * \bief Toont de naam van het album
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 */
	function album_naam(){
		if($this->album !== false){
			return $this->album['naam'];
		}
	}
	
	/**
	 * \brief Toont de albumomschrijving
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 */
	function album_omschrijving(){
		if($this->album !== false){
			return nl2br($this->album['omschrijving']);
		}
	}

	/**
	 * \brief Toont de albuminhoud
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 */
	function toon_album_inhoud(){
		$return = '';
		if($this->album_template !== false && $this->album !== false){
			$template = file_get_contents($this->album_template);
			$arr_template = explode('<--break-->',$template);
			$header = $arr_template[0];
			$content = $arr_template[1];
			$footer = $arr_template[2];
			
			$return .= $header;
			if(count($this->album_inhoud) > 0){
				for($i=0,$il=count($this->album_inhoud);$i<$il;$i++){
					$tmp = str_replace('[file]',$this->album_inhoud[$i]['bestandsnaam'],$content);
					$tmp = str_replace('[titel]',$this->album_inhoud[$i]['titel'],$tmp);
					$tmp = str_replace('[afbeelding_id]',$this->album_inhoud[$i]['afbeelding_id'],$tmp);
					$tmp = str_replace('[beschrijving]',str_replace(array("\n","\r","\n\r","\r\n"),'<br />',$this->album_inhoud[$i]['beschrijving']),$tmp);
					$return .= $tmp;
				}
			} else {
				$return .= 'Er zijn helas nog geen afbeeldingen toegevoegd aan dit album';
			}
			$return .= $footer;
		}
		return $return;
	}
	
	/**
	 * \brief Genereert het overzicht van de huidige actieve albums
	 * 
	 * @author P.Welling
	 * 
	 * @since 1.3
	 * 
	 * \b Gebruikt:
	 * - select_db()
	 */
	function album_overzicht(){
		$return = '';
		$this->select_db();
		$query_rs_albums = sprintf("SELECT * FROM cms_album WHERE status=1 ORDER BY volgnr ASC");
		$rs_albums = mysql_query($query_rs_albums,$this->conn);
		$row_rs_albums = mysql_fetch_assoc($rs_albums);
		$totalRows_rs_albums = mysql_num_rows($rs_albums);
		
		if($totalRows_rs_albums > 0){
			do {
				$query_rs_foto = sprintf("SELECT bestandsnaam,titel FROM cms_album_inhoud WHERE album_id=%s ORDER BY volgnr ASC LIMIT 1",
									$this->GetSQLValueString($row_rs_albums['album_id'], 'int'));
				$rs_foto = mysql_query($query_rs_foto,$this->conn);
				$row_rs_foto = mysql_fetch_assoc($rs_foto);
				$totalRows_rs_foto = mysql_num_rows($rs_foto);
				
				$img = ($totalRows_rs_foto == 1) ? '<a href="/album/'.$row_rs_albums['album_id'].'/'.$this->menu_id.'/"><img src="/inc/photo.php?file='.$row_rs_foto['bestandsnaam'].'&amp;box=150" alt="'.$row_rs_foto['titel'].'" /></a>'.$eol : '';
				 
				$return .= '<div class="album_overzicht_album">'.$this->eol;
				$return .= $img;
				$return .= '<div class="album_titel">'.$this->eol;
				$return .= '<p class="album_titel">'.$row_rs_albums['naam'].'</p>'.$this->eol;
				$return .= nl2br($row_rs_albums['omschrijving']).$this->eol;
				$return .= '<br /><a href="/album/'.$row_rs_albums['album_id'].'/'.$this->menu_id.'/">Bekijk album</a>'.$this->eol;
				$return .= '</div>';
				$return .= '</div>'.$this->eol;
			} while(($row_rs_albums = mysql_fetch_assoc($rs_albums))!= false);
		} else {
			$return .= 'Momenteel zijn er helaas gen fotoalbums beschikbaar';
		}
		mysql_free_result($rs_albums);
		return $return;
	}

	######### STATISTIEKEN #########


	/**
	 * \brief Voegt de hit toe aan de database zodat de stats worden bijgewerkt
	 * @author P.Welling
	 * @since 1.3
	 */
	function saveHits(){
		$this->select_db();
		$url = $_SERVER['REQUEST_URI'];
		$query_rs_saveHit = sprintf("INSERT INTO stats_hits SET datum=CURDATE(),aantal=1,url=%s ON DUPLICATE KEY UPDATE aantal= aantal+1", $this->GetSQLValueString($url,'text'));
		mysql_query($query_rs_saveHit,$this->conn);
	}

	function saveVisitor(){
		$this->select_db();
		$ip = $_SERVER['REMOTE_ADDR'];
		$query_rs_saveHit = sprintf("INSERT INTO stats_bezoekers SET datum=CURDATE(),ipAdres=%s,aantal=1 ON DUPLICATE KEY UPDATE aantal=aantal+1", $this->GetSQLValueString($ip,'text'));
		mysql_query($query_rs_saveHit,$this->conn);
	}
}
?>