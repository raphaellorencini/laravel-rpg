<?php

namespace App\Strategy;

use App\Models\Guilda;
use Illuminate\Support\Collection;

class BalanceamentoXPStrategy implements BalanceamentoInterface
{
//    public function balancear(array $jogadores, int $numGuildas): array
//    {
//        $guildas = array_fill(0, $numGuildas, ['jogadores' => [], 'xp_total' => 0]);
//        $jogadores = collect($jogadores);
//
//        // Organiza jogadores por classe
//        $jogadoresPorClasse = $jogadores->groupBy('classe.nome');
//
//        // Distribui classes essenciais primeiro
//        $classesEssenciais = ['Clérigo', 'Guerreiro'];
//        foreach ($classesEssenciais as $classe) {
//            for ($i = 0; $i < $numGuildas; $i++) {
//                $jogador = $jogadoresPorClasse[$classe]->pop();
//                if ($jogador) {
//                    $guildas[$i]['jogadores'][] = $jogador;
//                    $guildas[$i]['xp_total'] += $jogador['xp'];
//                }
//            }
//        }
//        for ($i = 0; $i < $numGuildas; $i++) {
//            // Verifica se há Magos disponíveis
//            $mago = $jogadoresPorClasse['Mago']->pop();
//
//            // Se não houver Magos, tenta pegar um Arqueiro
//            if (!$mago) {
//                $arqueiro = $jogadoresPorClasse['Arqueiro']->pop();
//                if ($arqueiro) {
//                    $guildas[$i]['jogadores'][] = $arqueiro;
//                    $guildas[$i]['xp_total'] += $arqueiro['xp'];
//                }
//            } else {
//                // Se houver Mago, adiciona à guilda
//                $guildas[$i]['jogadores'][] = $mago;
//                $guildas[$i]['xp_total'] += $mago['xp'];
//            }
//        }
//
//        $guildas = collect($guildas);
//        $jogadoresAtribuidos = $guildas
//            ->pluck('jogadores')
//            ->flatten(1);
//
//        // Distribui os demais jogadores tentando balancear XP
//        $jogadoresPorId = $jogadores->keyBy('id');
//        $jogadoresAtribuidosPorId = $jogadoresAtribuidos->keyBy('id');
//        $jogadoresRestantesIds = $jogadoresPorId->diffKeys($jogadoresAtribuidosPorId);
//        $jogadoresRestantes = $jogadoresRestantesIds->values();
//
//        foreach ($jogadoresRestantes as $jogador) {
//            $guildaMaisFraca = $guildas->sortBy('xp_total')->first();
//            $guildaMaisFraca['jogadores'][] = $jogador;
//            $guildaMaisFraca['xp_total'] += $jogador['xp'];
//        }
//
//        return $guildas->toArray();
//    }

    public function balancear(array $jogadores, array|Guilda|Collection $guildas): array
    {
        $jogadores = collect($jogadores);

        // Verifica se o número total de jogadores é suficiente
        $numMinimoJogadores = $guildas->count() * 4;
        if ($jogadores->count() < $numMinimoJogadores) {
            return ['erro' => 'Número insuficiente de jogadores para formar a guilda.'];
        }

        // Organiza jogadores por classe
        $jogadoresPorClasse = $jogadores->groupBy('classe.nome');

        // Verifica se tem ao menos um Mago ou Arqueiro
        $temMagoOuArqueiro = isset($jogadoresPorClasse['Mago']) || isset($jogadoresPorClasse['Arqueiro']);
        if (!$temMagoOuArqueiro) {
            return ['erro' => 'Está faltando um Mago ou Arqueiro para completar a guilda.'];
        }

        // Distribui classes essenciais primeiro
        $classesEssenciais = ['Clérigo', 'Guerreiro'];
        foreach ($classesEssenciais as $classe) {
            foreach ($guildas as $guilda) {
                $jogadoresGuilda = collect($guilda['jogadores']);
                if ($jogadoresGuilda->count() >= $guilda['maximo_jogadores']) continue;
                if (isset($jogadoresPorClasse[$classe])) {
                    $jogador = $jogadoresPorClasse[$classe]->pop();
                    if (!empty($jogador)) {
                        $guilda->adicionarJogador($jogador); // Método de adicionar jogador à guilda
                    }
                }
            }
        }
        $guildas->load('jogadores');

        // Adiciona Magos ou Arqueiros em cada guilda
        foreach ($guildas as $guilda) {
            if ($guilda->jogadores->count() >= $guilda->maximo_jogadores) continue;
            $magoArqueiro = ['Mago', 'Arqueiro'];
            shuffle($magoArqueiro);
            foreach ($magoArqueiro as $value) {
                if (isset($jogadoresPorClasse[$value])) {
                    $jogMagoArqueiro = $jogadoresPorClasse[$value]->pop();
                    if ($jogMagoArqueiro) {
                        $guilda->adicionarJogador($jogMagoArqueiro);
                    }
                }
            }
        }
        $guildas->load('jogadores');

        // Distribui os demais jogadores balanceando o XP
        $guildasJogadoresIds = $guildas->pluck('jogadores')->flatten()->pluck('id');
        $jogadoresRestantes = $jogadores->filter(function ($jogador) use ($guildasJogadoresIds) {
            return !$guildasJogadoresIds->contains($jogador['id']);
        });
        //$jogadoresRestantes = $jogadores->diff($guildas->pluck('jogadores')->flatten());
        foreach ($jogadoresRestantes as $jogador) {
            $guildaMaisFraca = $guildas->sortBy('xp_total')->first();
            if ($guildaMaisFraca->jogadores->count() < $guildaMaisFraca->maximo_jogadores) {
                if ($guildaMaisFraca->adicionarJogador($jogador)) {
                    $guildaMaisFraca->load('jogadores'); // Recarrega a relação após adicionar
                }
            }
        }

        // Validação final: verifica se todas as guildas têm pelo menos 4 jogadores
        foreach ($guildas as $guilda) {
            if ($guilda->jogadores->count() < 4) {
                return ['erro' => "A guilda '{$guilda->nome}' não possui o mínimo de 4 jogadores."];
            }
        }

        return $guildas->toArray();
    }

}