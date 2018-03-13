<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Martin-Pierre Frenette <typo3@cablan.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_t3lib.'class.t3lib_svbase.php');

require_once(t3lib_extMgm::extPath('lang','lang.php'));

/**
 * Service "TCA Helper service" for the "cablan_tca_helper" extension.
 *
 * This service can be used in other extensions to easly build an record display function which
 * automatically have the right types. For example, a database relation will show the label of 
 * the current value, a field will be text, even images are handled!
 *
 * This allows to very quickly build templates to show any record in TYPO3 extensions.
 *
 * The TCA (Typoscript Configuration Array) already contains everything there
 * is to know about fields for TYPO3 tables, including for custom extensions.
 *
 * This extension simply leverages it for very fast templating!
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 * @package	TYPO3
 * @subpackage	tx_cablantcahelper
 */
class tx_cablantcahelper_sv1 extends t3lib_svbase {
	var $prefixId = 'tx_cablantcahelper_sv1';		// Same as class name
	var $scriptRelPath = 'sv1/class.tx_cablantcahelper_sv1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'cablan_tca_helper';	// The extension key.
	var $LANG;
	var $maincache = array();
	var $parent = null;
	
	/**
	 * This is a function that is missing from the TYPO3 api...
	 * @param  string  $table        The table name
	 * @param  int     $uid          UID of the record
	 * @param  boolean $enableFields If we should call the enableFields function
	 * @return array                 The record (or null)
	 */
	public function getRecord($table, $uid, $enableFields = 1)	{
    	if ( $this->cObj == null ){
    		$this->cObj =t3lib_div::makeInstance('tslib_cObj');
    	}
    	
        $where = ' uid=' . intval($uid);
        if ($enableFields) {
            $where .= $this->cObj->enableFields($table);
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where);
        return @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
    }

	
	/**
	 * Each service needs a process function, but we do not use it, as we
	 * do not offer a default processing function. We thus always return false.
	 * performs the service processing
	 *
	 * @param	string		Content which should be processed.
	 * @param	string		Content type
	 * @param	array		Configuration array
	 * @return	boolean
	 */
	function process($content='', $type='', $conf=array())	{

		// Depending on the service type there's not a process() function.
		// You have to implement the API of that service type.

		return FALSE;
	}
		
	/**
	 * Allows the caller to connect to the API, so that the API use it for hooks.
	 * @param object $obj The parent of the service.
	 */
	public function SetParent($obj){
		$this->parent = $obj;
	}		
		
	/**
	 * Some fields are in every table, but not listed in the TCA, because they are
	 * present in every table. 	We need them for the extension to work.
	 * @param array $tcacolumns the current array to add the unlisted fields to.
	 * @return array 			the modified array.
	 */
	private function AddUnlistedFields( $tcacolumns){
		
		$tcacolumns['crdate'] = array();
		$tcacolumns['crdate']['label'] = 'LLL:EXT:lang/locallang_general.php:LGL.crdate'; 
        $tcacolumns['crdate']['config']['type'] = 'input';
        $tcacolumns['crdate']['config']['eval'] = 'datetime';
        $tcacolumns['crdate']['config']['size'] = 12 ;
        $tcacolumns['crdate']['config']['max'] = 20 ;
    
		$tcacolumns['tstamp'] = array();
		$tcacolumns['tstamp']['label'] = 'LLL:EXT:lang/locallang_general.php:LGL.tstamp'; 
        $tcacolumns['tstamp']['config']['type'] = 'input';
        $tcacolumns['tstamp']['config']['eval'] = 'datetime';
        $tcacolumns['tstamp']['config']['size'] = 12 ;
        $tcacolumns['tstamp']['config']['max'] = 20 ;
		
	
        $tcacolumns['uid'] = array();
		$tcacolumns['uid']['label'] = 'LLL:EXT:lang/locallang_general.php:LGL.uid'; 
        $tcacolumns['uid']['config']['type'] = 'input';
        $tcacolumns['uid']['config']['eval'] = 'int';
        
		$tcacolumns['pid'] = array();
		$tcacolumns['pid']['label'] = 'LLL:EXT:lang/locallang_general.php:LGL.pid'; 
        $tcacolumns['pid']['config']['type'] = 'input';
        $tcacolumns['pid']['config']['eval'] = 'int';
        
		return $tcacolumns;
	}	
	
	/**
	 * This is the main point of entry for the service: it takes in a markerArray, a row,
	 * and will fill in the markers for the fields.
	 *
	 * For example, if a tt_content record is passed, a new marker ###tt_content_title### will show
	 * an the title of the tt_content record. 
	 *
	 * We will also have ###LABEL_table_field### with the TCA label for the field.
	 * 
	 * @param array $markerArray  the current markerArray from the parent function
	 * @param array $row          the row to edit
	 * @param string $table        the table name of the row
	 * @param string $markerprefix if mentionned, will use that as the prefix instead of the table name
	 * @return array 			   the modified markerArray
	 */
	public function SubstituteTCAMarkers( $markerArray, $row, $table, $markerprefix = '' ){
		
		// to use the TCA, we must both load the TCA system, and the values for our 
		// specific table.
		$GLOBALS['TSFE']->includeTCA(); 
		t3lib_div::loadTCA($table);


		if ( $this->LANG == null ){
			$this->LANG = t3lib_div::makeInstance('language');
		}
		
		if ( $this->cObj == null ){
			$this->cObj = t3lib_div::makeInstance("tslib_cObj");
		} 
		
		//by default, we use the table name. The conventions in TYPO3 are to use
		//UPPERCASE markers, so we use lowercase ones to avoid collision. Therefore,
		// ###NEWS_TITLE### will show the news title from the tt_news pluging, 
		// but ###news_title### will show it from this extension.
		if ( $markerprefix == ''){
			$markerprefix  = $table;
		}
		
		// we work on a copy of the columns since we do not want to alter the global
		// array...				
		$tcacolumns = $GLOBALS['TCA'][$table]["columns"];
		
		$tcacolumns = $this->AddUnlistedFields($tcacolumns);
		
		foreach( $tcacolumns  as $field => $tcaconfig ){

			/////////////////////////////////////////////////////
 			/// Step 1 : Load the defaults if not alredy set
 			/////////////////////////////////////////////////////
    		if ( !isset( $row[$field] ) ){
	             if ( isset($tcaconfig['config']['default'] ) ){
		             $values[$field] = $tcaconfig['config']['default'];
		             }   
		        else{
		             $values[$field] = '';
		             }
	        }
	        
			//////////////////////////////////////////////////////////
			/// Step 2 : fill the label
			//////////////////////////////////////////////////////////
            $markerArray["###LABEL_" . $markerprefix. '_'. $field . "###"] = $this->LANG->sL($tcaconfig["label"]);
			
			//////////////////////////////////////////////////////////
			/// Step 3 : create the field, according to the TCA type
			//////////////////////////////////////////////////////////
			switch( $tcaconfig["config"]["type"] ){
	            case "input":
	                $content = $this->DisplayInputFieldFromTCA($row[$field], $tcaconfig);
	                break;
	            case "text":
	            	// text fields are simply printed as is.
	                $content = stripslashes(nl2br($row[$field]));
	                break;
	            case "group":
	                switch ( $tcaconfig["config"]["internal_type"] ){
	                    case "file":
	                        $content = $this->GetImagesAndFilesFromTCA( $row[$field], $tcaconfig);   
	                        break;
	                    case "db":
	                        $content = $this->DisplayMMValueFromTCA( $row[$field],$row['uid'], $tcaconfig );
	                        break;
	                        }
	                break;
	            case "select":
	                    $content = $this->DisplayMMValueFromTCA($row[$field],$row['uid'], $tcaconfig); 
		            break;                
	            default: 
	            	// we have no idea what is it, so we just output it.
	            	$content = $row[$field];
	                break;
			}
			$markerArray["###" . $markerprefix. '_'. $field .  "###"] = $content;
		}
			
		///////////////////////////////////////////////////////////////////////////////
		/// Step 4: Call the hooks in case we want processing in a custom extension
		///////////////////////////////////////////////////////////////////////////////
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cablantcahelper']['extraTableMarkerHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_cablantcahelper']['extraTableMarkerHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->extraTableMarkerHook( $markerArray, $table, $row, $this->parent  );
			}
		}
		/////////////////////////////////////////////////////////////////////////////////////
		/// Step 5: we have our own non-registered internal hook for built-in special cases
		/////////////////////////////////////////////////////////////////////////////////////
			$markerArray = $this->extraTableMarkerHook( $markerArray, $table, $row, $this->parent  );
			return $markerArray;
		}
		
		/**
		 * This function formats an input type properly, which might be a date, or 
		 * a normal field.
		 * @param [type] $value  value from the row
		 * @param [type] $config TCA configuration for this field
		 * @return 		 the value for the field.
		 */
		private function DisplayInputFieldFromTCA($value, $config){
       
           switch ( $config['config']['eval'] ){
	           case 'datetime':
	           		if ( $value > 0 ){
	               $tvalue = t3lib_BEfunc::datetime( $value );
	               }
	            break;
	            case 'date':
	            	if ( $value > 0 ){
	            	$tvalue = t3lib_BEfunc::date( $value );
	            	}
	            break;
	            case 'time':
	            	if ( $value > 0 ){
	               $tvalue = t3lib_BEfunc::time( $value );
	               }
	            break;
	            default:
	            	$tvalue = $value;
	            break;
	       }
        	return $tvalue;
    	}
    	
    	
     	  /**
    	 * This function allows to display the selected value(s) from a 
     	 * multiple-multiple relation table, which are tricky to display,
     	 * as some default values can be in the TCA, and others in the database!
     	 *
     	 * This function solves all of this!
     	 * 
     	 * @param string $value     the field value to display
    	 * @param [type] $uid       the uid of the record
    	 * @param [type] $tcaconfig TCA configuration for this field
    	 * @return string 			the value for the field.
    	 */
         private function DisplayMMValueFromTCA($value,$uid, $tcaconfig){
         	
			if ( $value != '' ){
				// mm values are stored as comma seperated values.
				$values = explode(',', $value);
	       		foreach( $values as $val){
	       			// the key is the record, while the value is the table.
	       			$newvalues[$val] = '';
	       		}
	       		$values = $newvalues;
	       		
	       		
		       if ( $tcaconfig["config"]['MM'] ){
		       		/// This is a MM field, as such, we need to query the DB to get
		       		/// the actual values : They are not in the field.
		       		/// We thus overwrite our current values with the new uid.
		       		/// 
		       		/// mm table store the 2 uids and the original table field
		       		/// contains the number of relations, not the actual ones!
		       		$values = $this->LoadMMValues( $values, $uid ,$tcaconfig );
		       }
		    	
		       // now that we have the values, either from the field or from the 
		       // MMvalues, we load them from the TCA.
		    	$values = $this->GetSelectValueFromTCA( $values, $tcaconfig );
		    
		    	// if we do have a foreign table, we'll load the labels from the 
		    	// foreign table, replacing the UID with their labels.
		    	if ( $tcaconfig["config"]['foreign_table'] != '' ){
	       	   	
		        	$table = $tcaconfig["config"]['foreign_table'];
		    		
		    		$values = $this->FillTCAMMValues($values, $table );
		            
		        
		        }else if ($tcaconfig["config"]['internal_type'] == 'db' ){
		        // otherwise, the TCA is configued to allow any type of 
		        // records to be linked, thus using a different method.
		       	   $values = $this->FillTCAMMValues($values);	
		       
		        }
	        
	        	// as mentionned, we do not care about the actual values of
	        	// the records: it's the keys that count. The value can be used,
	        	// for example, to display custom information which we do not need.
	        	if ( is_array( $values)){
	        		$keys = array_keys($values );
	        		$value = implode(',', $keys);
	        	}
	        
	    	}
	    	
		   return $value;
   }


	
    /**
     * Loads a table into the $cache parameter for easy access.
     *
     * Only works if cache is empty.
     *
     * Also, table can be an array with the table and the sort value,
     * as per TYPO3 convension.
     * 
     * @param mixed $table     table or array of table and sort field.
     * @param array  &$cache   the cache to fill
     * @param string $where    the where to apply when filling up
     * @param string $keyfield which field to use as the key of the array.
     */
    public function LoadCache( $table,&$cache = array(), $where = "1", $keyfield="uid" ){

        if ( !count($cache)){
          $tablearray = explode( "|", $table );
          if( is_array( $tablearray ) ){
            $table = $tablearray[0];
            $sort = $tablearray[1];
        	}                                                                                                               
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery( "*", $table, $where . " " . $this->cObj->enableFields($table), "", $sort);
        while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))
            {
             $cache[$row[$keyfield]]= $row;
            }
    
        }
    }

	/**
     * This function searches the $table for the row contaning
     * the UID of the $value, and returns the $fieldname.
     * It will use the $cache variable to accelerate further
     * calls.
     * @param  [type] $value     [description]
     * @param  [type] $table     [description]
     * @param  [type] $fieldname [description]
     * @param  string $default   [description]
     * @return [type]            [description]
     */
    private function getMMResult($value, $table, $fieldname, $default=""){
        $this->LoadCache( $table, $this->maincache[$table] );

        $values = explode(",", $value );
        
		$return = array();
		if ( is_array($values)){        
	        foreach( $values as $val){
		        if ( !is_null($this->maincache[$val]) ){
		            $return[] = str_replace(",", "&#044;", $this->maincache[$val][$fieldname]);
	            }
        	}
        	$value = implode(',', $return);
        }
        else if ( $value == "" ){
            $value = $default;
        }
    	return $value;
    }
    
    /**
     * This function expects an array in which the keys are the
     * the UID of the item and value is the table.
     * defaulttable is used if no table is specified.
     * It will use the maincache variable to accelerate further
     * calls.
     * 
     * @param [type] $values       [description]
     * @param string $defaulttable [description]
     */
    private function FillTCAMMValues($values, $defaulttable = ''){

		$return = array();
		if ( is_array($values)){
	        foreach( $values as $val => $table){
	        	if ( $table != '--TCA--'){
			    	if ( $table == ''){
			    		$table = $defaulttable;
			    	}
			    	if ( $table != ''){
						$this->LoadCache( $table, $this->maincache[$table] );
						t3lib_div::loadTCA($table);
					            
			        	$fieldname  = $GLOBALS['TCA'][$table]['ctrl']['label'];
						
				        if ( isset($this->maincache[$table][$val]) ){
						    $val = str_replace(",", "&#044;", $this->maincache[$table][$val][$fieldname]);
				        }
			     	}
			    }
		             
		        // we only care about the key, but we store the table as the value for
		     	$return[$val] = $table;
		    }
        	$values = $return;
    	}
    	return $values;
    }

	/**
	 * This function returns if a value is an image.
	 * @param [type] $str [description]
	 */
    private function IsImage($str ){
	   $extpos =  strrpos( $str, "." );
        if ( $extpos > 0 ){
            $ext = substr( $str, $extpos+1 );
			switch ( strtolower($ext) ){
				case 'jpg':
				case 'jpeg':
				case 'gif':
				case 'png':
				case 'bmp':
					return true;
				default:
					return false;
			}
        }
	}
        
        
    /**
     * This function shows an image/files configured from the TCA...
     * This, with the MM field, is the main benefit of this extension:
     * Automatic image display!
     * @param string $value  The value of the row
     * @param [type] $config The TCA configuration for the field
     * @return string 		 The image tag
     */
	private function GetImagesAndFilesFromTCA( $value,$config ){
        
		$array = array();
		if ( $value == "" ){
		    return "";    
		}
		// there can be multiple images or files!
		$imgs = explode( ",", $value);
			 
		$uploadPath = $config["config"]["uploadfolder"];
 		if ( is_array( $imgs ) ){
               foreach( $imgs as $img ){
                if ( $img != "" ){
                    if ( $this->IsImage(PATH_site.$uploadPath."/".$img) ){
	                    $imgTSConfig['altText'] = $this->LANG->sL($config["label"]);
	                    $imgTSConfig['file'] = $uploadPath . "/". $img;
	                    if ( $config['config']['width'] > 0 ){
	                        $imgTSConfig['file.']['width'] = $config['config']['width'];
	                        }
	                    if ( $config['config']['height'] > 0 ){
	                        $imgTSConfig['file.']['height'] = $config['config']['height'];
	                        }
	                    
	                    //Image to enlarge:
	                    $imgFile = $uploadPath . "/". $img;
	                    
	                    //imageLinkWrap Configuration:
	                    $conf['bodyTag'] = '<body bgColor="white" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">';
	                    $conf['wrap'] = '<a href="javascript: close();"> | </a>';
	                    $conf['width'] = '350';
	                    $conf['height'] = '350';
	                    $conf['JSwindow'] = '1';
	                    $conf['JSwindow.newWindow'] = '1';
	                    $conf['JSwindow.expand'] = '0,0';
	                    $conf['enable'] = '1';
	                    
	                    //output:
	                    $image = $this->cObj->IMAGE($imgTSConfig);
	                    $old = $GLOBALS['TSFE']->absRefPrefix;
	                    
	                    if ( $GLOBALS['TSFE']->absRefPrefix == "" ){
	                        $GLOBALS['TSFE']->absRefPrefix = '/';
	                        
	                    }
	                    
	                    $lw = $this->cObj->imageLinkWrap($image,$imgFile,$conf);
	                    
	                    $GLOBALS['TSFE']->absRefPrefix = $old;
	                    
	                    
	                    $array[] = $lw ;
                    }
                    else{
                    	// this is not an image, put a download link.
						$cleaned = $this->getCleanedFilename($img);
						$icon = $this->GetIconFromFileName($img);
                        $array[] = '<a href="'. $uploadPath . "/" . $img.'" target="_blank">'.
							$icon .$cleaned  .'</a><br />';
                    }
                }
            }
         }
            
       	if ( count($array) > 1 ){      
       		// there is no config in the TCA on how to display the images/files,
       		// So we use a simply <br /> to divide them.
       		return implode( "<br />", $array );
        }
        else if ( count($array ) == 1 ){
            return $array[0];
        }
    }

	/**
	 * This function loads MM values from a MM table. For example,
	 * tt_news and tt_news_cat have a mm relationship in the table
	 * tt_news_cat_mm in which the uid_local contains the tt_news 
	 * record uid and the uid_foreign contains the news category uid.
	 *
	 * This is typically complex to implement, but it is automated
	 * by this extension.
	 *
	 * sometimes, the mm table allows diffferent tables, which makes
	 * it even more complex.
	 * 
	 * @param [type] $values    the values fields, which should be the number of records.
	 * @param [type] $uid       the current row uid
	 * @param [type] $tcaconfig the tca configuration.
	 */
    private function LoadMMValues( $values, $uid, $tcaconfig ){
 		if ( $tcaconfig["config"]['MM'] && ( count($values) <= 1 ) ){
	        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery( "*", $tcaconfig["config"]['MM'], 'uid_local=' . intval($uid) );
	        if ( $result && $GLOBALS['TYPO3_DB']->sql_num_rows( $result ) > 0 ){
	            
	            // in a MM field, the local field is 
				// the number of MM records, not their values.
				// so, we can erase it.
				//
	            $values = array(); 
		        while (  $mmrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $result ) ){
					if ( isset($mmrow['tablenames'] ) && $mmrow['tablenames'] != ''){
		        		$table = $mmrow['tablenames'];
		        	}
		        	else{
		        		// if tablenames is not a field (or empty), then use the TCA foreign_table.
		        		$table = $tcaconfig['config']['foreign_table'];
		        		
		        	}
		        	
		        	
		        	if ( $table =='' ){
		        		// this is in effect an assert statement
		        		exit('In tx_cablantcahelper_sv1::LoadMMValues we are missing a table for a record of table '.$tcaconfig["config"]['MM']);
		        	}
		        	
		        	// we store the table as the value, as per convention, but we don't really need it.
	                $values[$mmrow['uid_foreign']] = $table;
	                
	            }
	               
	        }
		}
    	
      return $values;
  }          
  
  	/**
  	 * TYPO3 comes with a lot of file icons, so if can find one, show it next to a file download.
  	 * @param string $value the file name
  	 */
	public function GetIconFromFileName($value){
	
			$iconspath = 'typo3/gfx/fileicons';
	
			if (is_file(PATH_site.$iconspath."/".$this->getExtensionFromFileName($value) .'.gif'  )){					
        	$img.='<img src="'.$iconspath."/".$this->getExtensionFromFileName($value) .'.gif" alt="'.$this->getExtensionFromFileName($value).'"/>';
        	}
		return $img;
    }
    
    /**
     * This is a simple function which returns the last extension, in lowercase.
     * @param  [type] $filename [description]
     * @return [type]           [description]
     */
	public function getExtensionFromFileName($filename){
	   $extpos =  strrpos( $str, "." );
        if ( $extpos > 0 ){
            $ext = substr( $str, $extpos+1 );
		}
		return strtolower($ext);
	}

	/**
	 * TYPO3 stores many files with a prefix to indicate, for example,
	 * the user who uploaded the file or the extension which created it.
	 *
	 * Sadly, the marker used is just _, which is the same marker as for replacing
	 * a . in the filename! Therefore, we must respect the format and do the actual replacement.
	 *
	 * It's awkward, but it works.
	 * 
	 * @param  string $filename the raw username as encoded by TYPO3
	 * @return string           the cleaned username
	 */
     public function getCleanedFilename($filename){
		
		$parts = explode( '_', $filename);
		if ( count($parts) > 0){
			$filename = substr( $filename, strpos( $filename,'_')+1  );
			$filename = substr( $filename, 0, strrpos( $filename, '_')  );
			$filename[strrpos( $filename, '_')] = '.';
		}
		return $filename;
	}
	
	/**
	 * In addition to MM values, some fields have default values
	 * that can be selected, such as "ALL RECORDS", or "ALL LANGUAGES",
	 * This function adds those labels, which HAVE PRIORITY over mm records!
	 * 
	 * @param [type] $values [description]
	 * @param [type] $config [description]
	 */
	private function GetSelectValueFromTCA( $values, $config){
		if ( is_array($config["config"]["items"]) ){
            foreach ( $config["config"]["items"] as $key => $value){
            	// the label is stored in an array with the label at position 0, 
            	// and the value at position 1... we must also pass that label
            	// via the sL function since the label is an index to the 
            	// localization file.
                $rows[$value[1]] =$this->LANG->sL($value[0]) ;
            }
        }
        if ( is_array($values)){
        	$return = array();
	        foreach ($values as $val => $table){
	        	if ( isset($rows[$val])){
	        		// like in the other functions, it's the key
	        		// that contains the actual value: not the value.
	        		$return[$rows[$val]] = '--TCA--';
	        	}
	        	else{
	        		// we didn't find the val in the items, so we keep it
	        		// as it is.
	        		$return[$val] = $table;
	        	}
	        }
	        if ( count($return) > 0 ){
	        	$values = $return;
	        }
        }
        
        return $values;
		
	}
	
	/**
	 * This is our own internal hook which is basically used
	 * to call the extraItemMarkerProcessor function of tt_news,
	 * which allows to add all of the tt_news customization even when
	 * not actually showing a tt_news record from the tt_news plugin!
	 * 
	 * @param  array $markerArray the current markerArray.
	 * @param  string $table     the table name, if not tt_news, will just do nothing.
	 * @param  array $row        The full tt_news row.
	 * @param  obj $parent       The parrent passed to the marker processor, needed for tt_news processing.
	 * @return array              the modofied markerArray
	 */
	private function extraTableMarkerHook($markerArray, $table, $row, $parent){
		
		switch ( $table){
			
			case 'tt_news':
		
			
				// Adds hook for processing of extra item markers
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$markerArray = $_procObj->extraItemMarkerProcessor($markerArray, $row, $lConf, $this->parent);
					}
				}
				
				break;				
			default:
		}

		return $markerArray;
	}
	
	/**
	 * TYPO3 comes with multiple markerArray processing functions, and none
	 * of them work perfectly! The best one uses the cache and can be very buggy
	 * since it can caches between different records!
	 *	 *
	 * This adaptation allows to easily use a markerArray without having to 
	 * worry about the cache clearing, etc...
	 * 
	 * @param  string $template                   HTML code of the template
	 * @param  array  $markerArray                the MarkerArray
	 * @param  array  $subpartArray               the subpartArray to process.
	 * @param  array  $wrappedSubpartContentArray the wrappedSubpartContent array
	 * @return string                             The generated content.
	 */
	public function substituteMarkerArray($template, $markerArray=array(), $subpartArray=array(), $wrappedSubpartContentArray=array()){
		if ( $this->cObj == null ){
			    		$this->cObj =t3lib_div::makeInstance('tslib_cObj');
			    	}
			    	
		$content = $this->cObj->substituteMarkerArray($template, $markerArray);
		
		if (count($subpartArray )> 0 ){
			foreach ($subpartArray as $subPart => $subContent) {
	    		$content = $this->cObj->substituteSubpart($content, $subPart, $subContent);
			}
		}
		if (count($wrappedSubpartContentArray )> 0 ){
			foreach ($wrappedSubpartContentArray as $wrappedSubPart => $wrappedSubContent) {
	    		$content = $this->cObj->substituteSubpart($content, $wrappedSubPart, $wrappedSubContent );
			}
		}
		return $content;
	}
	    	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cablan_tca_helper/sv1/class.tx_cablantcahelper_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cablan_tca_helper/sv1/class.tx_cablantcahelper_sv1.php']);
}

?>