<?php
/**
 * PontoList
 * Lista os registros de ponto com localização
 * @author Jonny
 */
class PontoList extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;
    private static $database = 'permission';
    private static $activeRecord = 'Ponto';
    private static $primaryKey = 'id';
    private static $formName = 'form_PontoList';

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder(self::$formName);
        $this->form->setFormTitle('Controle de Ponto');

        $usuario_id = new TDBCombo('usuario_id', 'permission', 'SystemUser', 'id', 'name');
        $tipo       = new TCombo('tipo');
        $tipo->addItems(['' => 'Todos', 'entrada' => 'Entrada', 'saida' => 'Saída']);
        
        // Campos de data para filtro por período
        $data_inicio = new TDate('data_inicio');
        $data_inicio->setMask('dd/mm/yyyy');
        $data_inicio->setDatabaseMask('yyyy-mm-dd');
        
        $data_fim = new TDate('data_fim');
        $data_fim->setMask('dd/mm/yyyy');
        $data_fim->setDatabaseMask('yyyy-mm-dd');
        
        // Definir datas padrão (últimos 30 dias)
        $data_fim->setValue(date('d/m/Y'));
        $data_inicio->setValue(date('d/m/Y', strtotime('-30 days')));

        $this->form->addFields([new TLabel('Usuário')], [$usuario_id]);
        $this->form->addFields([new TLabel('Tipo')], [$tipo]);
        $this->form->addFields([new TLabel('Data Início')], [$data_inicio]);
        $this->form->addFields([new TLabel('Data Fim')], [$data_fim]);

        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');

        // === BOTÕES DE EXPORTAÇÃO ===
        $dropdown = new TDropDown('Exportar', 'fa:download');
        $dropdown->addAction('Exportar CSV', new TAction([$this, 'onExportCSV']), 'fa:file-csv blue');
        $dropdown->addAction('Exportar PDF', new TAction([$this, 'onExportPDF']), 'fa:file-pdf red');
        //$dropdown->addAction('Exportar XML', new TAction([$this, 'onExportXML']), 'fa:file-code green');

        $this->form->addHeaderWidget($dropdown);

        // === DATAGRID ===
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';

        $col_id       = new TDataGridColumn('id', 'ID', 'center', 50);
        $col_usuario  = new TDataGridColumn('usuario->name', 'Usuário', 'left');
        $col_tipo     = new TDataGridColumn('tipo', 'Tipo', 'center');
        $col_entrada  = new TDataGridColumn('data_hora_entrada', 'Entrada', 'center');
        $col_saida    = new TDataGridColumn('data_hora_saida', 'Saída Meio Dia', 'center');
        $col_entrada2 = new TDataGridColumn('data_hora_entrada_2', 'Entrada Tarde', 'center');
        $col_saida1   = new TDataGridColumn('data_hora_saida_1', 'Saída Final', 'center');
        $col_latlng   = new TDataGridColumn('latitude', 'Latitude', 'right');
        $col_lng      = new TDataGridColumn('longitude', 'Longitude', 'right');

        $this->datagrid->addColumn($col_id);
        $this->datagrid->addColumn($col_usuario);
        $this->datagrid->addColumn($col_tipo);
        $this->datagrid->addColumn($col_entrada);
        $this->datagrid->addColumn($col_saida);
        $this->datagrid->addColumn($col_entrada2);
        $this->datagrid->addColumn($col_saida1);
        $this->datagrid->addColumn($col_latlng);
        $this->datagrid->addColumn($col_lng);

        // Formata as colunas de data/hora
        $col_entrada->setTransformer(function($value) {
            return $value ? date('d/m/Y H:i:s', strtotime($value)) : '-';
        });
        
        $col_saida->setTransformer(function($value) {
            return $value ? date('d/m/Y H:i:s', strtotime($value)) : '-';
        });
        
        $col_entrada2->setTransformer(function($value) {
            return $value ? date('d/m/Y H:i:s', strtotime($value)) : '-';
        });
        
        $col_saida1->setTransformer(function($value) {
            return $value ? date('d/m/Y H:i:s', strtotime($value)) : '-';
        });

        // Formata a coluna tipo para mostrar valores mais amigáveis
        $col_tipo->setTransformer(function($value) {
            $tipos = [
                'entrada' => 'Entrada',
                'saida' => 'Saída'
            ];
            return isset($tipos[$value]) ? $tipos[$value] : $value;
        });

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add($this->form);
        $vbox->add(TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));

        parent::add($vbox);
    }

    public function onReload($param = NULL)
    {
        try {
            TTransaction::open(self::$database);

            $repository = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;
            
            // Aplica filtros se existirem
            $filters = [];
            
            if (!empty($param['usuario_id'])) {
                $filters[] = new TFilter('usuario_id', '=', $param['usuario_id']);
            }
            
            // Filtro por tipo - só aplica se for diferente de vazio (não "Todos")
            if (!empty($param['tipo'])) {
                $filters[] = new TFilter('tipo', '=', $param['tipo']);
            }
            
            // Filtro por período - corrigindo a lógica
            if (!empty($param['data_inicio']) || !empty($param['data_fim'])) {
                $data_inicio = !empty($param['data_inicio']) ? $param['data_inicio'] : '1900-01-01';
                $data_fim = !empty($param['data_fim']) ? $param['data_fim'] : '2100-12-31';
                
                // Cria um grupo de filtros OR para verificar em qualquer campo de data
                $date_filter_group = new TCriteria;
                
                // Verifica data_hora_entrada
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada)', '<=', $data_fim), TExpression::OR_OPERATOR);
                
                // Verifica data_hora_saida
                $date_filter_group->add(new TFilter('DATE(data_hora_saida)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida)', '<=', $data_fim), TExpression::OR_OPERATOR);
                
                // Verifica data_hora_entrada_2
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada_2)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada_2)', '<=', $data_fim), TExpression::OR_OPERATOR);
                
                // Verifica data_hora_saida_1
                $date_filter_group->add(new TFilter('DATE(data_hora_saida_1)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida_1)', '<=', $data_fim), TExpression::OR_OPERATOR);
                
                $filters[] = $date_filter_group;
            }
            
            // Aplica todos os filtros
            foreach ($filters as $filter) {
                $criteria->add($filter);
            }
            
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 20);

            $objects = $repository->load($criteria);

            $this->datagrid->clear();

            if ($objects) {
                foreach ($objects as $obj) {
                    $obj->usuario = new SystemUser($obj->usuario_id);
                    $this->datagrid->addItem($obj);
                }
            }

            $count = $repository->count($criteria);
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($criteria->getProperty('limit'));

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onSearch($param)
    {
        $data = $this->form->getData();
        
        // Preenche os parâmetros de busca
        $filters = [];
        
        if ($data->usuario_id) {
            $filters['usuario_id'] = $data->usuario_id;
        }
        if ($data->tipo) {
            $filters['tipo'] = $data->tipo;
        }
        if ($data->data_inicio) {
            $filters['data_inicio'] = $data->data_inicio;
        }
        if ($data->data_fim) {
            $filters['data_fim'] = $data->data_fim;
        }
        
        // Mantém os dados no formulário
        $this->form->setData($data);
        
        $this->onReload($filters);
    }
    
    public function onClear($param)
    {
        // Limpa os campos do formulário
        $this->form->clear();
        
        // Redefine as datas padrão
        $data_inicio = $this->form->getField('data_inicio');
        $data_fim = $this->form->getField('data_fim');
        
        $data_fim->setValue(date('d/m/Y'));
        $data_inicio->setValue(date('d/m/Y', strtotime('-30 days')));
        
        // Recarrega sem filtros
        $this->onReload();
    }

    /* ============================================================
     * EXPORTAR CSV
     * ============================================================ */
    public function onExportCSV($param)
    {
        try {
            TTransaction::open(self::$database);

            $repository = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;

            // Aplica os mesmos filtros da busca
            $data = $this->form->getData();
            
            if ($data->usuario_id) {
                $criteria->add(new TFilter('usuario_id', '=', $data->usuario_id));
            }
            
            if ($data->tipo) {
                $criteria->add(new TFilter('tipo', '=', $data->tipo));
            }
            
            // Filtro por período
            if ($data->data_inicio || $data->data_fim) {
                $data_inicio = $data->data_inicio ? $data->data_inicio : '1900-01-01';
                $data_fim = $data->data_fim ? $data->data_fim : '2100-12-31';
                
                $date_filter_group = new TCriteria;
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada)', '<=', $data_fim), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida)', '<=', $data_fim), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada_2)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada_2)', '<=', $data_fim), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida_1)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida_1)', '<=', $data_fim), TExpression::OR_OPERATOR);
                
                $criteria->add($date_filter_group);
            }

            $objects = $repository->load($criteria);

            if ($objects) {
                $filename = 'Pontos_' . date('Ymd_His') . '.csv';
                $file = fopen($filename, 'w');

                // Cabeçalho
                $header = [
                    'ID',
                    'Usuário',
                    'Tipo',
                    'Entrada',
                    'Saída Meio Dia', 
                    'Entrada Tarde',
                    'Saída Final',
                    'Latitude',
                    'Longitude'
                ];
                fputcsv($file, $header, ';');

                // Dados
                foreach ($objects as $obj) {
                    $obj->usuario = new SystemUser($obj->usuario_id);
                    
                    $row = [
                        $obj->id,
                        $obj->usuario->name,
                        $obj->tipo == 'entrada' ? 'Entrada' : 'Saída',
                        $obj->data_hora_entrada ? date('d/m/Y H:i:s', strtotime($obj->data_hora_entrada)) : '',
                        $obj->data_hora_saida ? date('d/m/Y H:i:s', strtotime($obj->data_hora_saida)) : '',
                        $obj->data_hora_entrada_2 ? date('d/m/Y H:i:s', strtotime($obj->data_hora_entrada_2)) : '',
                        $obj->data_hora_saida_1 ? date('d/m/Y H:i:s', strtotime($obj->data_hora_saida_1)) : '',
                        $obj->latitude,
                        $obj->longitude
                    ];
                    fputcsv($file, $row, ';');
                }

                fclose($file);
                TPage::openFile($filename);
            } else {
                new TMessage('info', 'Nenhum dado para exportar.');
            }

            TTransaction::close();
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /* ============================================================
     * EXPORTAR PDF
     * ============================================================ */
    public function onExportPDF($param)
    {
        try {
            TTransaction::open(self::$database);

            $repository = new TRepository(self::$activeRecord);
            $criteria = new TCriteria;

            // Aplica os mesmos filtros da busca
            $data = $this->form->getData();
            
            if ($data->usuario_id) {
                $criteria->add(new TFilter('usuario_id', '=', $data->usuario_id));
            }
            
            if ($data->tipo) {
                $criteria->add(new TFilter('tipo', '=', $data->tipo));
            }
            
            // Filtro por período
            if ($data->data_inicio || $data->data_fim) {
                $data_inicio = $data->data_inicio ? $data->data_inicio : '1900-01-01';
                $data_fim = $data->data_fim ? $data->data_fim : '2100-12-31';
                
                $date_filter_group = new TCriteria;
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada)', '<=', $data_fim), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida)', '<=', $data_fim), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada_2)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_entrada_2)', '<=', $data_fim), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida_1)', '>=', $data_inicio), TExpression::OR_OPERATOR);
                $date_filter_group->add(new TFilter('DATE(data_hora_saida_1)', '<=', $data_fim), TExpression::OR_OPERATOR);
                
                $criteria->add($date_filter_group);
            }

            $objects = $repository->load($criteria);

            if ($objects) {
                $html = $this->getGridAsHTML($objects);
                $filename = 'Pontos_' . date('Ymd_His') . '.html';
                
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
                .footer { margin-top: 20px; text-align: center; font-size: 9pt; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">Relatório de Pontos</div>
                <div>Gerado em: ' . date('d/m/Y H:i') . '</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Tipo</th>
                        <th>Entrada</th>
                        <th>Saída Meio Dia</th>
                        <th>Entrada Tarde</th>
                        <th>Saída Final</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($objects as $obj) {
            $obj->usuario = new SystemUser($obj->usuario_id);
            
            $html .= '
                    <tr>
                        <td>' . $obj->id . '</td>
                        <td>' . htmlspecialchars($obj->usuario->name) . '</td>
                        <td>' . ($obj->tipo == 'entrada' ? 'Entrada' : 'Saída') . '</td>
                        <td>' . ($obj->data_hora_entrada ? date('d/m/Y H:i:s', strtotime($obj->data_hora_entrada)) : '-') . '</td>
                        <td>' . ($obj->data_hora_saida ? date('d/m/Y H:i:s', strtotime($obj->data_hora_saida)) : '-') . '</td>
                        <td>' . ($obj->data_hora_entrada_2 ? date('d/m/Y H:i:s', strtotime($obj->data_hora_entrada_2)) : '-') . '</td>
                        <td>' . ($obj->data_hora_saida_1 ? date('d/m/Y H:i:s', strtotime($obj->data_hora_saida_1)) : '-') . '</td>
                        <td>' . $obj->latitude . '</td>
                        <td>' . $obj->longitude . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
            <div class="footer">
                Total de registros: ' . count($objects) . '
            </div>
        </body>
        </html>';

        return $html;
    }
}