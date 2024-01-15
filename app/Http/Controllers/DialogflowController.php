<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class DialogflowController extends Controller
{
    public function handle(Request $request)
    {


        try{
        $data = $request->json()->all();
        $intent = $data['queryResult']['intent']['displayName'];
        $parameters = $data['queryResult']['parameters'];

        if ($intent === 'produto.custo') {
            $produto = $parameters['produto'];
            $sabor  = $parameters['sabores'];
            $precoPizza = $this->getPrecoDoProduto($produto, $sabor);
            $response = [
                'fulfillmentText' =>$precoPizza,
            ];
            return response()->json($response);
        }
         if ($intent=='produto.opcoes') {
            $data = $request->json()->all();
            $intent = $data['queryResult']['intent']['displayName'];
            $parameters = $data['queryResult']['parameters'];
            $produto = $parameters['produto'];
            $opcao = $parameters['produtoOpcoes'];

            if ($produto &&  $opcao){
               $result= $this->findProdutoSabor($produto, $opcao); //realizar busca no banco de dados
               $result =" Sim Temos, O preço da $produto  $opcao é  $result";
               return response()->json(['fulfillmentText'=>$result]);
            }
            $result=$this->getMenuProduto($produto);
            return response()->json(['fulfillmentText'=>$result]);
         }


         if ($intent=='cardapio.almoco') {
            $imagemcardapio['imageUri']= "https://www.graficaprintcenter.com.br/uploads/produtos/cardapio-simples-plastificado-a5-15x21cm-papel-plastificado-compactado-corte-reto-4x0-colorido-frent-1637961459203315144861a14ef345891.jpg";
            $imagemcardapio['accessibilityText']="Cardapio refeição";
            return response()->json(['fulfillmentText'=>json_encode($imagemcardapio)]);
         }

         return response()->json(['fulfillmentText' => 'Intent não reconhecida.']);
    }catch(Exception $e){
      die($e->getMessage());
      return response()->json(['error'=>$e->getMessage()]);
     }
    }
    protected function getPrecoDoProduto($produto, $sabor=null)
    {
        $precosProdutos= json_decode($this->getProdutoJson(),TRUE);


        if (!$sabor) {
            $responseText = "Os preços  são:\n";
            $i=0;
            foreach ($precosProdutos[$produto] as $produto => $preco) {
                $i++;
                $responseText .= "\n    $produto  : $preco \n";
            }
            return $responseText ?? null;
        }

        return  "o preço da ".$produto . " - ".  $sabor." é : ".$precosProdutos[$produto][strtolower($sabor)] ?? null;
    }
    //buscar do banco de dados
    function buscaProduto($produto=null){
        $precosProdutos= json_decode($this->getProdutoJson(),true);

        return $precosProdutos[$produto]??$precosProdutos;
    }
    function getMenuProduto($produtoP){

       $listaopcoes=$this->buscaProduto($produtoP);

       $responseText = "Menu   $produtoP :\n";
            $i=0;
            foreach ($listaopcoes as $produto => $preco) {
               $i++;
                $responseText .= "\n     $produto  :  $preco \n";
            }

            return $responseText ?? null;
    }
    function findProdutoSabor($produto,$produtoOpcoes){

        $listaopcoes=$this->buscaProduto($produto);

        return  $listaopcoes[$produtoOpcoes];
    }
    function getProdutoJson(){
        return file_get_contents(storage_path('produtos.json'));
      }
}
