<?php
namespace RADUFU\Resource;

use RADUFU\Service\AtividadeService,
    Tonic\Resource,
    Tonic\Response;
/**
 * @uri /atividade
 * @uri /atividade/:id
 */
class AtividadeResource extends Resource {

    private $atividadeService = null;

    /**
     * @method GET
     * @provides application/json
     * @json
     * @param int $id
     * @return Tonic\Response
     */
    public function buscar($id = null) {
        if(is_null($id))
                throw new Tonic\MethodNotAllowedException();

        try {
            $this->atividadeService = new AtividadeService();
            return new Response( Response::OK, $this->atividadeService->search($id) );

        } catch (\RADUFU\DAO\NotFoundException $e) {
            throw new \Tonic\NotFoundException();
        }
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @param int $idProfessor
     * @return Tonic\Response
     */
    public function buscarPorProfessor($idProfessor = null) {
        if(is_null($idProfessor))
                throw new Tonic\MethodNotAllowedException();
        try {
            $this->comprovanteService = new ComprovanteService();
            return new Response( Response::OK, $this->comprovanteService->searchAll($idProfessor) );

        } catch (RADUFU\DAO\NotFoundException $e) {
            throw new Tonic\NotFoundException();
        }
    }

    /**
     * @method GET
     * @provides application/json
     * @json
     * @param int $id
     * @return Tonic\Response
     */
    public function dependente($id = null) {
        if(is_null($id))
            throw new Tonic\MethodNotAllowedException();
        if(!(isset($this->request->data->escolha)))
            return new Response(Response::BADREQUEST);

        try{
            $this->atividadeService = new AtividadeService();

            return new Response(
                Response::OK, $this->atividadeService->getDependency(
                    $id,
                    $this->request->data->escolha
                    ));
        } catch (RADUFU\DAO\NotFoundException $e) {
            throw new Tonic\NotFoundException();
        }
    }

    /**
     * @method POST
     * @provides application/json
     * @json
     * @return Tonic\Response
     */
    public function criar($professor = null) {
        if(is_null($professor))
            throw new Tonic\MethodNotAllowedException();
        if(!(isset($this->request->data->descricao)
            &&isset($this->request->data->datainicio)
            &&isset($this->request->data->datafim)))
            return new Response(Response::BADREQUEST);

        try {
            $this->atividadeService = new AtividadeService();
            $criado = $this->atividadeService->getNextId();

            $this->atividadeService->post(
                    $this->request->data->tipo,
                    $$this->request->data->descricao,
                    $this->request->data->datainicio,
                    $this->request->data->datafim,
                    $professor
                    );

            return new Response(Response::CREATED, array(
                'uri' => $criado
                ));

        } catch (RADUFU\DAO\Exception $e) {
            throw new Tonic\Exception($e->getMessage());
        }
    }

    /**
     * @method PUT
     * @provides application/json
     * @json
     * @return Tonic\Response
     */
    public function atualizar($id = null) {
        if(is_null($id))
            throw new Tonic\MethodNotAllowedException();
        if(!(isset($this->request->data->campo)
            &&isset($this->request->data->modificacao)))
            return new Response(Response::BADREQUEST);

        try {
            $this->atividadeService = new AtividadeService();
            $this->atividadeService->update(
                    $id,
                    $this->request->data->campo,
                    $this->request->data->modificacao
                    );

            return new Response(Response::OK);

        } catch (RADUFU\DAO\NotFoundException $e) {
            throw new Tonic\NotFoundException();
        } catch (RADUFU\DAO\Exception $e) {
            throw new Tonic\Exception($e->getMessage());
        }

    }

    /**
     * @method DELETE
     * @provides application/json
     * @json
     * @return Tonic\Response
     */
    public function remover($id = null) {
        if(is_null($id))
            throw new Tonic\MethodNotAllowedException();

        try {
            $this->atividadeService = new AtividadeService();
            $this->atividadeService->delete($id);

            return new Response(Response::OK);

        } catch (RADUFU\DAO\NotFoundException $e) {
            throw new Tonic\Exception($e->getMessage());
        }
    }

    /**
     * Transforma as requisições json para array e as repostas array para json
     */
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
