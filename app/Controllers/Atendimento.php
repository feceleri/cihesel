<?php
// require './vendor/autoload.php';

namespace App\Controllers;

use App\Models\Legados;
use App\Models\Paciente;
use App\Models\Cadastro;
use App\Models\Medicamento;
use App\Models\Listagem;
use Dompdf\Adapter\CPDF;
use Dompdf\Dompdf;
use Dompdf\Exception;
class  Atendimento extends BaseController
{
    
    public function index()
    {
        $paciente =  new Paciente();
        $post = $this->request->getPost();

        if (!empty($post)) {
            $busca = $post['search'];
            $nc = strpos($busca, "nc:"); 
            $cpf = strpos($busca, "cpf:");           
           
            if (!$nc){                       
                $data = [
                    'resultado' => $paciente->orderBy('nome')->like('nome', $busca)->findAll(),
                    'pager' => $paciente->pager
                ];               
            }
            if(is_int($nc))
            {                
                $busca=str_replace("nc:","",$busca);
                $data = [
                    'resultado' => $paciente->orderBy('nome')->where('id', $busca)->findAll(),
                    'pager' => $paciente->pager
                ];
            }
            if(is_int($cpf)){
                $busca=str_replace("cpf:","",$busca);
                $data = [
                    'resultado' => $paciente->orderBy('nome')->like('cpf', $busca)->findAll(),
                    'pager' => $paciente->pager
                ];  
            }
            

                
        } else {
            $data = [
                'resultado' => $paciente->orderBy('id')->paginate(10),
                'pager' => $paciente->pager
            ];
        }
        echo view('layout/paciente', $data);
    }

    public function salvar()
    {
        $paciente =  new Paciente();

        $post = $this->request->getPost();

        if (!empty($post)) {

            $mensagem = [
                'mensagem' => 'Cadastrado com sucesso!',
                'tipo' => 'alert-success',
            ];

            $dadosBD = [
                "nome" => $post["nomeCompleto"],
                "cpf" => $post["cpf"],
                "rg" => $post["rg"],
                "dataNascimento" => $post["dtNasc"],
                "sexo" => $post["sexo"],
                "nomeMae"     => $post["nomeDaMae"],
                "telefone1" => $post["tel1"],
                "telefone2" => $post["tel2"],
                "cep" => $post["cep"],
                "logradouro" => $post["logradouro"],
                "numeroCasa" => $post["numero"],
                "complementoCasa" => $post["complemento"],
                "cidade" => $post["localidade"],
                "bairro" => $post["bairro"]
            ];

            if (isset($post["id"])) {
                $dadosBD["id"] = $post["id"];
                if ($this->validaCPF($dadosBD["cpf"])) {
                    $paciente->save($dadosBD);
                    $mensagem["mensagem"] =  'Alterado com sucesso!';
                    $this->session->setFlashdata('mensagem', $mensagem);
                    return redirect()->to(base_url('/'));
                } else {
                    $mensagem["mensagem"] =  'CPF Inválido';
                    $mensagem['tipo'] = 'alert-danger';
                    // $dadosBD["cpf"] = "123";
                    // die;
                }
            }

            if ($this->validaCPF($dadosBD["cpf"])) {
                $paciente->save($dadosBD);
                $this->session->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('/'));
            } else {
                $mensagem['mensagem'] = 'Não foi possível cadastrar o paciente! CPF Duplicado! ';
                $mensagem['tipo'] = 'alert-danger';
                $this->session->setFlashdata('mensagem', $mensagem);
            }
        }

        echo view('layout/cadastro');
    }

    public function novoAtendimento()
    {
        $cadastros =  new Paciente();
        $resultado = $cadastros->getAll();
        $data = [
            'resultado' => $resultado
        ];
        echo view('layout/novoAtendimento1', $data);
    }

    public function perfil($id)
    {
        $id = base64_decode($id);
        $cadastros = new  Paciente();
        $resultado = $cadastros->getUser($id);

        $listagemModel =  new Listagem();
        $listagens = $listagemModel->select('listagem.id, listagem.senha, listagem.entrada, listagem.saida')->join('paciente', 'paciente.cpf = listagem.cpfResponsavel', 'listagem.idsAdicional->>"id"=' . $id)->where('paciente.id = ' . $id)->findAll();
        $listagensAdicional = $listagemModel->select('listagem.id, listagem.senha, listagem.entrada, listagem.saida')->join('paciente', 'paciente.cpf = listagem.cpfResponsavel', 'listagem.idsAdicional->>"id"=' . $id)->where("JSON_CONTAINS(idsAdicional, '{\"id\": $id }')")->findAll();
        $data = [
            'resultado' => $resultado,
            'listagens' => $listagens,
            'listagensAdicionais' => $listagensAdicional
        ];
        return view('layout/perfil', $data);
    }

    public function editar($id)
    {
        $id = base64_decode($id);
        $cadastros =  new Paciente();
        $resultado = $cadastros->getUser($id);
        $data = [
            'resultado' => $resultado,
            'editar' => true
        ];
        return view('layout/cadastro', $data);
    }

    public function deletar()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            $paciente =  new Paciente();
            return $this->response->setJSON($paciente->deleteUser($id));
        }
    }

    public function listagem()
    {
        $listagem =  new Listagem();
        $post = $this->request->getPost();

        if (!empty($post)) {
            $busca = $post['search'];
            $senha = strpos($busca, "senha:"); 
            $nome = strpos($busca, "nome:");
            $entrada = strpos($busca, "entrada:");
           
            if (!$senha){                       
                $arrayBd = [
                    'date' => $listagem->orderBy('id', 'desc')->where('senha', $busca)->findAll(),
                    'pager' => $listagem->pager
                ];               
            }
            if(is_int($senha))
            {                
                $busca=str_replace("senha:","",$busca);
                $arrayBd = [
                    'date' => $listagem->orderBy('id', 'desc')->where('senha', $busca)->findAll(),
                    'pager' => $listagem->pager
                ];
            }
            if(is_int($entrada))
            {                
                $busca=str_replace("entrada:","",$busca);
                $busca = new \DateTime($busca);
                $busca=$busca->format('Y-d-m');
                $arrayBd = [
                    'date' => $listagem->orderBy('id', 'desc')->like('entrada', $busca)->findAll(),
                    'pager' => $listagem->pager
                ];
            }
            if(is_int($nome)){
                $busca=str_replace("nome:","",$busca);
                $arrayBd = [
                    'date' => $listagem->orderBy('id', 'desc')->like('nomeResponsavel', $busca)->findAll(),
                    'pager' => $listagem->pager
                ];  
            }
            

                
        } else {
            $arrayBd = [
                'date' => $listagem->orderBy('id', 'desc')->paginate(10),
                'pager' => $listagem->pager
            ];
        }
        echo view('layout/listagem', $arrayBd);
    }

    public function salvarListagem()
    {
        $post = $this->request->getPost();
        if (!empty($post)) {
            $listagem =  new Listagem();
            $db = db_connect();
            $nomeTel = $db->query("SELECT nome, telefone1 FROM paciente WHERE cpf = '".$post["cpfResp"]."'")->getResult();

            $dadosBD = [
                "cpfResponsavel" => $post["cpfResp"],
                "senha" => $post["senha"],
                "qtdReceitaResponsavel" => $post["receitasResponsavel"],
                "idsAdicional" => $post["idsAdicional"],
                "idAdicionalTeste" => $post["idsAdicional"],
                "nomeResponsavel" => $nomeTel[0]->nome,
                "telResponsavel" => $nomeTel[0]->telefone1
            ];

            if ($listagem->save($dadosBD)) {
                $mensagem['mensagem'] = 'Listagem registrada com successo!';
                $mensagem['tipo'] = 'alert-success';
                $this->session->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/listagem'));
            } else {
                $mensagem['mensagem'] = 'Houve um erro no cadastramento, tente novamente!';
                $mensagem['tipo'] = 'alert-danger';
                $this->session->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/listagem'));
            }
        }

        $pessoas =  new Paciente();
        $resultado = $pessoas->getAll();
        $date = [
            'pessoa' => $resultado,
        ];
        echo view('layout/cadastroListagem', $date);
    }

    public function getCpf()
    {
        if ($this->request->isAJAX()) {
            $cpf = $this->request->getPost('cpf');
            $people =  new Paciente();
            $result = $people->where('cpf', $cpf)->find();
            if (!isset($result)) {
                return $this->response->setJSON(false);
            } else {
                return $this->response->setJSON($result);
            }
        }
    }

    public function senha($id)
    {
        $id = base64_decode($id);
        $bdListagem = new listagem();
        $bdPaciente = new paciente();
        $bdListagem->find($id);
        $people = $bdListagem->select('paciente.cpf,paciente.nome,paciente.telefone1,paciente.telefone2,qtdReceitaResponsavel,idsAdicional,listagem.id,listagem.senha,listagem.entrada,listagem.saida')->join('paciente', 'paciente.cpf = listagem.cpfResponsavel')->findAll();

        foreach ($people as $value) {
            if ($value->id == $id) {
                $responsavel = $value;
                if (($responsavel->idsAdicional != '0')) {
                    $idsAdicional = json_decode($responsavel->idsAdicional);
                    $adicionais = [];
                    foreach ($idsAdicional as $idAdicional) {
                        $item = $bdPaciente->find($idAdicional->id);
                        $item->qtd = $idAdicional->qtd;
                        array_push($adicionais, $item);
                    }
                    $date = [
                        'responsavel' => $responsavel,
                        'adicionais' => $adicionais,
                    ];
                    echo view('layout/senha', $date);
                } else {
                    $date = [
                        'responsavel' => $responsavel,
                    ];
                    echo view('layout/senha', $date);
                }
            }
        }
    }

    public function saidaListagem($id)
    {
        $id = base64_decode($id);
        $bd = new listagem;
        $data = [
            'saida' => date("Y/m/d")
        ];
        if ($bd->update($id, $data)) {
            $mensagem['tipo'] = 'alert-success';
            $mensagem['mensagem'] = 'Saída registrada com successo!';
            session()->setFlashdata('mensagem', $mensagem);
            return redirect()->to(base_url('atendimento/listagem'));
        } else {
            $mensagem['tipo'] = 'alert-danger';
            $mensagem['mensagem'] = 'Não foi possível registrar, tente novamente!';
            session()->setFlashdata('mensagem', $mensagem);
        }
    }

    public function saidaListagemManual()
    {
        if ($this->request->getPost()) {
            $bd = new listagem;
            $post = $this->request->getPost();
            if ($post['saida'] > date("Y-m-d")) {
                $mensagem['tipo'] = 'alert-danger';
                $mensagem['mensagem'] = 'A data foi inserida incorretamente!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/listagem'));
            }
            $id = $post['id'];
            $date = [
                'saida' => $post['saida'],
            ];
            if ($bd->update($id, $date)) {
                $mensagem['tipo'] = 'alert-success';
                $mensagem['mensagem'] = 'Saída registrada com successo!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/listagem'));
            } else {
                $mensagem['tipo'] = 'alert-danger';
                $mensagem['mensagem'] = 'Não foi possível regitrar, tente novamente!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/listagem'));
            }
        }
    }

    public function obs()
    {
        if ($this->request->getPost()) {
            $post = $this->request->getPost();
            $date = [
                'obs' => $post['obs'],
            ];
            $id = base64_decode($post['id']);
            $bd = new paciente;
            if ($bd->update($id, $date)) {
                $mensagem['tipo'] = 'alert-success';
                $mensagem['mensagem'] = 'Observação registrada com successo!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/perfil/' . base64_encode($id)));
            } else {
                $mensagem['tipo'] = 'alert-danger';
                $mensagem['mensagem'] = 'Não foi possível regitrar, tente novamente!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/perfil/' . base64_encode($id)));
            }
        }
    }

    public function novos()
    {
        
        if ($this->request->getPost()) {
            $dataPesquisa = $this->request->getPost();
            // $dataPesquisa['dataPaciente'] = new \DateTime($dataPesquisa['dataPaciente']);
            // $dataPesquisa['dataPaciente'] = $dataPesquisa['dataPaciente']->format('Y-d-m');
            
            if (isset($dataPesquisa['dataPaciente'])) {
                echo 'true';
                $bd = new paciente;
                $pacientes = $bd->where('created_at', $dataPesquisa)
                    ->findAll();
                $dados = [
                    'paciente' => $pacientes,
                ];
                return view('layout/novos', $dados);
            } else {
                $bd = new listagem;
                $listagem = $bd->like('entrada', $dataPesquisa['dataListagem'])
                ->findAll();
                $dados = [
                    'listagem' => $listagem,
                ];
                return view('layout/novosListagem', $dados);
            }
        }

        echo view('layout/novos');
    }

    public function novosListagem()
    {
        return view('layout/novosListagem');
    }

    function validaCPF($cpf)
    {

        if ($cpf == "123") {
            return false;
        }
        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    //Função verifica se o CPF já está cadastrado no sistema.
    public function verficaCpf()
    {
        if ($this->request->getPost()) {
            $paciente =  new Paciente();
            $cpf = $this->request->getPost();
            $respostaModel = $paciente->confereCpf($cpf);
            if ($respostaModel) {
                return json_encode(array(
                    'status' => 200,
                    'message' => "Error"
                ));
            } else {
                return json_encode(array(
                    'status' => 200,
                    'message' => "Success"
                ));
            };
        }
    }

    public function incompletos()
    {
        $paciente =  new Paciente();
        $post = $this->request->getPost();

        if (!empty($post)) {
            // $busca = $post['search'];
            // $nc = strpos($busca, "nc:"); 
            // $cpf = strpos($busca, "cpf:");           
           
            // if (!$nc){                       
            //     $data = [
            //         'resultado' => $paciente->orderBy('nome')->like('nome', $busca)->findAll(),
            //         'pager' => $paciente->pager
            //     ];               
            // }
            // if(is_int($nc))
            // {                
            //     $busca=str_replace("nc:","",$busca);
            //     $data = [
            //         'resultado' => $paciente->orderBy('nome')->where('id', $busca)->findAll(),
            //         'pager' => $paciente->pager
            //     ];
            // }
            // if(is_int($cpf)){
            //     $busca=str_replace("cpf:","",$busca);
            //     $data = [
            //         'resultado' => $paciente->orderBy('nome')->like('cpf', '')->findAll(),
            //         'pager' => $paciente->pager
            //     ];  
            // }
            

                
        } else {
            $data = [
                'resultado' => $paciente->orderBy('id')->where('cpf', "")->paginate(10),
                'pager' => $paciente->pager
            ];
        }
        echo view('layout/incompletos', $data);
    }

    public function listagemPDF(){
        $listagem = new Listagem();
        $segment =  $this->request->uri->getSegment(3);
        $dompdf = new Dompdf();
        $dompdf->loadHtml('gfhgf');
        $dompdf->render();
        $dompdf->stream("test.pdf", array("Attachment"=>1));


        if($segment)
        {
            $id = $segment;
            $html_content = '<h3 align="center">Listagem Semanal</h3>';
            $html_content .= $this->htmltopdf_model->fetch_single_details($id);
            $dompdf->loadHtml($html_content);
            $dompdf->render();
            $dompdf->stream("".$id.".pdf", array("Attachment"=>0));
        }
    }

    public function legados(){
        $legado =  new Legados();
        $post = $this->request->getPost();

        if (!empty($post)) {
            $busca = $post['search'];
            $id = strpos($busca, "id:"); 
            $senha = strpos($busca, "senha:");           
           
            if (!$id){                       
                $data = [
                    'resultado' => $legado->orderBy('id')->like('id', $busca)->findAll(),
                    'pager' => $legado->pager
                ];               
            }
            if(is_int($id))
            {                
                $busca=str_replace("id:","",$busca);
                $data = [
                    'resultado' => $legado->orderBy('id')->where('id', $busca)->findAll(),
                    'pager' => $legado->pager
                ];
            }
            if(is_int($senha)){
                $busca=str_replace("senha:","",$busca);
                $data = [
                    'resultado' => $legado->orderBy('id')->like('senha', $busca)->findAll(),
                    'pager' => $legado->pager
                ];  
            }
            

                
        } else {
            $data = [
                'resultado' => $legado->orderBy('id')->paginate(10),
                'pager' => $legado->pager
            ];
        }
        echo view('layout/legados', $data);
    }
    public function editarLegados($id)
    {
        $id = base64_decode($id);
        $atendimentos =  new Legados();
        $resultado = $atendimentos->getService($id);
        $data = [
            'resultado' => $resultado,
            'editar' => true
        ];
        return view('layout/legado', $data);
    }

    public function salvarLegado()
    {
        $legado =  new Legados();

        $post = $this->request->getPost();

        if (!empty($post)) {

            $mensagem = [
                'mensagem' => 'Cadastrado com sucesso!',
                'tipo' => 'alert-success',
            ];

            $dadosBD = [
                "senha" => $post["senha"],
                "idPaciente" => $post["idPaciente"],
                "entrada" => $post["dtEntrada"],
                "obs"     => $post["obs"]
            ];

            if (isset($post["id"])) {
                $dadosBD["id"] = $post["id"];
                
                    $legado->save($dadosBD);
                    $mensagem["mensagem"] =  'Alterado com sucesso!';
                    $this->session->setFlashdata('mensagem', $mensagem);
                    return redirect()->to(base_url('/atendimento/legados'));
                } 
        }

        echo view('layout/legado');
    }

    public function obsLegado()
    {
        if ($this->request->getPost()) {
            $post = $this->request->getPost();
            $date = [
                'obs' => $post['obs'],
            ];
            $id = base64_decode($post['id']);
            $bd = new paciente;
            if ($bd->update($id, $date)) {
                $mensagem['tipo'] = 'alert-success';
                $mensagem['mensagem'] = 'Observação registrada com successo!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/legados/' . base64_encode($id)));
            } else {
                $mensagem['tipo'] = 'alert-danger';
                $mensagem['mensagem'] = 'Não foi possível regitrar, tente novamente!';
                session()->setFlashdata('mensagem', $mensagem);
                return redirect()->to(base_url('atendimento/legados/' . base64_encode($id)));
            }
        }
    }

    public function deletarLegado()
    {
        if ($this->request->isAJAX()) {
            $id = $this->request->getPost('id');
            $legado =  new Legados();
            return $this->response->setJSON($legado->deleteService($id));
        }
    }

}
