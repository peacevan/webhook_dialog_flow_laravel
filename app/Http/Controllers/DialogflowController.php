<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class DialogflowController extends Controller
{
    public function handle(Request $request)
    {
        $result=$this->getMenuProduto('bebidas');

       try{
        $data = $request->json()->all();
        $intent = $data['queryResult']['intent']['displayName'];
        $parameters = $data['queryResult']['parameters'];

        if ($intent === 'produto.custo') {
            $produto = $parameters['produto'];
            $sabor = $parameters['produtoOpcoes'];
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

        // Lógica para outras intenções, se necessário
        return response()->json(['fulfillmentText' => 'Intent não reconhecida.']);
    }catch(Exception $e){
        die($e->getMessage());
      return response()->json(['error'=>$e->getMessage()]);
     }
    }
    // Função simulada para obter o preço do produto buscar do banco.
    protected function getPrecoDoProduto($produto, $sabor=null)
    {
        //vindo do banco
        $precosProdutos = [
            'pizza' => [
                'calabresa' => 'R$ 20.00',
                'mussarela' => 'R$ 25.00',
                'margherita' => 'R$ 22.00',
                'frango_com_catupiry' => 'R$ 28.00',
                'quatro_queijos' => 'R$ 26.00',
                'portuguesa' => 'R$ 24.00',
                'pepperoni' => 'R$ 27.00',
                'atum' => 'R$ 23.00',
                'bacon' => 'R$ 29.00',
                'vegetariana' => 'R$ 26.00',
                'palmito' => 'R$ 25.00',
                'camarao' => 'R$ 32.00',
                'funghi' => 'R$ 28.00',
                'rucula_com_tomate_seco' => 'R$ 30.00',
                'chocolate' => 'R$ 18.00',
                'banana_com_canela' => 'R$ 17.00',
                'romeu_e_julieta' => 'R$ 19.00',
                'bauru' => 'R$ 21.00',
                'escarola' => 'R$ 22.00',
                'brocolis_com_bacon' => 'R$ 31.00',
            ],
            'almoço' => [
                'executivo' => 'R$ 50.00',
                'comercial' => 'R$ 50.00',
                'vegetariano' => 'R$ 45.00',
                'fitness' => 'R$ 55.00',
                'infantil' => 'R$ 35.00',
                'picanha' => 'R$ 60.00',
                'salada_ceasar' => 'R$ 48.00',
                'massa_carbonara' => 'R$ 52.00',
                'salmão_grelhado' => 'R$ 58.00',
                'frango_parmesão' => 'R$ 54.00',
            ],
        ];
        //se não informou o sabor retorna o preço de todo os produto informado
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
         //vindo do banco
         $precosProdutos = [
            'pizza' => [
                'calabresa' => 'R$ 20.00',
                'mussarela' => 'R$ 25.00',
                'margherita' => 'R$ 22.00',
                'frango_com_catupiry' => 'R$ 28.00',
                'quatro_queijos' => 'R$ 26.00',
                'portuguesa' => 'R$ 24.00',
                'pepperoni' => 'R$ 27.00',
                'atum' => 'R$ 23.00',
                'bacon' => 'R$ 29.00',
                'vegetariana' => 'R$ 26.00',
                'palmito' => 'R$ 25.00',
                'camarao' => 'R$ 32.00',
                'funghi' => 'R$ 28.00',
                'rucula_com_tomate_seco' => 'R$ 30.00',
                'chocolate' => 'R$ 18.00',
                'banana_com_canela' => 'R$ 17.00',
                'romeu_e_julieta' => 'R$ 19.00',
                'bauru' => 'R$ 21.00',
                'escarola' => 'R$ 22.00',
                'brocolis_com_bacon' => 'R$ 31.00',
            ],
            'almoço' => [
                'executivo' => 'R$ 50.00',
                'comercial' => 'R$ 50.00',
                'vegetariano' => 'R$ 45.00',
                'fitness' => 'R$ 55.00',
                'infantil' => 'R$ 35.00',
                'picanha' => 'R$ 60.00',
                'salada_ceasar' => 'R$ 48.00',
                'massa_carbonara' => 'R$ 52.00',
                'salmão_grelhado' => 'R$ 58.00',
                'frango_parmesão' => 'R$ 54.00',
            ],
            'bebidas' => [
                'água_mineral' => 'R$ 3.00',
                'refrigerante_lata' => 'R$ 5.00',
                'refrigerante_2l' => 'R$ 10.00',
                'suco_natural' => 'R$ 7.00',
                'suco_caixa' => 'R$ 6.00',
                'café' => 'R$ 4.00',
                'chá' => 'R$ 4.50',
                'cerveja' => 'R$ 8.00',
                'vinho_tinto' => 'R$ 20.00',
                'coquetel_sem_álcool' => 'R$ 6.50',
            ],
        ];

        return $precosProdutos[$produto]??$precosProdutos;


    }

    function getMenuProduto($produto){
           //se não informou o sabor retorna o preço de todo os produto informado
       $listaopcoes=$this->buscaProduto($produto);
       $responseText = "Menu   $produto :\n";
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
}
