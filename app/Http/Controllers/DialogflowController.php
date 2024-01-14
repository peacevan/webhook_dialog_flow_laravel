<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class DialogflowController extends Controller
{
    public function handle(Request $request)
    {

        // Obtenha a mensagem do usuário do Dialogflow
        $userMessage = $request->input('query');
        // Log da solicitação recebida
        Log::info('Solicitação recebida: ' . json_encode($request->all()));


        // Substitua 'SEU_TOKEN' pelo token real do seu projeto Dialogflow
        $token ="a5d2c6e0083321e2560084769fbf0e2acb7b4f54=";
        $dialogflowResponse = (new Client)->post('https://api.dialogflow.com/v1/query', [
            'headers' => [
                'Authorization' => 'Bearer a5d2c6e0083321e2560084769fbf0e2acb7b4f54',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'v' => '20150910',
                'query' => $userMessage,
                'lang' => 'pt-BR',  // Substitua pelo idioma desejado
            ],
        ]);

        // Decodifica a resposta JSON
        $dialogflowData = json_decode($dialogflowResponse->getBody(), true);

        // Obtém a resposta do Dialogflow
        $botReply = $dialogflowData['result']['fulfillment']['speech'];

        // Simule uma função getPrecoDoProduto para produto.custo
        $produto = $dialogflowData['result']['parameters']['Produto'];
        $precoProduto = $this->getPrecoDoProduto($produto);

        // Construa a resposta com o preço do produto, se disponível
        $respostaCompleta = $botReply;
        if ($precoProduto) {
          $respostaCompleta .= " O preço do produto é: " . $precoProduto;
        }

         // Log da resposta enviada
         Log::info('Resposta enviada: ' . json_encode($respostaCompleta));
         // Retorna a resposta ao Dialogflow
         return response()->json(['fulfillmentText' => $respostaCompleta]);
    }

    // Função simulada para obter o preço do produto
    protected function getPrecoDoProduto($produto)
    {
        // Simulação de preços para alguns produtos
        $precos = [
            'Pizza Margherita' => 'R$ 20.00',
            'Almoço Executivo' => 'R$ 15.00',
        ];

        return $precos[$produto] ?? null;
    }
}
