<?php
namespace RADUFU\Resource;

use RADUFU\Service\RelatorioService,
    RADUFU\Service\AtividadeService,
    Tonic\Resource,
    Tonic\Response,
    RADUFU\Model\Professor;
/**
 * @uri /relatorio
 */
class RelatorioResource extends Resource {
/*
    Esta classe esta destinada a gerar o relatorio final, de acordo com as datas informadas utilizando o framework FPDF e FPDI.
*/
    private $relatorioService;

    /**
     * @method POST
     * @provides application/json
     * @json
     */
    public function GerarRelatorio(){
        /*
           $id, $dataI, $dataF,$classe,$nivel,$categorias
        */
        #Criando o dicionario com as opcoes possiveis(classe,nivel[0,1,2])
            // Classe auxiliar
            $pont_ref[0][0] = 120;
            $pont_ref[0][1] = 125;
            $pont_ref[0][2] = 130;

            // Classe assistente
            $pont_ref[1][0] = 138;
            $pont_ref[1][1] = 146;
            $pont_ref[1][2] = 154;

            // Classe adjunto
            $pont_ref[2][0] = 166;
            $pont_ref[2][1] = 178;
            $pont_ref[2][2] = 190;

            // Classe associado
            $pont_ref[3][0] = 214;
            $pont_ref[3][1] = 226;
            $pont_ref[3][2] = 238;
        #limitacao de ensino sera igual para todos
            #$lim_ensi = 0.85; //estou usando no service mesmo

        session_start();
        $professor = $_SESSION["user"];

        if(!(isset($this->request->data->inicio)
            &&isset($this->request->data->fim)
            &&isset($this->request->data->classe)
            &&isset($this->request->data->nivel)
            &&isset($this->request->data->categorias)))
            return new Response(Response::BADREQUEST);

        try {

            // Não usado!!!
            //$data_inicio = $this->data($dataI);
            //$data_final = $this->data($dataF);

            $this->relatorioService = new RelatorioService(
                $professor->getId(),
                $this->request->data->inicio,
                $this->request->data->fim,
                $pont_ref[$this->request->data->classe][$this->request->data->nivel],
                $this->request->data->categorias
                );
            $this->relatorioService->GerarRelatorio();
            return new Response(Response::OK);

        } catch (RADUFU\DAO\NotFoundException $e) {
            throw new Tonic\NotFoundException();
        } catch (RADUFU\DAO\Exception $e) {
            throw new Tonic\Exception($e->getMessage());
        }
    }
    #funcao para formatar a data
    private function data($data)
        {
            $aux = explode("/",$data);
            return $aux[2].'-'.$aux[1].'-'.$aux[0];
        }

    private function download($arquivo)
        {
            // Define o tempo máximo de execução em 0 para as conexões lentas
            set_time_limit(0);
            // Arqui você faz as validações e/ou pega os dados do banco de dados
            $aquivoNome = 'imagem.jpg'; // nome do arquivo que será enviado p/ download
            $arquivoLocal = '/pasta/do/arquivo/'.$aquivoNome; // caminho absoluto do arquivo

            // Verifica se o arquivo não existe

            if (!file_exists($arquivoLocal)) {
                // Exiba uma mensagem de erro caso ele não exista
                exit;
            }
            $novoNome = 'imagem_nova.jpg';
            // Configuramos os headers que serão enviados para o browser

            header('Content-Description: File Transfer');

            header('Content-Disposition: attachment; filename="'.$novoNome.'"');

            header('Content-Type: application/octet-stream');

            header('Content-Transfer-Encoding: binary');

            header('Content-Length: ' . filesize($aquivoNome));

            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

            header('Pragma: public');

            header('Expires: 0');

            readfile($arquivo);
        }

    protected function json() {

        $this->before(function ($request) {
            if ($request->contentType == 'application/json') {
                $request->data = json_decode($request->data);
            }
        });

        $this->after(function ($response) {
         $response->contentType = 'application/json';
         $response->body = json_encode($response->body);
     });
    }
}
?>
