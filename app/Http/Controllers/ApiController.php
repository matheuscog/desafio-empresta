<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller 
{    
    /**
     * Exibe todas as intituições.
     */
    public function getInstituicoes()
    {
        return json_decode(file_get_contents(storage_path().'/data/instituicoes.json'), true);
    }

    /**
     * Exibe todos os convenios.
     */
    public function getConvenios()
    {
        return json_decode(file_get_contents(storage_path().'/data/convenios.json'), true);
    }

    /**
     * Realiza a simulação de crédito disponível para o cliente.
     */
    public function simularCredito(Request $request)
    {
        $body = json_decode($request->getContent(), true);
        
        if(!isset($body['valor_emprestimo']) || !is_float($body['valor_emprestimo'])){
            return response()->json([
                "message" => "Valor do empréstimo inválido."
            ], 406);
        } else {
            $json = json_decode(file_get_contents(storage_path().'/data/taxas_instituicoes.json'), true); // JSON com informações
            $informacoes_filtradas = array(); // Será utilizada para armazenar o array final.

            $instituicoes_selecionadas = isset($body['instituicoes']) && sizeof($body['instituicoes']) > 0
                ? $body['instituicoes'] : $this->getTodasInstituicoes();
            $convenios_selecionados = isset($body['convenios']) && sizeof($body['convenios']) > 0
                ? $body['convenios'] : $this->getTodosConvenios();
            $num_parcelas = false;
            
            // Verifica se o número de parcelas enviado é válido
            if(isset($body['parcela'])){
                if($body['parcela'] <= 0){
                    return response()->json([
                        "message" => "Valor da parcela inválida. Este deve ser maior que zero."
                    ], 406);
                } else {
                    $num_parcelas = $body['parcela'];
                }                
            }  

            // Verifica se a(s) instituição enviada existe na base de dados
            foreach($instituicoes_selecionadas as $instituicao){
                if(!in_array($instituicao, $this->getTodasInstituicoes())){
                    return response()->json([
                        "message" => "Instituição(ões) inválida."
                    ], 406);
                }
            }

            // Verifica se o(s) convênio enviado existe na base de dados
            foreach($convenios_selecionados as $convenio){
                if(!in_array($convenio, $this->getTodosConvenios())){
                    return response()->json([
                        "message" => "Convenio(s) inválido."
                    ], 406);
                }
            }

            // Caso o usuário tenha definido o número de parcelas
            if($num_parcelas){
                foreach($instituicoes_selecionadas as $instituicao){
                    $arrayItems = array();
                    foreach ($json as $oportunidade){ 
                        if($oportunidade['instituicao'] == $instituicao && in_array($oportunidade['convenio'], $convenios_selecionados) && $oportunidade['parcelas'] == $num_parcelas){
                            array_push($arrayItems, array(
                                "taxa"          => $oportunidade['taxaJuros'],
                                "parcelas"      => $oportunidade['parcelas'],
                                "valor_parcela" => number_format($body['valor_emprestimo'] * $oportunidade['coeficiente'], 2, '.', ''),
                                "convenio"      => $oportunidade['convenio'],
                            )); 
                        }                    
                    }
                    $informacoes_filtradas = array_merge($informacoes_filtradas, array($instituicao => $arrayItems));
                }
            } else {
                foreach($instituicoes_selecionadas as $instituicao){
                    $arrayItems = array();
                    foreach ($json as $oportunidade){ 
                        if($oportunidade['instituicao'] == $instituicao && in_array($oportunidade['convenio'], $convenios_selecionados)){
                            array_push($arrayItems, array(
                                "taxa"          => $oportunidade['taxaJuros'],
                                "parcelas"      => $oportunidade['parcelas'],
                                "valor_parcela" => number_format($body['valor_emprestimo'] * $oportunidade['coeficiente'], 2, '.', ''),
                                "convenio"      => $oportunidade['convenio'],
                            )); 
                        }                    
                    }
                    $informacoes_filtradas = array_merge($informacoes_filtradas, array($instituicao => $arrayItems));
                }
            }

            return json_encode($informacoes_filtradas);
        }
    }

    private function getTodasInstituicoes()
    {
        $json = json_decode(file_get_contents(storage_path().'/data/taxas_instituicoes.json'), true); // JSON com informações
        $data = array();

        foreach ($json as $item){
            if(!in_array($item['instituicao'], $data)){
                array_push($data, $item['instituicao']);
            }
        }

        return $data;
    }

    private function getTodosConvenios()
    {
        $json = json_decode(file_get_contents(storage_path().'/data/taxas_instituicoes.json'), true); // JSON com informações
        $data = array();

        foreach ($json as $item){
            if(!in_array($item['convenio'], $data)){
                array_push($data, $item['convenio']);
            }
        }

        return $data;
    }
}