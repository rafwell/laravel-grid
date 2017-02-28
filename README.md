##Sobre o projeto
**rafwell/laravel-grid** é um componente para criação de grids poderosos, com poucas linhas de código. O componente está pronto para funcionar com seu projeto em Bootstrap 3, possui funcionalidades de exportação para Excel ou XLS, pesquisa avançada ou simples, ordenação e ações em linha ou em massa.

##Good news!
A new version of this package is comming! The new version will be support multi-language features to expand our limits! If you're anxious like us, contribute with the development sounds good. Follow the changes on the new repository: [rafwell/laravel-easygrid](https://github.com/rafwell/laravel-easygrid). This one repository will be descontinued when the new is ready for production apps. All current features will be work on the new version, small modifications on structure (for best use) and the code will be rewriten to english. With this, it will not be possible to maintain a compatibility between the current and the new. However, because it is another repository, and another namespace, you can work with the two simultaneously, performing the migration smoothly.

Boas notícias - Uma nova versão deste pacote está chegando! A nova versão será compatível com recursos multi-idioma para expandir nossos limites! Se você está ansioso como nós, pense em contribuir para o desenvolvimento. Siga as alterações no novo repositório: [rafwell/laravel-easygrid](https://github.com/rafwell/laravel-easygrid). Este repositório será descontinuado quando o novo estiver pronto para aplicações de produção. Todos os recursos atuais serão incluidos na nova versão, pequenas modificações na estrutura (para melhor uso) e o código será reescrito para o inglês. Com isto, não será possível manter uma compatibilidade entre o atual e o novo. Porém, por se tratar de outro repositório, e outro namespace, você poderá trabalhar com os dois simultaneamente, realizando a migração de maneira suave.

##Compatibilidade
**rafwell/laravel-grid** é compatível com Laravel 5.2+

##Instalação
1. Adicione a dependência ao seu composer.json ```composer require "rafwell/laravel-grid"```.
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
//Produto é um model para a tabela produtos
$Grid = (new Grid(Produto::query(), 'ProdutoGridId'))           
    ->campos([
        'id'=>'Código',//id é a coluna no banco de dados e 'Código' é como ela será exibida na tela
        'descricao'=>'Descrição',
        'status'=>[ //Exemplo utilizando campos calculados dentro do banco de dados
          'rotulo'=>'Ativo',
          'campo'=>"case when ativo = 1 then 'Sim' else 'Não' end"
        ],
        'created_at'=>'Data Criação',
        'preco'=>'Preço'
    ])
    ->acao('Editar', 'admin/produtos/{id}/edit') //Botão editar, entre chaves "{}" qualquer campo que foi utilizado acima, inclusive os calculados. Neste caso: id, descricao ou status
    ->acao('Excluir', 'admin/produtos/{id}', false, false, 'DELETE', 'Deseja realmente excluir este registro?')
    ->pesquisaAvancada([
    	'id'=>['rotulo'=>'Código','tipo'=>'integer'],
    	'created_at'=>['rotulo'=>'Data Criação','tipo'=>'date'],
    	'descricao'=>['rotulo'=>'Descrição','tipo'=>'text'],
    	'preco'=>['rotulo'=>'Preço','tipo'=>'money'],
    	
    ])->trataLinha(function($linha){
    	$linha['created_at'] = date('d/m/Y', strtotime($linha['created_at']));
    	//O campo preço está sendo formatado via mutators dentro do model Produto
    	return $linha;
    });
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
    (case when ativo = 1 then 'Sim' else 'Não' end) as status
  from produtos
) s
```
Finalmente, exiba o grid na sua view:
```
{!!$grid!!}
```
A visualização será semelhante a isto:
![Imagem 1 - Grid simples](https://s31.postimg.org/5me80xvrf/Captura_de_tela_de_2016_08_01_20_23_54.png)

##Um exemplo um pouco mais completo
No exemplo acima, ao realizar uma pesquisa na caixa de buscas todos os campos visíveis no grid são concatenados e pesquisados sob um like '%string%'. Na pesquisa avançada é possível pesquisar campo a campo, strings, inteiros, decimais, datas e horas, com a simples inclusão de uma chamada à função pesquisaAvancada.

```
//Produto é o model da tabela produtos
$Grid = (new Grid(Produto::query(), 'ProdutoGridId'))           
    ->campos([
        'id'=>'Código',//id é a coluna no banco de dados e 'Código' é como ela será exibida na tela
        'descricao'=>'Descrição',
        'status'=>[ //Exemplo utilizando campos calculados dentro do banco de dados
          'rotulo'=>'Ativo',
          'campo'=>"case when ativo = 1 then 'Sim' else 'Não' end"
        ],
        'created_at'=>'Data Criação',
        'preco'=>'Preço'
    ])
    ->acao('Editar', 'admin/produtos/{id}/edit') //Botão editar, entre chaves "{}" qualquer campo que foi utilizado acima, inclusive os calculados. Neste caso: id, descricao ou status
    ->acao('Excluir', 'admin/produtos/{id}', false, false, 'DELETE', 'Deseja realmente excluir este registro?')
    ->pesquisaAvancada([
        'id'=>['rotulo'=>'Código','tipo'=>'integer'],
        'created_at'=>['rotulo'=>'Data Criação','tipo'=>'date'],
        'descricao'=>['rotulo'=>'Descrição','tipo'=>'text'],
        'preco'=>['rotulo'=>'Preço','tipo'=>'money'],
        
    ])->trataLinha(function($linha){
        $linha['created_at'] = date('d/m/Y', strtotime($linha['created_at']));
        //O campo preço está sendo formatado via mutators dentro do model Produto
        return $linha;
    });

return view('suaview',[
    'grid'=>$Grid->make(),
]);
```
O resultado incluirá um botão de pesquisa avançada que quando clicado, exibirá o grid da seguinte maneira:
![Imagem 2 - Pesquisa avançada](https://s32.postimg.org/5k1ncfw11/Captura_de_tela_de_2016_08_01_20_21_04.png)

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
##License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
