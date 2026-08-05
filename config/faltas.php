<?php

return [
    // Horas após o horário de início do agendamento sem check-in para considerar falta
    'horas_para_no_show' => env('FALTAS_HORAS_PARA_NO_SHOW', 2),

    // Prazo que o prestador tem para justificar a falta após ela ser detectada
    'prazo_justificativa_horas' => env('FALTAS_PRAZO_JUSTIFICATIVA_HORAS', 48),

    // Duração do bloqueio de candidatura aplicado quando a falta não é justificada
    'duracao_bloqueio_dias' => env('FALTAS_DURACAO_BLOQUEIO_DIAS', 7),

    // Prazo que o prestador tem, após a confirmação da candidatura, para desistir da demanda sem penalidade
    'janela_desistencia_horas' => env('FALTAS_JANELA_DESISTENCIA_HORAS', 3),
];
