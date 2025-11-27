<?php
/**
 * PontoForm
 * Registro de ponto (até 4 registros por dia) com geolocalização e timeline
 * Estrutura: entrada, saida (meio dia), entrada_2 (tarde), saida_1 (final)
 */
class PontoForm extends TPage
{
    protected $form;
    protected $timeline; 

    public function __construct()
    {
        parent::__construct();

        // Formulário (usamos campos ocultos para latitude/longitude e para usuario)
        $this->form = new BootstrapFormBuilder('form_ponto_timeline');
        
        // Campos (visíveis/ocultos)
        $lblDate = new TLabel('Data');
        $lblDate->setProperty('style', 'font-weight:bold');

        $dateNow = new TLabel(date('d/m/Y'));
        $timeNow = new TLabel(date('H:i:s'));
        $timeNow->setId('current_time_label');

        $usuario_id = new THidden('usuario_id');
        $latitude   = new THidden('latitude');
        $longitude  = new THidden('longitude');

        // Campo visível que mostra localização (lat/lng)
        $locationDisplay = new TEntry('location_display');
        $locationDisplay->setEditable(FALSE);
        $locationDisplay->placeholder = 'Aguardando localização...';

        // Hidden for debugging if needed (not added to form)
        $this->form->addContent( [$lblDate, $dateNow, new TLabel(''), $timeNow] );
        $this->form->addFields([new TLabel('LOCALIZAÇÃO')], [$locationDisplay]);
        $this->form->addFields([$usuario_id]); // hidden
        $this->form->addFields([$latitude], [$longitude]); // hidden

        // Botão registrar
        $btn = $this->form->addAction('Registrar Ponto', new TAction([$this, 'onSave']), 'fa:thumbs-up white');
        $btn->class = 'btn btn-primary btn-lg';
        $btn->style = 'width:260px; margin: 12px auto; display:block;';

        // Timeline area (HTML)
        $this->timeline = new TElement('div');
        $this->timeline->id = 'timeline_container';
        $this->timeline->style = 'margin-top:20px;';

        // Painel agrupando form + timeline
        $panel = new TPanelGroup('Registro de Ponto');
        $panel->add($this->form);
        $panel->add($this->timeline);

        parent::add($panel);

        // JS - atualiza relógio a cada segundo e pega geolocalização
        TScript::create("
            // atualiza relógio na tela
            setInterval(function(){
                var now = new Date();
                var hh = ('0'+now.getHours()).slice(-2);
                var mm = ('0'+now.getMinutes()).slice(-2);
                var ss = ('0'+now.getSeconds()).slice(-2);
                var timeStr = hh+':'+mm+':'+ss;
                var lbl = document.getElementById('current_time_label');
                if (lbl) lbl.innerText = timeStr;
            }, 1000);

            // pega geolocalização e preenche campos hidden e visual
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    document.querySelector('input[name=latitude]').value = lat;
                    document.querySelector('input[name=longitude]').value = lng;
                    var display = document.querySelector('input[name=location_display]');
                    if (display) {
                        display.value = lat.toFixed(6) + ' , ' + lng.toFixed(6);
                    }
                }, function(err){
                    console.warn('geolocation error', err);
                    var display = document.querySelector('input[name=location_display]');
                    if (display) display.value = 'Localização indisponível';
                }, { enableHighAccuracy: true, timeout: 10000 });
            } else {
                var display = document.querySelector('input[name=location_display]');
                if (display) display.value = 'Navegador não suporta geolocalização';
            }
        ");

        // Carrega dados existentes para montar timeline
        $this->onReload();
    }

    /**
     * Monta timeline com os dados do dia do usuário logado
     */
    public function onReload($param = NULL)
    {
        try {
            // busca sessão
            $user = TSession::getValue('userid');
            if (!$user) {
                // limpa timeline e exibe mensagem
                $this->timeline->add("<div class='alert alert-warning'>Sessão expirada. Faça login novamente.</div>");
                return;
            }

            TTransaction::open('permission');

            // Busca registro do dia (uma linha por dia)
            $criteria = new TCriteria;
            $criteria->add(new TFilter('usuario_id', '=', $user));
            // verifica data em qualquer campo de data
            $criteria->add(new TFilter('DATE(data_hora_entrada)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_saida)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_saida_1)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_entrada_2)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 1);

            $repository = new TRepository('Ponto');
            $ponto = $repository->load($criteria, FALSE);

            $html = '';

            if ($ponto && count($ponto) > 0) {
                $p = $ponto[0];

                // timeline markup (simples e responsivo)
                $html .= "<div style='display:flex; justify-content:center; margin-top:20px;'>";
                $html .= "<div style='width:800px; max-width:95%;'>";
                $html .= "<div style='position:relative; padding-left:60px;'>";
                // vertical line
                $html .= "<div style='position:absolute; left:30px; top:0; bottom:0; width:4px; background:#e9ecef; border-radius:4px;'></div>";

                // Entrada (manhã)
                if (!empty($p->data_hora_entrada)) {
                    $dtEntrada = (new DateTime($p->data_hora_entrada))->format('H:i:s');
                    $html .= $this->timelineItem('Entrada', $dtEntrada, 'green', -60, 'fa-sign-in-alt');
                } else {
                    $html .= $this->timelineItem('Entrada', '--:--:--', 'gray', -60, 'fa-sign-in-alt');
                }

                // Saída (meio dia)
                if (!empty($p->data_hora_saida)) {
                    $dtSaidaMeioDia = (new DateTime($p->data_hora_saida))->format('H:i:s');
                    $html .= $this->timelineItem('Saída a Tarde', $dtSaidaMeioDia, 'orange', -20, 'fa-utensils');
                } else {
                    $html .= $this->timelineItem('Saída a Tarde', '--:--:--', 'gray', -20, 'fa-utensils');
                }

                // Entrada (tarde)
                if (!empty($p->data_hora_entrada_2)) {
                    $dtEntradaTarde = (new DateTime($p->data_hora_entrada_2))->format('H:i:s');
                    $html .= $this->timelineItem('Entrada a Tarde', $dtEntradaTarde, 'blue', 20, 'fa-sign-in-alt');
                } else {
                    $html .= $this->timelineItem('Entrada a Tarde', '--:--:--', 'gray', 20, 'fa-sign-in-alt');
                }

                // Saída (final)
                if (!empty($p->data_hora_saida_1)) {
                    $dtSaidaFinal = (new DateTime($p->data_hora_saida_1))->format('H:i:s');
                    $html .= $this->timelineItem('Saída Final', $dtSaidaFinal, 'red', 60, 'fa-sign-out-alt');
                } else {
                    $html .= $this->timelineItem('Saída Final', '--:--:--', 'gray', 60, 'fa-sign-out-alt');
                }

                $html .= "</div>"; // relative
                $html .= "</div>"; // container
                $html .= "</div>";
            } else {
                $html .= "<div class='alert alert-info'>Nenhum ponto registrado para hoje.</div>";
            }

            $this->timeline->children = [];
            $this->timeline->add($html);

            TTransaction::close();
        } catch (Exception $e) {
            TTransaction::rollback();
            $this->timeline->add("<div class='alert alert-danger'>Erro ao carregar timeline: {$e->getMessage()}</div>");
        }
    }

    /**
     * Helper para montar um item da timeline
     */
    private function timelineItem($title, $time, $color='green', $offset=0, $icon='fa-circle')
    {
        $bg = '';
        switch($color) {
            case 'green': $bg = '#28a745'; break;
            case 'orange': $bg = '#fd7e14'; break;
            case 'blue': $bg = '#007bff'; break;
            case 'red': $bg = '#dc3545'; break;
            default: $bg = '#6c757d'; break;
        }

        $item  = "<div style='margin:32px 0; position:relative;'>";
        $item .= "<div style='position:absolute; left:0; top:0; width:60px; text-align:center;'>";
        $item .= "<div style='width:40px; height:40px; line-height:40px; border-radius:50%; background:{$bg}; color:#fff; display:inline-block;'>";
        $item .= "<i class='fas {$icon}'></i></div></div>";
        $item .= "<div style='margin-left:80px; background:#fff; border:1px solid #e9ecef; padding:14px; border-radius:6px;'>";
        $item .= "<div style='font-weight:700; color:#333;'>{$title}</div>";
        $item .= "<div style='margin-top:8px; font-size:18px; color:#555;'>{$time}</div>";
        $item .= "</div></div>";

        return $item;
    }

    /**
     * Determina qual tipo de registro deve ser feito baseado no estado atual
     */
    private function determinarProximoRegistro($pontoExistente)
    {
        // Se não existe registro do dia, começa com entrada
        if (!$pontoExistente) {
            return 'entrada';
        }
        
        // Verifica qual é o próximo registro necessário em sequência
        if (empty($pontoExistente->data_hora_entrada)) {
            return 'entrada';
        }
        elseif (empty($pontoExistente->data_hora_saida)) {
            return 'saida';
        }
        elseif (empty($pontoExistente->data_hora_entrada_2)) {
            return 'entrada';
        }
        elseif (empty($pontoExistente->data_hora_saida_1)) {
            return 'saida';
        }
        else {
            throw new Exception('Todos os pontos do dia já foram registrados.');
        }
    }

    /**
     * Ao clicar em Registrar Ponto
     */
    public function onSave($param)
    {
        try {
            // Abre conexão
            TTransaction::open('permission');

            // Sessão
            $user = TSession::getValue('userid');
            if (!$user) {
                throw new Exception('Sessão expirada. Faça login novamente.');
            }

            // pega valores do form (hidden latitude/longitude)
            $data = $this->form->getData();
            $lat = isset($data->latitude) ? $data->latitude : null;
            $lng = isset($data->longitude) ? $data->longitude : null;

            // hora atual do servidor
            $now = new DateTime('now');
            $timeFull = $now->format('Y-m-d H:i:s');

            // busca registro do usuário para HOJE (uma linha por dia)
            $criteria = new TCriteria;
            $criteria->add(new TFilter('usuario_id', '=', $user));
            $criteria->add(new TFilter('DATE(data_hora_entrada)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_saida)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_saida_1)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->add(new TFilter('DATE(data_hora_entrada_2)', '=', date('Y-m-d')), TExpression::OR_OPERATOR);
            $criteria->setProperty('order', 'id desc');
            $criteria->setProperty('limit', 1);

            $repo = new TRepository('Ponto');
            $pontos = $repo->load($criteria, FALSE);
            $ponto = ($pontos && count($pontos)>0) ? $pontos[0] : null;

            // Determina qual é o próximo registro a ser feito
            $proximoRegistro = $this->determinarProximoRegistro($ponto);

            // grava/atualiza
            if (!$ponto) {
                // cria novo registro do dia
                $pontoObj = new Ponto;
                $pontoObj->usuario_id = $user;
                
                // Sempre começa com entrada
                $pontoObj->data_hora_entrada = $timeFull;
                $pontoObj->tipo = 'entrada';
                
                $pontoObj->latitude = $lat;
                $pontoObj->longitude = $lng;
                $pontoObj->store();
            } else {
                // atualiza registro existente
                if ($proximoRegistro == 'entrada') {
                    if (empty($ponto->data_hora_entrada)) {
                        $ponto->data_hora_entrada = $timeFull;
                        $ponto->tipo = 'entrada';
                    } else if (empty($ponto->data_hora_entrada_2)) {
                        $ponto->data_hora_entrada_2 = $timeFull;
                        $ponto->tipo = 'entrada';
                    }
                } else if ($proximoRegistro == 'saida') {
                    if (empty($ponto->data_hora_saida)) {
                        $ponto->data_hora_saida = $timeFull;
                        $ponto->tipo = 'saida';
                    } else if (empty($ponto->data_hora_saida_1)) {
                        $ponto->data_hora_saida_1 = $timeFull;
                        $ponto->tipo = 'saida';
                    }
                }
                
                $ponto->latitude = $lat;
                $ponto->longitude = $lng;
                $ponto->store();
            }

            TTransaction::close();

            // recarrega timeline e exibe mensagem
            $mensagens = [
                'entrada' => 'Ponto de entrada registrado com sucesso!',
                'saida' => 'Ponto de saída registrado com sucesso!'
            ];
            
            new TMessage('info', $mensagens[$proximoRegistro]);
            $this->onReload();

        } catch (Exception $e) {
            TTransaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }
}