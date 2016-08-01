##Sobre o projeto
**rafwell/laravel-grid** é um componente para criação de grids poderosos, com poucas linhas de código. O componente está pronto para funcionar com seu projeto em Bootstrap 3, possui funcionalidades de exportação para Excel ou XLS, pesquisa avançada ou simples, ordenação e ações em linha ou em massa.

##Instalação
1. Adicione ao seu composer.json: ```"rafwell/laravel-grid": "dev-master"``` e execute um ```composer update```.
2. Adicione ao seu ```app/config/app.ph``` o seguinte provider:
    ```
    Rafwell\Grid\GridServiceProvider::class
    ```
3. Execute: ```php artisan vendor:publish```
4. Inclua na sua view ou layout os arquivos js e css. Aqui espero que você já tenha o bootstrap configurado e funcionando no seu ambiente. Este pacote já inclui a versão necessária de cada componente para perfeita utilização, para sua comodidade. Mas você pode usar o CDN do distribuidor se preferir.

###JS e CSS de terceiros
Este pacote foi escrito para trabalhar com bootstrap 3 e Jquery. Utilizamos os seguintes auxiliares, que você deve ter em seu projeto, para correta utilização das funções do sistema:

* [Datetimepicker](https://eonasdan.github.io/bootstrap-datetimepicker/), para pesquisa avançada em campos date e datetime. 
* [Priceformat](http://jquerypriceformat.com/), para pesquisa avançada em campos money.
* [Fontawesome](http://fontawesome.io/), para icones.

Provavelmente você já tem esses componentes em seu sistema, pois são bem comuns em sistemas web.
####Arquivos JS
```
<!-- DEPENDÊNCIAS PARA RODAR LARAVEL-GRID - SÓ INCLUA SE AINDA NÃO ESTIVER USANDO-AS EM SEU PROJETO -->
<script src="vendor/rafwell/data-grid/moment/moment.js"></script>
<script type="text/javascript" src="vendor/rafwell/data-grid/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="vendor/rafwell/data-grid/priceformat/price_format.min.js"></script>

<!-- JS LARAVEL-GRID -->
<script src="vendor/rafwell/data-grid/js/data-grid.js"></script>
```
####Arquivos CSS
```
<!-- DEPENDÊNCIAS PARA RODAR LARAVEL-GRID - SÓ INCLUA SE AINDA NÃO ESTIVER USANDO-AS EM SEU PROJETO -->
<link rel="stylesheet" href="vendor/rafwell/data-grid/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="vendor/rafwell/data-grid/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" />

<!-- CSS LARAVEL-GRID -->
<link rel="stylesheet" href="vendor/rafwell/data-grid/css/data-grid.css">
```

##Um exemplo simples
No seu controller:
```
use Rafwell\Grid\Grid;
```
Na sua função:
```
//Cargo é um model para a tabela cargos.
$Grid = (new Grid(Cargo::query(), 'CargosGridId'))           
    ->campos([
        'id'=>'Código',//id é a coluna no banco de dados e 'Código' é como ela será exibida na tela
        'descricao'=>'Descrição',
        'status'=>[ //Exemplo utilizando campos calculados dentro do banco de dados
          'rotulo'=>'Status do Cargo',
          'campo'=>"case when cargos.status === 1 'Ativo' else 'Inativo' end"
        ]
    ])
    ->acao('Editar', 'admin/cargos/{id}/edit') //Botão editar, entre chaves "{}" qualquer campo que foi utilizado acima, inclusive os calculados. Neste caso: id, descricao ou status
    ->acao('Excluir', 'admin/cargos/{id}', false, false, 'DELETE', 'Deseja realmente excluir este registro?');

return view('suaview',[
    'grid'=>$Grid->make(),
]);
```
Quando $Grid->make() é chamado, um sql semelhante a este será executado:
```
select
  id,
  descricao,
  status
from (
  select 
    id,
    descricao,
    (case when cargos.status === 1 'Ativo' else 'Inativo' end) as status
  from cargos
) s
```
Finalmente, exiba o grid na sua view:
```
{!!$grid!!}
```
A visualização será semelhante a isto:
![Imagem 1 - Grid simples](https://s32.postimg.org/98h570p45/Captura_de_tela_de_2016_08_01_12_12_19.png)

##Um exemplo um pouco mais completo
No exemplo acima, ao realizar uma pesquisa na caixa de buscas todos os campos visíveis no grid são concatenados e pesquisados sob um like '%string%'. Na pesquisa avançada é possível pesquisar campo a campo, strings, inteiros, decimais, datas e horas, com a simples inclusão de uma chamada à função pesquisaAvancada:

```
//Cargo é um model para a tabela cargos.
$Grid = (new Grid(Cargo::query(), 'CargosGridId'))           
    ->campos([
        'id'=>'Código',//id é a coluna no banco de dados e 'Código' é como ela será exibida na tela
        'descricao'=>'Descrição',
        'status'=>[ //Exemplo utilizando campos calculados dentro do banco de dados
          'rotulo'=>'Status do Cargo',
          'campo'=>"case when cargos.status === 1 'Ativo' else 'Inativo' end"
        ]
    ])
    ->acao('Editar', 'admin/cargos/{id}/edit') //Botão editar, entre chaves "{}" qualquer campo que foi utilizado acima, inclusive os calculados. Neste caso: id, descricao ou status
    ->acao('Excluir', 'admin/cargos/{id}', false, false, 'DELETE', 'Deseja realmente excluir este registro?')
    ->pesquisaAvancada([
        'id'=>['tipo'=>'integer','rotulo'=>'Código'],                
        'descricao'=>['tipo'=>'text','rotulo'=>'Descrição'],
        'status'=>['tipo'=>'text','rotulo'=>'Status do Cargo']
    ]);

return view('suaview',[
    'grid'=>$Grid->make(),
]);
```
O resultado incluirá um botão de pesquisa avançada que quando clicado, exibirá o grid da seguinte maneira:
![Imagem 2 - Pesquisa avançada](https://s31.postimg.org/5fz5bpsdn/Captura_de_tela_de_2016_08_01_12_23_24.png)

##Tratando ações em linha
Em alguns casos você pode não querer exibir alguma ação em uma determinada linha. Por exemplo, para um orçamento aprovado, você pode permitir sua exclusão. Para solucionar este cenário, temos a função trataLinha, que pode alterar elementos do grid, inclusive as ações. Esta função é chamada a cada linha e você pode fazer sua verificação e tomar as ações necessárias:
```
->trataLinha(function($linha){
    if($linha['status_proposta_id'] === 2){
      unset($linha['gridAcoes']['Excluir']);
    }
    return $linha;
})
```
##Considerações finais
Este pacote está em produção em diversos projetos que tenho trabalhado, há cerca de 6 meses e tem sido muito bem aceito.
Darei atenção aos PRs e Issues que surgirem com a maior brevidade possível.

Este foi um dos meus primeiros códigos sob laravel. Com certeza há algo para ser melhorado. Se você tem uma sugestão, envie um PR ou abra um Issue para discutirmos sobre a implementação.

A documentação será feita a medida que o projeto for sendo aceito pela comunidade.
