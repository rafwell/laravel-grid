<div class="grid-container" id="grid{{$id}}">
	@if ($pesquisaAvancada && $pesquisaAvancadaAberta === true)
		<div class="pesquisa pesquisa-avancada {{isset($valorPesquisado) && $valorPesquisado<>'' ? 'realizada' : ''}}">
			<form action="{{$url}}" method="get">
				@foreach($camposRequest as $campo=>$valor)
					@if ($campo<>'pesquisar')
						<input type="hidden" name="{{$campo}}" value="{{$valor}}">
					@endif
				@endforeach
				<fieldset>
					<legend>Pesquisa avançada</legend>
					@include('grid::pesquisaAvancada', ['campos'=>$pesquisaAvancadaCampos])
					<button class="bt-pesquisar-pesquisa-avancada btn btn-default" type="submit" title="Pesquisar">
						<span class="fa fa-search"> </span> Pesquisar
					</button>
					<a href="{{$urlPesquisaSimples}}" class="btn btn-default" title="Pesquisa Simples"><span class="fa fa-search-minus"></span></a>
					@if ($total>0)
						<span class="total-info pull-right">Página {{$paginaAtual}} de {{$totalPaginas}}. Total de {{$total}} registro{{$total>1 ? 's': ''}}.</span>
					@endif
				</fieldset>
			</form>
		</div>
	@else
		<div class="pesquisa pesquisa-simples {{isset($valorPesquisado) && $valorPesquisado<>'' ? 'realizada' : ''}}">		
			<form action="{{$url}}" method="get">			
				@foreach($camposRequest as $campo=>$valor)
					@if ($campo<>'pesquisar')
						<input type="hidden" name="{{$campo}}" value="{{$valor}}">
					@endif
				@endforeach
		      	<input type="text" name="pesquisar" class="form-control input-pesquisar" placeholder="Pesquisar por..." value="{{$valorPesquisado}}">
		        <button class="bt-pesquisar btn btn-default" type="submit" title="Pesquisar"><span class="fa fa-search"></span></button>		      	
		      	@if (isset($valorPesquisado) && $valorPesquisado<>'')				      		
			       	<button class="bt-limpar-pesquisa btn btn-default" type="button" title="Limpar pesquisa"><span class="fa fa-remove"></span></button>
		      	@endif
		      	@if ($pesquisaAvancada && $pesquisaAvancadaAberta === false)
					<a href="{{$urlPesquisaAvancada}}" class="bt-pesquisa-avancada btn btn-default" title="Pesquisa Avançada"><span class="fa fa-search-plus"></span></a>
		      	@endif
		    </form>
			@if ($total>0)
				<span class="total-info">Página {{$paginaAtual}} de {{$totalPaginas}}. Total de {{$total}} registro{{$total>1 ? 's': ''}}.</span>
			@endif
		</div>
	@endif
	
	<div class="row">
		<div class="col-md-8">
			@if($acoesEmMassa)
			<div class="acoes-em-massa">
				<select name="grid_acao_em_massa" class="grid_acao_em_massa form-control" data-token="{{ csrf_token() }}">
					<option value="">Aplicar aos selecionados</option>
					@foreach($acoesEmMassa as $acao)
					<option value="{{$acao['url']}}">{!!$acao['titulo']!!}</option>
					@endforeach
				</select>		
			</div>
			@endif	
		</div>
		<div class="col-md-4">
			<div class="exibindo-registros-info pull-right">
				<span>Exibindo </span>
				<select name="registros-por-pagina" data-url="{{$urlRegistrosPorPagina}}">
					@foreach($nrRegistrosPorPaginaDisponiveis as $nr)
					<option value="{!!$nr!!}" {!!$nr==$nrRegistrosPorPagina ? 'selected' : '' !!}>{!!$nr!!}</option>
					@endforeach
				</select>
				<span>registros por página.</span>
			</div>
		</div>
	</div>	
	<div class="table-responsive">
		<table class="table table-bordered table-striped table-hover table-condensed grid">
			<thead>
				<tr>
					@if($checkbox['exibir'])
						<th>
							<input type="checkbox" class="selecionar-todos">
						</th>
					@endif					
					@foreach ($campos as $k=>$v)						
						<th class="{!!$v['alias']!!}">
							<div class="setas">
								<a href="{{$url}}&ordem={!!$v['alias']!!}&direcao=crescente" title="Ordenar crescente" class="seta-cima"></a>
								<a href="{{$url}}&ordem={!!$v['alias']!!}&direcao=decrescente" title="Ordenar decrescente" class="seta-baixo"></a>
							</div>
							<span>{{$v['rotulo']}}</span>
						</th>					
					@endforeach
					@if (isset($acoes))
						<th class="acoes">Ações</th>
					@endif
				</tr>
			</thead>			
			<tbody>
				@if (isset($linhas) && count($linhas)>0)
					@foreach ($linhas as $linha)			
						<tr>
							@if($checkbox['exibir'])
								<td>
									<input class="grid-checkbox" type="checkbox" name="grid_checkbox_{!!$checkbox['campo']!!}" value="{!!$linha[$checkbox['campo']]!!}"/>
								</td>
							@endif
							@foreach ($campos as $k=>$v)																
								<td class="campo {!!$v['alias']!!}">{!!$linha[$v['alias']]!!}</td>
							@endforeach
							@if (isset($acoes))
								<td class="acoes">
									@foreach ($linha['gridAcoes'] as $acao)	
										@if($acao['metodo']=='GET')								
											<a href="{!!$acao['url']!!}" title="{{$acao['titulo']}}" class="btn btn-xs acao btn-default">
												@if (isset($acao['icone']))
													<span class="{{$acao['icone']}}"></span>
												@endif
												@if ($acao['somenteIcone']===false)
													{{$acao['titulo']}}
												@endif
											</a>
										@else
											<form action="{!!$acao['url']!!}" method="POST" <?php echo ($acao['confirm']!==false ? 'onsubmit="if(!confirm(\''.addslashes(htmlentities($acao['confirm'])).'\')){event.preventDefault; return false;}; "' : '' ); ?> >
												{{csrf_field()}}
												<input type="hidden" name="_method" value="{!!$acao['metodo']!!}">
												<button type="submit" title="{{$acao['titulo']}}" class="btn btn-xs acao btn-default">
													@if (isset($acao['icone']))
														<span class="{{$acao['icone']}}"></span>
													@endif
													@if ($acao['somenteIcone']===false)
														{{$acao['titulo']}}
													@endif
												</button>
											</form>
										@endif
									@endforeach
								</td>
							@endif
						</tr>
					@endforeach
				@else
					<tr>
						<td colspan="{!!isset($acoes) ? count($campos)+($checkbox['exibir'] ? 1 : 0)+1 : count($campos)+($checkbox['exibir'] ? 1 : 0) !!}" class="nenhum-registro-encontrado">
							<span>Nenhum registro encontrado.</span>
						</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if (isset($linhas) && count($linhas)>0)
	<div class="row">
		<div class="col-md-{!! $totalPaginas>1 ? '9' : '12' !!}">	
			@if($permiteExportacao)
			<div class="input-group">				
				<select name="exportar" class="form-control">
					<option value="">Selecione um formato para exportação</option>
					<option value="xls">XLS</option>
					<option value="csv">CSV</option>
				</select>
				<a href="#" data-href="{{$urlExportacao}}" target="_blank" class="input-group-addon bt-exportar" title="Exportar"><span class="fa fa-download"></span> Exportar</a>
			</div>		
			@endif
		</div>
		<div class="col-md-3">
			@if ($totalPaginas>1)
				<div class="nav-pagination">
		            <div class="input-group">
						<a href="{!!$urlPaginaAnterior!!}" class="direcao input-group-addon" title="Página anterior">
							<span class="fa fa-chevron-left"></span>
						</a>
		                <select class="form-control select-pagina" data-url="{!!$urlPaginacao!!}">
		                	@for($i=1; $i<=$totalPaginas;$i++)
			                    <option value="{!!$i!!}" {{$i==$paginaAtual ? 'selected' : ''}} >Página {!!$i!!}</option>
			                @endfor
		                </select>
						<a href="{!!$urlProximaPagina!!}" class="direcao input-group-addon" title="Próxima página">
							<span class="fa fa-chevron-right"></span>
						</a>	                
		            </div>
		        </div>
			@endif	
		</div>
	</div>
	@endif
</div>