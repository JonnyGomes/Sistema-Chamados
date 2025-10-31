<?php
class ChamadoForm extends TPage
{
    protected $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_chamado');
        $this->form->setFormTitle('📝 Cadastro de Chamado');

        // Campos do formulário
        $id            = new TEntry('id');
        $titulo        = new TEntry('titulo');
        $descricao     = new TText('descricao');
        $tipo          = new TCombo('tipo');
        $status        = new TCombo('status');
        $responsavel_id = new TDBCombo('responsavel_id', 'permission', 'SystemUser', 'id', 'name');
        $observacoes   = new TText('observacoes');
        $latitude      = new TEntry('latitude');
        $longitude     = new TEntry('longitude');

        // Valores para os combos
        $tipo->addItems(['interno'=>'Interno', 'externo'=>'Externo']);
        $status->addItems(['aberto'=>'Aberto', 'em_andamento'=>'Em andamento', 'fechado'=>'Fechado']);

        $id->setEditable(FALSE);
        
        // Configurar máscaras para coordenadas
        $latitude->setMask('9,9999999');
        $longitude->setMask('9,9999999');

        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Título *')], [$titulo]);
        $this->form->addFields([new TLabel('Descrição *')], [$descricao]);
        $this->form->addFields([new TLabel('Tipo *')], [$tipo]);
        $this->form->addFields([new TLabel('Status *')], [$status]);
        $this->form->addFields([new TLabel('Responsável')], [$responsavel_id]);
        $this->form->addFields([new TLabel('Observações')], [$observacoes]);
        $this->form->addFields([new TLabel('Latitude')], [$latitude]);
        $this->form->addFields([new TLabel('Longitude')], [$longitude]);

        // Campos obrigatórios
        $titulo->addValidation('Título', new TRequiredValidator);
        $descricao->addValidation('Descrição', new TRequiredValidator);
        $tipo->addValidation('Tipo', new TRequiredValidator);
        $status->addValidation('Status', new TRequiredValidator);

        $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addAction('Voltar', new TAction(['ChamadoList', 'onReload']), 'fa:arrow-left blue');

        parent::add($this->form);
    }

    public function onEdit($param)
    {
        try {
            if (isset($param['id'])) {
                TTransaction::open('permission');
                $object = new Chamado($param['id']);
                $this->form->setData($object);
                TTransaction::close();
            } else {
                $this->form->clear();
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSave($param)
    {
        try {
            TTransaction::open('permission');
            $this->form->validate();
            $data = $this->form->getData();

            $object = new Chamado;
            $object->fromArray((array)$data);
            
            // PREENCHER CAMPOS OBRIGATÓRIOS QUE NÃO ESTÃO NO FORMULÁRIO
            $object->usuario_abertura_id = TSession::getValue('userid');
            $object->data_abertura = date('Y-m-d H:i:s');
            
            // Se for um novo chamado, garantir que o status seja 'aberto'
            if (empty($data->id)) {
                $object->status = 'aberto';
            }
            
            // Se estiver fechando o chamado, preencher data_fechamento
            if ($data->status == 'fechado' && empty($data->data_fechamento)) {
                $object->data_fechamento = date('Y-m-d H:i:s');
            }
            
            // Se estiver reabrindo, limpar data_fechamento
            if ($data->status != 'fechado' && !empty($data->data_fechamento)) {
                $object->data_fechamento = null;
            }
            
            $object->store();

            TTransaction::close();
            new TMessage('info', 'Chamado salvo com sucesso!');
            TApplication::gotoPage('ChamadoList');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}