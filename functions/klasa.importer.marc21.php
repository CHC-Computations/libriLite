<?php


class marc21 {
	
	
	public function __construct($fullRecord) {
		
		$this->fullRecord = $fullRecord;
		$this->record = new stdclass;
		$this->record->LEADER = $fullRecord->LEADER;
		$this->record->marcFields = $fullRecord;
		
		}
	
	public function setLanguageMap($Tmap) {
		$this->languageMap = $Tmap;
		}

	public function setRolesMap($map) {
		$this->creativeRolesMap = $map;
		}

	
	public function setBasicUri($link=null) {
		$this->basicUri = $link;
		}
	
	
	public function getMarcLine($field, $subfields=array(), $sep=' ', $sepLn='<br/>') {
		if (!is_array($subfields))
			$subfields = (array)$subfields;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			
			foreach ($line as $row) {
				$codes = array();
				if (!empty($row->code)) {
					foreach ($row->code as $code=>$val) {
						if (count($subfields)>0) {
							if (in_array($code,$subfields))
								$codes[] = $val;
							} else 
							$codes[] = $val;
						}
					$result .= implode($sep, $codes).$sepLn;
					} 
				if (!is_object($row))
					$result .= $row.$sepLn;
				}
				
			return $result;	
			} else 
			return null;
		}
	
	public function getMarcFirst($field, $subfields=array(), $sep=' ') {
		if (!is_array($subfields))
			$subfields = (array)$subfields;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			
			$row = (array)current($line);
			
			$codes = array();
			if (!empty($row['code'])) {
				foreach ($row['code'] as $code=>$val) {
					if (count($subfields)>0) {
						if (in_array($code,$subfields))
							$codes[$code] = $val;
						} else 
						$codes[$code] = $val;
					}
				} 
			if (!is_array($row))
				$codes = $row;
			
				
			return $codes;	
			} else 
			return null;
		} 	
		
	public function getMarcFirstStr($field, $subfields=array(), $sep=' ') {
		if (!is_array($subfields))
			$subfields = (array)$subfields;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			
			$row = (array)current($line);
			
			$codes = array();
			if (!empty($row['code'])) {
				foreach ($row['code'] as $code=>$val) {
					if (count($subfields)>0) {
						if (in_array($code,$subfields))
							$codes[] = $val;
						} else 
						$codes[] = $val;
					}
				foreach ($codes as $k=>$v)
					if (is_Array($v))
						$codes[$k] = implode($sep, $v);
					
				$result .= implode($sep, $codes);
				} 
			if (!is_array($row))
				$result .= $row;
			
			$result = trim(chop($result));	
			if ($result=='')
				return null;
				else 
				return $result;	
			} else 
			return null;
		} 
	
	public function getMarcArr($field, $subfields) {
		
		}
	
	public function getLeader() {
		return $this->record->LEADER;
		}

	public function getTitle() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b']));
		
		
		if ($title == '[Název textu k dispozici na připojeném lístku]') {
			$title = "[Title on the picture (retrobi record)]"; 
			}
		
		if (!empty($title))
			return $title;
			// return mb_convert_encoding($title, 'UTF-8', 'ISO-8859-1');
			else 
			return "[no title]";
		}
		
	public function getTitleFull() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b', 'c']));
		if (!empty($title))
			return $title;
			else 
			return "[no title]";
		}
		
	public function getTitleSort() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b', 'c']));
		if (!empty($title))
			return $this->str2url($title);
			else 
			return "[no title]";
		}
		
	public function getTitleSub() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['b']));
		if (!empty($title))
			return $title;
			else 
			return "[no title]";
		}
		
		
	public function getTitleShort() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a']));
		if (!empty($title))
			return $title;
			else 
			return "[no title]";
		}
		
		
	public function getTitleAlt() {
		$field = '700';
		$roles = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->t) && is_Array($sf->code->t))
					foreach ($sf->code->t as $z) {
						$roles[] = $z;	
						}
				}
			}
		return $roles;	
		}
		
		
	
	public function getDescription () {
		$desc = $this->getMarcLine('520', ['a']);
		
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
	
	
	public function getStatmentOfResp() {
		$desc = $this->getMarcFirstStr('245', ['c']);
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
	
	public function getSourceDocument() {
		$desc = $this->getMarcFirstStr('995', ['a']);
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
	
	
	public function preparePerson($desc) {
			
		$ndesc['name'] = '';
		if (!empty($desc['a'])) 
			if (is_Array($desc['a']))
				$ndesc['name'] = implode(' ',$desc['a']);
				else 
				$ndesc['name'] = $desc['a'];
		if (!empty($desc['c'])) 
			if (is_Array($desc['c']))
				$ndesc['namec'] = ' '.implode(' ',$desc['c']);
				else 
				$ndesc['namec'] = ' '.$desc['c'];
				
		
		if (!empty($desc['d'])) {
			if (is_array($desc['d']))
				$desc['d']=implode(' ',$desc['d']);
			$ndesc['date'] = $desc['d'];
			}
		
		if (!empty($desc['1'])) {
			if (is_array($desc['1']))
				$desc['1']=implode(' ',$desc['1']);
			$ndesc['id'] = $desc['1'];
			}
			
		if (is_array($ndesc))	
			return implode(' ',$ndesc);
			else 
			return $ndesc;
		}
	
	public function personInitials($desc) {
			
		$ndesc['name'] = '';
		if (!empty($desc['a'])) {
			if (is_Array($desc['a']))
				$name = implode(' ',$desc['a']);
				else 
				$name = $desc['a'];
			$name = str_replace('-', ' ', $name);
			$init = explode(' ', $name);
			foreach ($init as $str)
				$nt[]=strtolower(substr($str,0,1));
			krsort($nt);
			if (is_array($ndesc))	
				return implode(' ',$nt).' '.implode('',$nt);
				else 
				return $init;
			}
		}
	
	
	public function getMainAuthor() {
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			return $this->preparePerson($desc);
			} else 
			return null;
		}
	
	public function getMainAuthorW() {
		// inicjały dla: Takala, Jukka-Pekka = j p t jpt
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			return $this->personInitials($desc);
			} else 
			return null;
		}
	
	public function getMainAuthorSort() {
		// Name + date without special chars. check:  $str = preg_replace('/[[:cntrl:]]/', '', $str);
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			return $this->str2url($this->preparePerson($desc));
			} else 
			return null;
		}
	
	public function transleteCrativeRoles($code) {
		$Tmap = $this->creativeRolesMap;
		if (!empty($Tmap[$code])) { 
			#echo $Tmap[$code].".";
			return $Tmap[$code];
			} else {
			#echo $code.".";
			return $code;
			}
		}
	
	
	
	public function getMainAuthorRole() {
		$field = '100';
		$roles = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->e) && is_Array($sf->code->e))
					foreach ($sf->code->e as $z) {
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				}
			}
		sort($roles);	
		return $roles;	
		}
		
	public function getMainOtherAuthorsRoles() {
		$field = '700';
		$roles = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->e) && is_Array($sf->code->e))
					foreach ($sf->code->e as $z) {
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				}
			}
		$outroles = [];
		foreach ($roles as $k=>$v)
			$outroles[] = $v;
		#sort($roles);	
		return $outroles;	
		}

	
	
		
	public function getCorporateAuthor() {
		$fields = [110,111,710,711];
		$cauthors = [];
		foreach ($fields as $field) {
			$desc = $this->getMarcFirst($field);
			if (!empty($desc['a'])) 
				$cauthors[] = $desc['a'];
			}
		return $cauthors;
		}
	
	public function getOtherAuthors() {
		$field = 700;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$authors[] = $this->preparePerson($desc);
				}
				
			return $authors;	
			} else 
			return null;
		}
		
	
		
	public function getOtherAuthorsW() {
		$field = 700;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$authors[] = $this->personInitials($desc);
				}
				
			return $authors;	
			} else 
			return null;
		}
		
	
	
	public function getRelations() {
		$auth = $this->getConfig('author-classification');
		$field = 500;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$this->preparePerson($desc);
				$authors[] = $this->render('record/author-link.php',['author' => $desc ]);
				
				} 
				
			return $authors;	
			} else 
			return null;
		
		
		}
	

	public function getMajorFormat() {
		$formats = [
			'a' => 'Book chapter',
			'b' => 'Journal article',
			'm' => 'Book'
			];
		$code = substr($this->getLeader(), 7, 1);	
		if (array_key_exists($code, $formats))
			return $formats[$code];
			else 
			return 'Other';
		}
	
	public function getFormat() {
		$formats = [
			'ab' => 'Article',
			'am' => 'Book',
			'aa' => 'Book Chapter',
			'cm' => 'Musical Score', 
			'km' => 'Photo', 
			'as' => 'Serial', 
			'em' => 'Map',
			'gb' => 'Slide',
			
			'mb' => 'Computer file (Serial component part)',
			'im' => 'Nonmusical sound recording (Monograph/Item)',
			'om' => 'Kit (Monograph/Item)',
			'gm' => 'Projected medium (Monograph/Item)',
			];
		$code = substr($this->getLeader(), 6, 2);	
		if (!empty($this->GetMarcFirst(111)))
			$ret[] ='Conference Proceeding';
		if (array_key_exists($code, $formats))
			$ret[] = $formats[$code];
			else 
			$ret[] = "unknown ($code)";
		if (count($ret)==1)
			return current($ret);
			else 
			return $ret;
		}

	public function translateLangCode($code) {
		$code = $this->str2url($code);
		$Tmap = $this->languageMap;
		if (!empty($Tmap[$code])) { 
			#echo $Tmap[$code].".";
			return $Tmap[$code];
			} else {
			#echo $code.".";
			if (stristr($code,'\\'))
				return 'undefined';
				else 
				return $code;
			}
		}
	
		
	public function getLanguage() {
		
		$field = '008';
		$langs = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field[0])) ) {
			$z = substr($this->record->marcFields->$field[0],35,3);
			if (!stristr($z,'\\'))
				$langs[$z] = $this->translateLangCode($z);	
			}
		
		$field = '041';
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->a) && is_Array($sf->code->a))
					foreach ($sf->code->a as $z) {
						$langs[$z] = $this->translateLangCode($z);	
						}
					
				if (!empty($sf->code->h) && is_Array($sf->code->h))
					foreach ($sf->code->h as $z) {
						$langs[$z] = $this->translateLangCode($z);	
						}
					}
			}
		
		#echo "<pre>".print_R($langs,1)."</pre>";	
		sort($langs);	
		return $langs;	
		}
		
		
	public function getPublishDate() {
		$field = '008';
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field[0])) ) {
			return substr($this->record->marcFields->$field[0],7,4);
			}
		$field = '264';
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->c))
					if (is_Array($sf->code->c)) {
						foreach ($sf->code->c as $c) 
							return $c;	
						}
						else 
						return $sf->code->c;
				}
			}
		$field = '260';
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->c))
					if (is_Array($sf->code->c)) {
						foreach ($sf->code->c as $c) 
							return $c;	
						}
						else 
						return $sf->code->c;
				}
			}
		
		
		return null;	
		}
		
		
	public function getGenreM() { 
		$field = '380';
		$res = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->i)&&($sf->code->i == 'Major genre')&& !empty($sf->code->a))
					if (is_Array($sf->code->a)) {
						foreach ($sf->code->a as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf->code->a;
				}
			}
		if (count($res)==0)
			$res[]='Undefined';
		return $res;	
		}	
		
	public function getGenreS() { 
		$field = '381';
		$res = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->i)&&($sf->code->i == 'Major genre')&& !empty($sf->code->a))
					if (is_Array($sf->code->a)) {
						foreach ($sf->code->a as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf->code->a;
				}
			}
			
		return $res;	
		}	
	
	public function getGenre() { 
		$field = '655';
		$res = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->a))
					if (is_Array($sf->code->a)) {
						foreach ($sf->code->a as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf->code->a;
				}
			}
			
		return $res;	
		}	
	
	
	
	public function getRegion() {
		$field = '651';
		$res = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->a))
					if (is_Array($sf->code->a)) {
						foreach ($sf->code->a as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf->code->a;
				}
			}
			
		return $res;	
		}
	
	public function getPublished() {
		$field = '260';
		$res = [];
		$lp = 0;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				$res[$lp] = '';
				foreach ($sf->code as $z) {
					if (is_array($z))
						$res[$lp] .= ' '.implode(' ',$z);
						else 
						$res[$lp] .= ' '.$z;
					}	
				}
			$lp++;
			}
			
		return $res;	
		}
	
	
	
	public function getEdition() {
		$field = '250';
		$res = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				foreach ($sf->code as $z) {
					$res[] .= $z;
					}	
				}
			}
			
		return implode(' ',$res);	
		}
	
		
		
	public function getIn() {
		$field = '773';
		$res = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				$sres = [];
				foreach ($sf->code as $k=>$z) {
					if (is_Array($z))
						$z = implode(', ', $z);
					switch ($k) {
						
						case 'x' : 
							$sres[]= 'ISSN '.$z; 
							$this->record->ISSN = $z;
							break;
						case 'q':
						case 'w':
						case '7':
						case '9':
							break;
						default: $sres[]= $z; break;
						}
					
					}	
				$res[] = implode(' ',$sres);	
				}
			}
		return implode('<br/>',$res);	
		}
		
	
	public function getSubjectPersons() {
		$field = 600;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$authors[] = $this->preparePerson($desc);
				} 
			return $authors;	
			} else 
			return null;
		}
	
	public function getRefferedWork() {
		$field = 787;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec[] = $row->code->a;
					
					} 
			return $rec;		
			}
		}
	
	public function getSeria() {
		$field = 490;
		$rec = [];
		$lp = 0;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) {
				$lp++;
				$ln = [];
				if (!empty($row->code->a) && is_array($row->code->a)) 
					$ln[] = implode(' ',$row->code->a);
				if (!empty($row->code->a) && !is_array($row->code->a)) 
					$ln[] = $row->code->a;
				
				if (!empty($row->code->v) && is_array($row->code->v))  
					$ln[] = implode(' ', $row->code->v);
				if (!empty($row->code->v) && !is_array($row->code->v))  
					$ln[] = $row->code->v;
				#echo "<pre>".print_R($ln,1)."</pre>";
				if (is_array($ln))
					$rec[] = implode(' ', $ln);
					else 
					$rec[] = $ln;
				} 
				
			return $rec;		
			}
		}
	
	
	public function getSubjects() {
		$res = [];
		$min = 601;
		$max = 699;
		$lp = 0;
		$uri = [];
		for ($field = $min; $field<=$max; $field++) {
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
				foreach ($this->record->marcFields->$field as $sf) {
					$lp++;
					foreach ($sf->code as $k=>$z) {
						switch ($k) {
							case '0': 
							case '2': 
							case '7': break;
							default: 
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] = urlencode($sz);
										}
									} else {	
									$uri[] = urlencode($z);
									}
							}
						
						}	
					}
				}
			}
		
		return $uri;	
		}
	
	public function getSubjectsFull() {
		$res = [];
		$min = 600;
		$max = 699;
		$lp = 0;
		$uri = [];
		for ($field = $min; $field<=$max; $field++) {
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
				foreach ($this->record->marcFields->$field as $sf) {
					$lp++;
					foreach ($sf->code as $k=>$z) {
						switch ($k) {
							case '0': 
							case '2': 
							case '7': break;
							default: 
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] = urlencode($sz);
										}
									} else {	
									$uri[] = urlencode($z);
									}
							}
						
						}	
					}
				}
			}
		
		return $uri;	
		}
	
	

	
	
	public function getISSN() {
		$fields = [
			'022'=>'a',
			'440'=>'x',
			'490'=>'x',
			'730'=>'x',
			'773'=>'x',
			'776'=>'x',
			'780'=>'x',
			'785'=>'x'
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return $res;
		
		}
	
	public function getArticleISSN() {
		$fields = [
			'773'=>'x',
			];
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return $res;
		
		}
	
	public function getISBN() {
		// isbn = 020a:773z
		$fields = [
			'020'=>'a',
			'773'=>'z',
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return $res;
		
		}
	
	public function getSpellingShingle() {
		// author
		// title 
		// topic 
		
		}
		
		
	public function getContainerTitle2() {
		$fields = [
			'773'=>'s',
			'773'=>'t',
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return str_replace('. -', '', $res);
		
		}
	
	public function getOclcNum() {
		$field = '035';
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec = $row->code->a;
					if (stristr($rec, '(OCoLC)'))
						return str_replace('(OCoLC)', '', $rec);
					} 
			}
		return null;
		}
	
	public function getCtrlNum() {
		$field = '035';
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec[] = $row->code->a;
					
					} 
			return $rec;		
			}
		}
	
	public function getWorkKey() {
		
		$author = $this->getMainAuthorSort();
		$title = $this->str2url($this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b'])));
		
		
		$author = preg_replace("/[^a-z]+/", "", strtolower($author));
		$title = preg_replace("/[^a-z]+/", "", strtolower($title));
	
		return "AT $author $title";
		}
	
	
	
	########################################   
	
	
	
	public function getCurrentTime() {
		return date("Y-m-d").'T'.date("H:i:s").'Z';
		}
		
		
	public function getFullMrc($id) {
		$file = file_get_contents('http://localhost/lite/import/marc21/getMRC.php?id='.$id);
		return $file;
		}
			
	public function getRecFormat() {
		return 'marc';
		}
		

	public function drawTextMarc() {
		if (!empty($this->record->marcFields)) {
			$result = 'LDR  '.$this->record->LEADER."\n";
			foreach ($this->record->marcFields as $field=>$subarr) {
				if (is_Array($subarr))
				foreach ($subarr as $row) {
					$codes = array();
					$value = $ind = ''; 
					$row = (array)$row;
					if (!empty($row['ind1'])) {
						$ind = $row['ind1'];
						if (!empty($row['ind2']))
							$ind .= $row['ind2'];
							else 
							$ind .= ' ';
						}
					if (!empty($row['code'])) {
						foreach ($row['code'] as $code=>$val) 
							if (is_array($val))
								$codes[]='$'.$code.implode('$'.$code, $val);
								else
								$codes[]='$'.$code.$val;
						$value = implode('', $codes);
						if ($ind=='')
							$ind = '  ';
						} 
					$result.="$field  $ind$value\n";
					}
				}
			
			return $result;	
			} else {
			return "no record loaded";	
			}
		}
		

	public function drawMarc() {
		if (!empty($this->record->marcFields)) {
			$result = '<table class="table table-striped">
					<thead><tr><td style="text-align:right"><b>LEADER</b></td><td colspan=3>'.$this->record->LEADER.'</td></tr></thead>
					<tbody>
					';
			foreach ($this->record->marcFields as $field=>$subarr) {
				if (is_Array($subarr))
				foreach ($subarr as $row) {
					$codes = array();
					$value = $ind = ''; 
					$row = (array)$row;
					if (!empty($row['ind1'])) {
						$ind = "<td>$row[ind1]</td>";
						if (!empty($row['ind2']))
							$ind .= "<td>$row[ind2]</td>";
							else 
							$ind .= "<td></td>";
						}
					if (!empty($row['code'])) {
						foreach ($row['code'] as $code=>$val) 
							if (is_array($val))
								$codes[]="<b>|$code</b> ".implode(" <b>|$code</b> ", $val);
								else
								$codes[]="<b>|$code</b> $val ";
						$value = "<td>".implode(' ', $codes)."</td>";
						if ($ind=='')
							$ind = "<td></td><td></td>";
						} 
					if (count($row)==1)
						$value="<td colspan=3>$row[0]</td>";
					if ($value=='')
						$value = '<td></td>';
					$result.="<tr>	
						<td style='text-align:right'><b>$field</b></td>
						$ind
						$value
						</tr>";
					}
				}
			$result.="</tbody></table>";
			
			
			return $result;	
			} else {
			return "no record loaded";	
			}
		}
	
	
	public function currentDate() {
		return date("Y-m-d");
		}	
	public function currentTime() {
		return date("Y-m-d H:i:s");
		}
	
	
	private function removeLastSlash($t1) {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1,'/');
		
		if (($pos>0)and($pos>=strlen($t1)-3))
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
	
	private function removeLastComa($t1) {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1,',');
		
		if (($pos>0)and($pos>=strlen($t1)-3))
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
	
	function str2url( $str, $replace = " " ){
     
        // konwersja znaków utf do znaków podstawowych
        $str = iconv('UTF-8', 'ASCII//IGNORE', $str);
     
        // Niektóre francuskie i niemieckie litery pozostawiają po takiej konwersji (jak powyżej)
        // dodatkowe znaki. Poniższe dwie linijki te znaki wycinają
     
        $charsArr = array( '^', "'", '"', '`', '~');
        $str = str_replace( $charsArr, '', $str );
     
        $return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
        return str_replace(' ', $replace, $return);
        }
	
		
	}

?>