<?php
namespace Rafwell\Grid;
use Rafwell\Grid\GridController;
use Illuminate\Http\Request;
use DB;
use Rafwell\Grid\Helpers;
use Carbon\Carbon;

class Grid{
	public $query;
	public $id;
	public $campos;
	public $camposAdicionais;
	public $camposSelect = [];	
	public $acoes;
	public $paginaAtual = 1;	
	public $totalPaginas;
	public $total;
	public $valorPesquisado;
	public $camposPesquisa;	
	public $camposWherePesquisa;
	public $checkbox = ['exibir'=>false, 'campo'=>false];
	public $acoesEmMassa;
	public $pesquisaAvancada = false;
	public $pesquisaAvancadaAberta = false;
	public $pesquisaAvancadaCampos = [];
	public $nrRegistrosPorPaginaDisponiveis = [10,20,30,50,100,200];
	public $nrRegistrosPorPagina = 10;
	public $trataLinhaClosure;
	public $exportacao = true;
	public $exibirRegistrosExcluidos = false;
	public $ordemPadrao = []; //['campo', 'direcao']
	private $driverName = '';
	private $permiteExportacao = true;

	function __construct($query, $id){
		$this->query = $query;		
		$this->id = $id;				
		$this->Request = Request::capture();
		$this->driverName = strtolower($this->query->getConnection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));
		return $this;
	}

	public function campos($campos){
		foreach($campos as $k=>$v){
			if(is_string($v)){
				$v = [
					'rotulo'=>$v,
					'campo'=>$k					
				];				
				
			}
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$v['alias'] = substr($k, $strrpos+1);				
			}else{
				$v['alias'] = $k;
			}

			$campos[$k] = $v;
		}

		foreach($campos as $k=>$v){
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$k = substr($k, $strrpos+1);
			}
			$this->camposSelect[$k] = $k;
		}

		$this->campos = $campos;

		return $this;
	}	

	public function acaoCampos($campos){
		$this->acaoCampos = $campos;

		foreach($campos as $k){
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$k = substr($k, $strrpos+1);
			}
			$this->camposSelect[$k] = $k;
		}

		return $this;
	}

	public function acao($titulo, $url, $icone = false, $somenteIcone = false, $metodo = 'GET', $confirm = false){		
		$this->acoes[$titulo] = [
			'titulo'=>$titulo,
			'url'=>$url, 
			'icone'=>$icone, 
			'somenteIcone'=>$somenteIcone,
			'metodo'=>strtoupper($metodo),
			'confirm'=>$confirm
		];
		return $this;
	}

	public function acaoEmMassa($titulo, $url){
		$this->acoesEmMassa[$titulo] = [
			'titulo'=>$titulo,
			'url'=>$url
		];
		return $this;
	}

	public function trataURLParametros($parametros){		
		$parametrosStr = '';
		$parametros['grid'] = $this->id;
		$parametrosStr = http_build_query($parametros);

		if(strlen($parametrosStr)>0) $parametrosStr='?'.$parametrosStr;		
		
		return $parametrosStr;
	}

	public function checkbox($exibir, $campo){
		$this->checkbox['exibir'] = $exibir;
		$this->checkbox['campo'] = $campo;
		return $this;
	}

	public function getUrl($tipo = ''){
		$urlAtual = $this->Request->fullUrl();

		if( strpos($urlAtual, '?') !== false)
			$urlAtual = substr($urlAtual, strpos($urlAtual, '?')+1 );
		else
			$urlAtual = '';

		parse_str($urlAtual, $parametros);

		if(isset($parametros['grid']) && $parametros['grid']!=$this->id){
			unset($parametros['pagina']);
			unset($parametros['pesquisar']);
			unset($parametros['ordem']);
			unset($parametros['direcao']);
		}		

		switch ($tipo) {			
			case 'paginaAnterior':
				if ($this->paginaAtual>1){
					$parametros['pagina'] = $this->paginaAtual-1;					
				}else{					
					$parametros['pagina'] = 1;
				}		
			break;
			case 'proximaPagina':			
				if($this->paginaAtual<$this->totalPaginas){
					$parametros['pagina'] = $this->paginaAtual+1;	
				}else{
					$parametros['pagina'] = $this->paginaAtual;	
				}	
			break;
			case 'paginacao':
				unset($parametros['pagina']);
			break;	
			case 'pesquisa-avancada':
				$parametros['pesquisa-avancada'] = 'true';
				unset($parametros['pesquisar']);
			break;		
			case 'pesquisa-simples':
				unset($parametros['pesquisa-avancada']);
				unset($parametros['pesquisar']);
			break;
			case 'registros-por-pagina':
				unset($parametros['registros-por-pagina']);
			break;
		}

		$url = $this->Request->url().$this->trataURLParametros($parametros);

		return $url;
	}

	public function getCamposRequest(){
		$urlAtual = $this->getUrl();

		if( strpos($urlAtual, '?') !== false)
			$urlAtual = substr($urlAtual, strpos($urlAtual, '?')+1 );
		else
			$urlAtual = '';

		parse_str($urlAtual, $parametros);		

		return $parametros;

	}

	public function trataLinha($closure){
		$this->trataLinhaClosure = $closure;
		return $this;
	}

	public function pesquisaAvancada($opts){
		$this->pesquisaAvancada = true;
		$this->pesquisaAvancadaCampos = $opts;
		foreach($opts as $k=>$v){
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$k = substr($k, $strrpos+1);
			}			

			if(is_array($v) && isset($v['where']) && $v['where']!==false)
				continue;

			$this->camposSelect[$k] = $k;
		}

		foreach ($this->pesquisaAvancadaCampos as $key => &$campo) {
			if(is_string($campo)){
				$campo = [
					'rotulo'=>ucwords( str_replace('_', ' ', $key) ),
					'tipo'=>$campo
				];
			}

			if(!isset($campo['where']))
				$campo['where'] = false;
			else{
				$campo['somenteWhereSub'] = true;
			}

			if(!isset($campo['somenteWhereSub']))
				$campo['somenteWhereSub'] = false;
		}
				
		return $this;
	}

	public function permiteExportacao($bool){
		$this->permiteExportacao = $bool;
		return $this;
	}
	
	public function exportacao($bool){
		$this->exportacao = $bool;
		return $this;
	}

	public function ordemPadrao(array $ordem){		
		$this->ordemPadrao[] = [$ordem[0], (isset($ordem[1]) ? $ordem[1] : 'asc')];		
		return $this;
	}

	public function exibirRegistrosExcluidos($bool){
		$this->exibirRegistrosExcluidos = $bool;
		return $this;
	}

	public function make(){		
		$campos = $this->campos;		

		$selectCampos = [];

		
		foreach($campos as $k=>$v){
			if(strpos($v['campo'], ' ')!==false){
				$v['campo'] = '('.$v['campo'].')';
			}
			if($v['campo'] <> $v['alias']){
				switch ($this->driverName) {
					case 'odbc':
						$selectCampos[] = $v['campo'].' as ['.$v['alias'].']';
					break;
					default:
						$selectCampos[] = $v['campo'].' as '.$v['alias'];
					break;
				}				
			}
			else 
				$selectCampos[] = $v['campo'];
		}

		if(isset($this->acaoCampos)){
			foreach($this->acaoCampos as $campo){
				$this->camposAdicionais[$campo] = $campo;
			}
		}					

		foreach($this->pesquisaAvancadaCampos as $campo=>$opts){
			if($opts['where']!==false || $opts['somenteWhereSub']===true) continue;
				$this->camposAdicionais[$campo] = $campo;
		}		

		if(isset($this->camposAdicionais)){
			foreach($this->camposAdicionais as $campoAdicional){
				$existe = false;
				foreach($this->campos as $campo){
					if($campo['alias']==$campoAdicional || $campo['campo']== $campoAdicional){
						$existe = true;
						break;
					}
				}
				if(!$existe){
					$selectCampos[] = $campoAdicional;
				}
			}
		}
				
		for($i=0;$i<count($selectCampos);$i++){
			$selectCampos[$i] = DB::raw($selectCampos[$i]);
		}

		$this->query->select($selectCampos);
			
		//Cria a subquery
		$bindings = $this->query->getBindings();
		$subQuery = clone($this->query);

		$this->query = $this->query->getModel()->newQuery();

		if($this->Request->grid==$this->id){
			//Paginação
			$this->paginaAtual = $this->Request->pagina ? $this->Request->pagina : 1;
			
			if(isset($this->Request->pesquisar)){
				if(is_string($this->Request->pesquisar)){
					//busca simples
					$this->valorPesquisado = htmlentities($this->Request->pesquisar);
				
					$whereBusca = '';

					foreach($this->campos as $campo=>$rotulo){
						if($strrpos = strrpos($campo, '.'))
							$campo = substr($campo, $strrpos+1);

						switch ($this->driverName) {
							case 'odbc':
								$whereBusca.='+'.$campo;
							break;
							case 'sqlsrv':								
								$whereBusca.="+COALESCE(CAST($campo AS NVARCHAR(MAX)), '')";
							break;
							default:
								$whereBusca.=",COALESCE($campo, '')";
							break;
						}						
					}

					if($whereBusca){
						switch ($this->driverName) {
							case 'odbc':
							case 'sqlsrv':
								//sqlserver < 2012 não tem a função concat
								$whereBusca = substr($whereBusca, 1);
							break;
							default:
								$whereBusca = 'CONCAT('.substr($whereBusca, 1).')';
							break;
						}							
						$this->query->where(DB::raw($whereBusca), 'like', '%'.$this->Request->pesquisar.'%');

					}
				}else{
					//where busca avançada
					for($i=0;$i<count($this->Request->pesquisar);$i++){						

						foreach($this->Request->pesquisar[$i] as $campo=>$valor){							
							if($this->pesquisaAvancadaCampos[$campo]['somenteWhereSub']===true)
								$queryBusca =& $subQuery;
							else
								$queryBusca =& $this->query;

							$campoAux = $campo;							

							if(is_string($valor)){
								$this->valorPesquisado[$campo] = $valor;

								if($valor!=='' && $this->pesquisaAvancadaCampos[$campo]['where']===false){	
									if(is_string($this->pesquisaAvancadaCampos[$campo]) || $this->pesquisaAvancadaCampos[$campo]['tipo']=='text')
										$queryBusca->where($campoAux, 'like', '%'.$valor.'%');
									else									
										$queryBusca->where($campoAux, $valor);
								}
								$valorTratado = $valor;
							}else{
								if(isset($valor['de']) && $valor['de']!=='')
									$valorAux = $valor['de'];
								else
								if(isset($valor['ate']) && $valor['ate']!=='')									
									$valorAux = $valor['ate'];
								else
									$valorAux = '';


								switch ($this->pesquisaAvancadaCampos[$campo]['tipo']) {
									case 'date':
										$valorTratado = Helpers::converteData($valorAux);
									break;
									case 'datetime':
										$valorTratado = Helpers::converteDataHora($valorAux);
									break;
									case 'money':
										$valorTratado = Helpers::converteMoedaReaisMoney($valorAux);
									break;
									case 'integer':
										$valorTratado = (int) $valorAux;
									break;

									case 'numeric':
										$valorTratado = str_replace(',', '.', $valorAux);
									break;
								}

								if(isset($valor['de']) && $valor['de']!==''){
									$this->valorPesquisado[$campo.'_de'] = $valorAux;
									if($this->pesquisaAvancadaCampos[$campo]['where']===false)
										$queryBusca->where($campoAux, '>=', $valorTratado);
								}
								
								if(isset($valor['ate']) && $valor['ate']!==''){
									$this->valorPesquisado[$campo.'_ate'] = $valorAux;
									if($this->pesquisaAvancadaCampos[$campo]['where']===false)
										$queryBusca->where($campoAux, '<=', $valorTratado);
								}
							}

							if($this->pesquisaAvancadaCampos[$campo]['where']){								
								call_user_func($this->pesquisaAvancadaCampos[$campo]['where'], $this, $queryBusca, $valorTratado, $campoAux);
							}
						}
					}
				}
			}			

			//Busca avançada
			if($this->Request['pesquisa-avancada']) $this->pesquisaAvancadaAberta = true;
		}		

		if(method_exists($subQuery->getModel(), 'getQualifiedDeletedAtColumn')){
			$posicaoDeletedAt = mb_strpos($subQuery->toSql(), '`'.$subQuery->getModel()->getTable().'`.`deleted_at` ');				
			if($posicaoDeletedAt!==false && $this->exibirRegistrosExcluidos === false){					
				$deleted_at = mb_substr($subQuery->toSql(), $posicaoDeletedAt);
				
				if(mb_strpos($deleted_at, ' ')!==false){
					//Algumas queries tem group by
					$deleted_at = mb_substr($deleted_at, 0, mb_strpos($deleted_at, 'null')+4);				
				}				

				$subQuery->whereRaw($deleted_at);									
			}else{

				$subQuery->withTrashed();
			}

			$this->query->withTrashed();
		}
		
		$this->query->select('*');		
		
		$bindings2 = $subQuery->getBindings();
		$bindings = $this->query->getBindings();
		$bindings = array_merge($bindings2, $bindings);

		$this->query->from( DB::raw('('.$subQuery->toSql().') '.$this->query->getModel()->getTable().' ') );

		$this->query->setBindings($bindings);		
		
		//Antes de paginar, contar as linhas		
		
		$this->total = $this->query->count();
		
		$this->nrRegistrosPorPagina = (int) $this->Request->get('registros-por-pagina');
		if(!$this->nrRegistrosPorPagina)
			$this->nrRegistrosPorPagina = $this->nrRegistrosPorPaginaDisponiveis[0];

		$this->totalPaginas = intval(ceil(($this->total/$this->nrRegistrosPorPagina)));

		if($this->paginaAtual>$this->totalPaginas)
			$this->paginaAtual = $this->totalPaginas;		
		
		//ordenação		

		if(isset($this->Request->ordem) && isset($this->Request->direcao)){
			if($strrpos = strrpos($this->Request->ordem, '.'))
				$this->Request->ordem = substr($this->Request->ordem, $strrpos+1);

			$this->query->orderBy($this->Request->ordem, ($this->Request->direcao == 'crescente' ? 'asc' : 'desc'));
		}else
		if($this->ordemPadrao){
			foreach($this->ordemPadrao as $ordem){				
				$this->query->orderBy($ordem[0], $ordem[1]);
			}
		}

		if(!$this->exportacao || ($this->exportacao && ($this->Request->get('exportar')!='xls' && $this->Request->get('exportar')!='csv')))
			$this->query->skip(($this->paginaAtual-1)*$this->nrRegistrosPorPagina)->take($this->nrRegistrosPorPagina);		

		//executar query
		
		$linhas = $this->query->get()->toArray();
		if($this->exportacao && ($this->Request->get('exportar')=='xls' || $this->Request->get('exportar')=='csv')){
			array_unshift($linhas, $this->campos);
			$excel = \App::make('excel');

			$excel->create($this->id.' - '.Carbon::now(), function($excel) use($linhas) {				
			    $excel->sheet('Sheetname', function($sheet) use($linhas){
			    	for($i=0; $i<count($linhas); $i++){
			    		if($i===0){
			    			$cabecalho = [];
			    			foreach($linhas[$i] as $k=>$v){
			    				$cabecalho[] = $v['rotulo'];
			    			}
			    			$sheet->appendRow($cabecalho);
			    		}else{			    			
			    			$sheet->appendRow($linhas[$i]);
			    		}
					}			        
			    });

			})->download( $this->Request->get('exportar') );

		}
	    $nrLinhas = count($linhas);

	    //parser das ações
	    if(isset($this->acoes)){
	      for($i = 0; $i<$nrLinhas; $i++){
	        foreach($this->acoes as $acao){
	          if(strpos($acao['url'], '{')!==false){
	            //Existe variável para substituir na url
	            foreach($linhas[$i] as $campo=>$valor){   
	              if($campo<>'gridAcoes')
	                $acao['url'] = str_replace('{'.$campo.'}', $valor, $acao['url']);
	            }           
	          }
	          $linhas[$i]['gridAcoes'][$acao['titulo']] = $acao;
	        }
	      }
	    }

	    if($this->trataLinhaClosure){
	    	for($i = 0; $i<count($linhas); $i++){	 
	    		$linhas[$i] = call_user_func($this->trataLinhaClosure, $linhas[$i]);
	    	}
	    }	    

	    //renderiza o grid
	    return \View::make('grid::grid', [
	      'linhas'=>$linhas,
	      'total'=>$this->total,
	      'campos'=>$this->campos,
	      'acoes'=>$this->acoes,
	      'paginaAtual'=>$this->paginaAtual,	      
	      'totalPaginas'=>$this->totalPaginas,
	      'id'=>$this->id,
	      'urlPaginaAnterior'=>$this->getUrl('paginaAnterior'),
	      'urlProximaPagina'=>$this->getUrl('proximaPagina'),
	      'url'=>$this->getUrl(),
	      'valorPesquisado'=>$this->valorPesquisado,
	      'camposRequest'=>$this->getCamposRequest(),
	      'urlPaginacao'=>$this->getUrl('paginacao'),
	      'checkbox'=>$this->checkbox,
	      'acoesEmMassa'=>$this->acoesEmMassa,
	      'pesquisaAvancada'=>$this->pesquisaAvancada,
	      'pesquisaAvancadaAberta'=>$this->pesquisaAvancadaAberta,
	      'pesquisaAvancadaCampos'=>$this->pesquisaAvancadaCampos,
	      'urlPesquisaAvancada'=>$this->getUrl('pesquisa-avancada'),
	      'urlPesquisaSimples'=>$this->getUrl('pesquisa-simples'),
	      'nrRegistrosPorPagina'=>$this->nrRegistrosPorPagina,
	      'nrRegistrosPorPaginaDisponiveis'=>$this->nrRegistrosPorPaginaDisponiveis,
	      'urlRegistrosPorPagina'=>$this->getUrl('registros-por-pagina'),
	      'exportacao'=>$this->exportacao,
	      'permiteExportacao'=>$this->permiteExportacao,
	      'urlExportacao'=>$this->getUrl('urlExportacao')
	    ]);
	}

}