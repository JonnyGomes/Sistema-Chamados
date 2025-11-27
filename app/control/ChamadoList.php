<?php
class ChamadoList extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;
    private static $database = 'permission';
    private static $activeRecord = 'Chamado';
    private static $primaryKey = 'id';
    private static $formName = 'form_ChamadoList';

    public function __construct()
    {
        parent::__construct();

        // === FORM ===
        $this->form = new BootstrapFormBuilder(self::$formName);
        $this->form->setFormTitle('Lista de Chamados');

        $status = new TCombo('status');
        $status->addItems([
            'aberto'     => 'Aberto',
            'andamento'  => 'Em andamento',
            'resolvido'  => 'Resolvido',
            'fechado'    => 'Fechado',
        ]);
        $status->setValue(TSession::getValue('filtro_status'));
        $status->setDefaultOption('Todos');

        $this->form->addFields([new TLabel('Status:')], [$status]);
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');

        // === BOTÕES DE EXPORTAÇÃO ===
        $dropdown = new TDropDown('Exportar', 'fa:download');
        $dropdown->addAction('Exportar CSV', new TAction([$this, 'onExportCSV']), 'fa:file-csv blue');
        $dropdown->addAction('Exportar PDF', new TAction([$this, 'onExportPDF']), 'fa:file-pdf red');
        //$dropdown->addAction('Exportar XML', new TAction([$this, 'onExportXML']), 'fa:file-code green');

        $this->form->addHeaderWidget($dropdown);

        // === DATAGRID ===
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $col_id     = new TDataGridColumn('id', 'ID', 'center', '50');
        $col_titulo = new TDataGridColumn('titulo', 'Título', 'left');
        $col_status = new TDataGridColumn('status', 'Status', 'center');
        $col_data_abertura = new TDataGridColumn('data_abertura', 'Data Abertura', 'center');
        $col_solucao = new TDataGridColumn('solucao', 'Solução', 'center');
        $col_data_fechamento = new TDataGridColumn('data_fechamento', 'Data Fechamento', 'center');

        $col_data_abertura->setTransformer(function ($value) {
            if (empty($value)) return '-';
            return (new DateTime($value))->format('d/m/Y H:i');
        });

        $col_data_fechamento->setTransformer(function ($value) {
            if (empty($value)) return '-';
            return (new DateTime($value))->format('d/m/Y H:i');
        });

        $col_status->setTransformer(function ($value) {
            $colors = [
                'aberto'     => 'red',
                'andamento'  => 'orange',
                'resolvido'  => 'green',
                'fechado'    => 'gray'
            ];
            $color = $colors[$value] ?? 'blue';
            return "<span style='color:white; background:{$color}; padding:4px 8px; border-radius:5px'>{$value}</span>";
        });

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_titulo);
        $this->datagrid->addColumn($col_status);
        $this->datagrid->addColumn($col_data_abertura);
        $this->datagrid->addColumn($col_solucao);
        $this->datagrid->addColumn($col_data_fechamento);

        // === AÇÕES ===
        $action_view = new TDataGridAction([$this, 'onView'], ['id' => '{id}']);
        $action_view->setLabel('Visualizar');
        $action_view->setImage('fa:eye gray');

        // NOVO BOTÃO: IMPRIMIR REGISTRO EM PDF
        $action_print = new TDataGridAction([$this, 'onPrintRecord'], ['id' => '{id}']);
        $action_print->setLabel('Imprimir');
        $action_print->setImage('fa:print blue');

        $action_edit = new TDataGridAction([$this, 'onEdit'], ['id' => '{id}']);
        $action_edit->setLabel('Editar');
        $action_edit->setImage('fa:edit green');

        $action_finalizar = new TDataGridAction([$this, 'onFinalizarChamado'], ['id' => '{id}']);
        $action_finalizar->setLabel('Finalizar');
        $action_finalizar->setImage('fa:check orange');

        $action_delete = new TDataGridAction([$this, 'onDeleteConfirm'], ['id' => '{id}']);
        $action_delete->setLabel('Excluir');
        $action_delete->setImage('fa:trash red');

        $this->datagrid->addAction($action_view);
        $this->datagrid->addAction($action_print); // NOVO BOTÃO ADICIONADO
        $this->datagrid->addAction($action_edit);
        $this->datagrid->addAction($action_finalizar);
        $this->datagrid->addAction($action_delete);

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('Chamados', $this->datagrid, $this->pageNavigation));

        parent::add($vbox);
        
        // === CARREGAR OS DADOS AUTOMATICAMENTE ===
        $this->onReload();
    }

    public function onSearch($param)
    {
        $data = $this->form->getData();
        TSession::setValue('filtro_status', $data->status);
        $this->onReload($param);
    }

    public function onReload($param = NULL)
    {
        try {
            TTransaction::open(self::$database);

            $repo = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 20);

            $status = TSession::getValue('filtro_status');
            if (!empty($status)) {
                $criteria->add(new TFilter('status', '=', $status));
            }

            $chamados = $repo->load($criteria, FALSE);
            $this->datagrid->clear();

            if ($chamados) {
                foreach ($chamados as $obj) {
                    $this->datagrid->addItem($obj);
                }
            }

            $this->pageNavigation->setCount($repo->count($criteria));
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit(20);

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /* ============================================================
     * EXPORTAR CSV
     * ============================================================ */
    public function onExportCSV($param)
    {
        try {
            TTransaction::open(self::$database);

            $repo = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;

            $status = TSession::getValue('filtro_status');
            if (!empty($status)) {
                $criteria->add(new TFilter('status', '=', $status));
            }

            $objects = $repo->load($criteria, FALSE);

            if ($objects) {
                $filename = 'Chamados_' . date('Ymd_His') . '.csv';
                $file = fopen($filename, 'w');

                // Cabeçalho
                $columns = $this->datagrid->getColumns();
                $header = [];
                foreach ($columns as $col) {
                    $header[] = $col->getLabel();
                }
                fputcsv($file, $header, ';');

                // Dados
                foreach ($objects as $obj) {
                    $row = [];
                    foreach ($columns as $col) {
                        $name = $col->getName();
                        $row[] = $obj->{$name};
                    }
                    fputcsv($file, $row, ';');
                }

                fclose($file);
                TPage::openFile($filename);
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /* ============================================================
     * EXPORTAR PDF (Versão Simplificada)
     * ============================================================ */
    public function onExportPDF($param)
    {
        try {
            TTransaction::open(self::$database);

            $repo = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;

            $status = TSession::getValue('filtro_status');
            if (!empty($status)) {
                $criteria->add(new TFilter('status', '=', $status));
            }

            $objects = $repo->load($criteria);

            if ($objects) {
                $html = $this->getGridAsHTML($objects);
                $filename = 'Chamados_' . date('Ymd_His') . '.html';
                
                // Salva como HTML (que pode ser impresso como PDF pelo navegador)
                file_put_contents($filename, $html);
                TPage::openFile($filename);
                
                new TMessage('info', 'Relatório gerado como HTML. Use a opção "Imprimir como PDF" do seu navegador.');
            } else {
                new TMessage('info', 'Nenhum dado para exportar.');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', 'Erro ao gerar relatório: ' . $e->getMessage());
            TTransaction::rollback();
        }
    }

    /* ============================================================
     * IMPRIMIR REGISTRO ESPECÍFICO EM PDF
     * ============================================================ */
    public function onPrintRecord($param)
    {
        try {
            TTransaction::open(self::$database);
            
            $chamado = new Chamado($param['id']);
            
            if ($chamado) {
                $html = $this->getRecordAsHTML($chamado);
                $filename = 'Chamado_' . $chamado->id . '_' . date('Ymd_His') . '.html';
                
                // Salva como HTML (que pode ser impresso como PDF pelo navegador)
                file_put_contents($filename, $html);
                TPage::openFile($filename);
                
                new TMessage('info', 'Chamado gerado para impressão. Use a opção "Imprimir como PDF" do seu navegador.');
            } else {
                new TMessage('error', 'Chamado não encontrado.');
            }
            
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', 'Erro ao gerar impressão: ' . $e->getMessage());
            TTransaction::rollback();
        }
    }

    // Método auxiliar para gerar HTML de um registro específico
    private function getRecordAsHTML($chamado)
    {
        $data_abertura = !empty($chamado->data_abertura) ? (new DateTime($chamado->data_abertura))->format('d/m/Y H:i') : '-';
        $data_fechamento = !empty($chamado->data_fechamento) ? (new DateTime($chamado->data_fechamento))->format('d/m/Y H:i') : '-';
        
        // Mapear status para cores
        $status_colors = [
            'aberto'     => '#dc3545',
            'andamento'  => '#fd7e14', 
            'resolvido'  => '#198754',
            'fechado'    => '#6c757d'
        ];
        $status_color = $status_colors[$chamado->status] ?? '#0d6efd';
        
        $html = '
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12pt; 
                    margin: 20px;
                    line-height: 1.6;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px;
                    border-bottom: 2px solid #333;
                    padding-bottom: 20px;
                }
                .title { 
                    font-size: 20pt; 
                    font-weight: bold;
                    color: #333;
                }
                .subtitle {
                    font-size: 14pt;
                    color: #666;
                }
                .info-section {
                    margin-bottom: 25px;
                }
                .info-label {
                    font-weight: bold;
                    color: #333;
                    width: 200px;
                    display: inline-block;
                }
                .info-value {
                    color: #555;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 20px;
                    color: white;
                    font-weight: bold;
                }
                .description-box {
                    border: 1px solid #ddd;
                    padding: 15px;
                    margin: 10px 0;
                    background-color: #f9f9f9;
                    border-radius: 5px;
                }
                .solution-box {
                    border: 1px solid #ddd;
                    padding: 15px;
                    margin: 10px 0;
                    background-color: #f0f8f0;
                    border-radius: 5px;
                }
                .footer {
                    margin-top: 40px;
                    text-align: center;
                    font-size: 10pt;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">RELATÓRIO DE CHAMADO</div>
                <div class="subtitle">Sistema de Gestão de Chamados</div>
            </div>
            
            <div class="info-section">
                <div><span class="info-label">Número do Chamado:</span> <span class="info-value">#' . $chamado->id . '</span></div>
                <div><span class="info-label">Data de Abertura:</span> <span class="info-value">' . $data_abertura . '</span></div>
                <div><span class="info-label">Status:</span> <span class="info-value"><span class="status-badge" style="background-color: ' . $status_color . '">' . strtoupper($chamado->status) . '</span></span></div>
            </div>
            
            <div class="info-section">
                <div class="info-label">Título:</div>
                <div style="font-size: 14pt; font-weight: bold; margin: 10px 0;">' . htmlspecialchars($chamado->titulo) . '</div>
            </div>
            
            <div class="info-section">
                <div class="info-label">Descrição do Problema:</div>
                <div class="description-box">' . nl2br(htmlspecialchars($chamado->descricao ?? 'Não informada')) . '</div>
            </div>';
            
        if (!empty($chamado->solucao)) {
            $html .= '
            <div class="info-section">
                <div class="info-label">Solução Aplicada:</div>
                <div class="solution-box">' . nl2br(htmlspecialchars($chamado->solucao)) . '</div>
            </div>';
        }
        
        if (!empty($chamado->data_fechamento)) {
            $html .= '
            <div class="info-section">
                <div><span class="info-label">Data de Fechamento:</span> <span class="info-value">' . $data_fechamento . '</span></div>
            </div>';
        }
        
        $html .= '
            <div class="footer">
                Documento gerado em: ' . date('d/m/Y H:i') . ' | Página 1 de 1
            </div>
        </body>
        </html>';

        return $html;
    }

    // Método auxiliar para gerar HTML da tabela completa
    private function getGridAsHTML($objects)
    {
        $html = '
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style>
                body { font-family: Arial, sans-serif; font-size: 10pt; }
                table { width: 100%; border-collapse: collapse; }
                th { background-color: #f8f9fa; border: 1px solid #ddd; padding: 8px; text-align: left; }
                td { border: 1px solid #ddd; padding: 8px; }
                .header { text-align: center; margin-bottom: 20px; }
                .title { font-size: 16pt; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">Relatório de Chamados</div>
                <div>Gerado em: ' . date('d/m/Y H:i') . '</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Status</th>
                        <th>Data Abertura</th>
                        <th>Solução</th>
                        <th>Data Fechamento</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($objects as $obj) {
            $data_abertura = !empty($obj->data_abertura) ? (new DateTime($obj->data_abertura))->format('d/m/Y H:i') : '-';
            $data_fechamento = !empty($obj->data_fechamento) ? (new DateTime($obj->data_fechamento))->format('d/m/Y H:i') : '-';
            
            $html .= '
                    <tr>
                        <td>' . $obj->id . '</td>
                        <td>' . htmlspecialchars($obj->titulo) . '</td>
                        <td>' . htmlspecialchars($obj->status) . '</td>
                        <td>' . $data_abertura . '</td>
                        <td>' . htmlspecialchars($obj->solucao ?? '-') . '</td>
                        <td>' . $data_fechamento . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
            <div style="margin-top: 20px; text-align: center;">
                Total de registros: ' . count($objects) . '
            </div>
        </body>
        </html>';

        return $html;
    }

    /* ============================================================
     * EXPORTAR XML
     * ============================================================ */
    public function onExportXML($param)
    {
        try {
            TTransaction::open(self::$database);

            $repo = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;

            $status = TSession::getValue('filtro_status');
            if (!empty($status)) {
                $criteria->add(new TFilter('status', '=', $status));
            }

            $objects = $repo->load($criteria);

            $xml = new SimpleXMLElement('<chamados/>');

            foreach ($objects as $obj) {
                $row = $xml->addChild('chamado');
                foreach ($obj->toArray() as $key => $value) {
                    $row->addChild($key, htmlspecialchars($value));
                }
            }

            $filename = 'Chamados_' . date('Ymd_His') . '.xml';
            $xml->asXML($filename);
            TPage::openFile($filename);

            TTransaction::close();

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    // === VISUALIZAR ===
    public function onView($param)
    {
        try {
            TTransaction::open(self::$database);
            $chamado = new Chamado($param['id']);
            $msg = "<b>Título:</b> {$chamado->titulo}<br>" .
                   "<b>Status:</b> {$chamado->status}<br>" .
                   "<b>Descrição:</b> {$chamado->descricao}<br>" .
                   "<b>Solução:</b> {$chamado->solucao}";
            new TMessage('info', $msg);
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    // === EDITAR ===
    public function onEdit($param)
    {
        try {
            TTransaction::open(self::$database);
            $chamado = new Chamado($param['id']);

            if (in_array($chamado->status, ['resolvido', 'fechado'])) {
                new TMessage('warning', 'Este chamado já foi encerrado, mas pode ser editado se necessário.');
            }

            AdiantiCoreApplication::loadPage('ChamadoForm', 'onEdit', ['id' => $param['id']]);
            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
        }
    }

    // === FINALIZAR (Diálogo) ===
    public static function onFinalizarChamado($param)
    {
        $id = $param['id'];

        $form = new BootstrapFormBuilder('form_finalizar_chamado');
        $form->setFormTitle('Finalizar Chamado');

        $solucao = new TText('solucao');
        $solucao->setSize('100%', 80);
        $id_field = new THidden('id');
        $id_field->setValue($id);

        $form->addFields([new TLabel('Solução:')], [$solucao]);
        $form->addFields([$id_field]);

        $action = new TAction(['ChamadoList', 'finalizarChamado']);
        $form->addAction('Confirmar', $action, 'fa:check green');

        new TInputDialog('Finalizar Chamado', $form);
    }

    // === FINALIZAR (Salvar) ===
    public static function finalizarChamado($param)
    {
        try {
            TTransaction::open(self::$database);

            $id = $param['id'];
            $solucao = trim($param['solucao'] ?? '');

            $chamado = new Chamado($id);
            $chamado->status = 'resolvido';
            $chamado->solucao = $solucao;
            $chamado->data_fechamento = date('Y-m-d H:i:s');
            $chamado->store();

            TTransaction::close();

            new TMessage('info', 'Chamado finalizado e solução registrada com sucesso!');
            AdiantiCoreApplication::loadPage('ChamadoList', 'onReload');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    // === EXCLUSÃO ===
    public static function onDeleteConfirm($param)
    {
        $action = new TAction(['ChamadoList', 'onDelete']);
        $action->setParameters($param);
        new TQuestion('Deseja realmente excluir este chamado?', $action);
    }

    public static function onDelete($param)
    {
        try {
            TTransaction::open(self::$database);

            $repo = new TRepository('ChamadoHistorico');
            $criteria = new TCriteria;
            $criteria->add(new TFilter('chamado_id', '=', $param['id']));
            $repo->delete($criteria);

            $chamado = new Chamado($param['id']);
            $chamado->delete();

            TTransaction::close();

            new TMessage('info', 'Chamado excluído com sucesso!');
            AdiantiCoreApplication::loadPage('ChamadoList', 'onReload');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}