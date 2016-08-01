<div class="campos">
<?php
foreach($campos as $campo=>$opts){
	if(!is_array($opts)) $opts = ['tipo'=>$opts];
	if(!isset($opts['rotulo'])) $opts['rotulo'] = ucwords(str_replace('-', ' ', str_slug($campo)));
	switch ($opts['tipo']){
		case 'text': ?>
			<div class="campo {!!$campo!!} {!!isset($valorPesquisado[$campo]) && $valorPesquisado[$campo]!=='' ? 'pesquisado' : ''!!}">
				<label>{!!$opts['rotulo']!!} <span class="remover"><span class="fa fa-remove"></span></span> </label>
				<input type="text" name="pesquisar[][{!!$campo!!}]" value="{{$valorPesquisado[$campo]}}" class="form-control" />
			</div>
		<?php
		break;
		case 'select': ?>
			<div class="campo {!!$campo!!} {!!isset($valorPesquisado[$campo]) && $valorPesquisado[$campo]!=='' ? 'pesquisado' : ''!!}">
				<label>{!!$opts['rotulo']!!} <span class="remover"><span class="fa fa-remove"></span></label>
				<select name="pesquisar[][{!!$campo!!}]" class="form-control">
					<option value=""></option>					
					@foreach($opts['options'] as $value=>$label)					
					<option value="{{$value}}" {!!isset($valorPesquisado[$campo]) && $valorPesquisado[$campo]!=='' && $valorPesquisado[$campo]==$value ? 'selected' : ''!!}>{{$label}}</option>
					@endforeach
				</select>
			</div>
		<?php
		break;
		case 'date':
		case 'datetime': ?>
			<div class="campo duplo {!!$campo!!}">				
				<div class="de {!!isset($valorPesquisado[$campo.'_de']) && $valorPesquisado[$campo.'_de']!=='' ? 'pesquisado' : ''!!}">
					<label>{!!$opts['rotulo']!!} <span class="remover"><span class="fa fa-remove"></span></label>
					<div class="input input-group {!!$opts['tipo']!!} datetimepicker">
						<input type="text" name="pesquisar[][{!!$campo!!}][de]" class="form-control" value="{{isset($valorPesquisado[$campo.'_de']) ? $valorPesquisado[$campo.'_de'] : ''}}">
						<span class="input-group-addon">
	                        <span class="fa fa-calendar"></span>
	                    </span>
					</div>			
				</div>				
				<div class="ate {!!isset($valorPesquisado[$campo.'_ate']) && $valorPesquisado[$campo.'_ate']!=='' ? 'pesquisado' : ''!!}">
					<label><span class="remover"><span class="fa fa-remove"></span></label>
					<div class="input input-group {!!$opts['tipo']!!} datetimepicker">
						<input type="text" name="pesquisar[][{!!$campo!!}][ate]" class="form-control" value="{{isset($valorPesquisado[$campo.'_ate']) ? $valorPesquisado[$campo.'_ate'] : ''}}">
						<span class="input-group-addon">
	                        <span class="fa fa-calendar"></span>
	                    </span>
					</div>			
				</div>		
			</div>
		<?php
		break;
		case 'money': ?>
			<div class="campo duplo {!!$campo!!}">				
				<div class="de {!!isset($valorPesquisado[$campo.'_de']) && $valorPesquisado[$campo.'_de']!=='' ? 'pesquisado' : ''!!}">
					<label>{!!$opts['rotulo']!!} <span class="remover"><span class="fa fa-remove"></span></label>
					<div class="input input-group money">
						<span class="input-group-addon">de:</span>
						<input type="text" name="pesquisar[][{!!$campo!!}][de]" class="form-control" value="{{isset($valorPesquisado[$campo.'_de']) ? $valorPesquisado[$campo.'_de'] : ''}}">
					</div>			
				</div>				
				<div class="ate {!!isset($valorPesquisado[$campo.'_ate']) && $valorPesquisado[$campo.'_ate']!=='' ? 'pesquisado' : ''!!}">
					<label><span class="remover"><span class="fa fa-remove"></span></label>
					<div class="input input-group money">
						<span class="input-group-addon">até:</span>
						<input type="text" name="pesquisar[][{!!$campo!!}][ate]" class="form-control" value="{{isset($valorPesquisado[$campo.'_ate']) ? $valorPesquisado[$campo.'_ate'] : ''}}">
					</div>			
				</div>		
			</div>
		<?php
		break;
		case 'integer':
		case 'numeric': ?>
			<div class="campo duplo {!!$campo!!}">				
				<div class="de {!!isset($valorPesquisado[$campo.'_de']) && $valorPesquisado[$campo.'_de']!=='' ? 'pesquisado' : ''!!}">
					<label>{!!$opts['rotulo']!!} <span class="remover"><span class="fa fa-remove"></span></label>
					<div class="input input-group {!!$opts['tipo']!!}">
						<span class="input-group-addon">de:</span>
						<input type="text" name="pesquisar[][{!!$campo!!}][de]" class="form-control" value="{{isset($valorPesquisado[$campo.'_de']) ? $valorPesquisado[$campo.'_de'] : ''}}">
					</div>			
				</div>				
				<div class="ate {!!isset($valorPesquisado[$campo.'_ate']) && $valorPesquisado[$campo.'_ate']!=='' ? 'pesquisado' : ''!!}">
					<label><span class="remover"><span class="fa fa-remove"></span></label>
					<div class="input input-group {!!$opts['tipo']!!}">
						<span class="input-group-addon">até:</span>
						<input type="text" name="pesquisar[][{!!$campo!!}][ate]" class="form-control" value="{{isset($valorPesquisado[$campo.'_ate']) ? $valorPesquisado[$campo.'_ate'] : ''}}">
					</div>			
				</div>		
			</div>
		<?php
		break;
	}
} ?>
</div>