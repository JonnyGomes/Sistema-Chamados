<?php
class ChamadoForm extends TPage
{
    protected $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_chamado');
        $this->form->setFormTitle('Cadastro de Chamado');
        $this->form->setFieldSizes('100%');

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

        // Valores para os combos (com ícones)
        $tipo->addItems([
            'interno' => '🏢 Interno', 
            'externo' => '🌐 Externo'
        ]);
        
        $status->addItems([
            'aberto' => '⏳ Aberto',
            'em_andamento' => '🔄 Em Andamento', 
            'fechado' => '✅ Fechado'
        ]);

        $id->setEditable(FALSE);
        
        // Configurar tamanhos dos campos
        $id->setSize('100%');
        $titulo->setSize('100%');
        $descricao->setSize('100%', 120);
        $observacoes->setSize('100%', 80);
        
        // Configurar máscaras para coordenadas
        $latitude->setMask('9,9999999');
        $longitude->setMask('9,9999999');

        // Layout organizado em seções
        $this->form->appendPage('Informações Principais');
        
        // Seção 1: Identificação
        $this->form->addFields(
            [new TLabel('ID', null, 14, 'b'), $id]
        );
        
        $this->form->addFields(
            [new TLabel('Título *', '#ff0000', 14, 'b'), $titulo]
        );
        
        $this->form->addFields(
            [new TLabel('Descrição *', '#ff0000', 14, 'b'), $descricao]
        );

        // Seção 2: Classificação
        $row1 = $this->form->addFields(
            [new TLabel('Tipo *', '#ff0000', 14, 'b'), $tipo],
            [new TLabel('Status *', '#ff0000', 14, 'b'), $status]
        );
        $row1->layout = ['col-sm-6', 'col-sm-6'];

        // Seção 3: Responsável
        $this->form->addFields(
            [new TLabel('Responsável', null, 14, 'b'), $responsavel_id]
        );

        $this->form->appendPage('Detalhes Adicionais');
        
        // Seção 4: Observações
        $this->form->addFields(
            [new TLabel('Observações', null, 14, 'b'), $observacoes]
        );

        // Seção 5: Coordenadas (comentadas)
        // $this->form->addFields([new TLabel('Latitude')], [$latitude]);
        // $this->form->addFields([new TLabel('Longitude')], [$longitude]);

        // Campos obrigatórios
        $titulo->addValidation('Título', new TRequiredValidator);
        $descricao->addValidation('Descrição', new TRequiredValidator);
        $tipo->addValidation('Tipo', new TRequiredValidator);
        $status->addValidation('Status', new TRequiredValidator);

        // Ações com ícones e cores
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
            new TMessage('info', '✅ Chamado salvo com sucesso!');
            TApplication::gotoPage('ChamadoList');
        } catch (Exception $e) {
            new TMessage('error', '❌ ' . $e->getMessage());
            TTransaction::rollback();
        }
    }
}