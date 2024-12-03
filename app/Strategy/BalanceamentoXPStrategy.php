<?php

namespace App\Strategy;

use App\Models\Guilda;
use Illuminate\Support\Collection;

class BalanceamentoXPStrategy implements BalanceamentoInterface
{
    public function balancear(array $jogadores, array|Guilda|Collection $guildas): array
    {
        $jogadores = collect($jogadores);

        // Verifica se o número total de jogadores é suficiente
        $numMinimoJogadores = $guildas->count() * 4;
        if ($jogadores->count() < $numMinimoJogadores) {
            return ['error' => 'Número insuficiente de jogadores para formar a guilda.'];
        }

        // Organiza jogadores por classe
        $jogadoresPorClasse = $jogadores->groupBy('classe_nome');

        // Verifica se tem ao menos um Mago ou Arqueiro
        $temMagoOuArqueiro = isset($jogadoresPorClasse['Mago']) || isset($jogadoresPorClasse['Arqueiro']);

        if (!$temMagoOuArqueiro) {
            $numGuerreiros = $jogadoresPorClasse->get('Guerreiro')?->count() ?? 0;
            $numClerigos = $jogadoresPorClasse->get('Clérigo')?->count() ?? 0;

            // Verifica se já está equilibrado entre Guerreiros e Clérigos
            $clerigosIdeais = intdiv($numGuerreiros, 2) + ($numGuerreiros % 2);

            if ($numClerigos < $clerigosIdeais) {
                $faltamClerigos = $clerigosIdeais - $numClerigos;
                if ($faltamClerigos > 0) {
                    return ['error' => "Está faltando um Mago ou Arqueiro para completar a guilda. Adicione {$faltamClerigos} Clérigo(s) para equilibrar."];
                }
            } elseif ($numGuerreiros < ($numClerigos * 2)) {
                $faltamGuerreiros = ceil(($numClerigos * 2) / 2) - $numGuerreiros;
                if ($faltamGuerreiros > 0) {
                    return ['error' => "Está faltando um Mago ou Arqueiro para completar a guilda. Adicione {$faltamGuerreiros} Guerreiro(s) para equilibrar."];
                }
            }

            // Se Guerreiros e Clérigos estiverem equilibrados, apenas falta Mago ou Arqueiro
            return ['error' => "Está faltando um Mago ou Arqueiro para completar a guilda."];
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
                    $jogadorMagoArqueiro = $jogadoresPorClasse[$value]->pop();
                    if ($jogadorMagoArqueiro) {
                        $guilda->adicionarJogador($jogadorMagoArqueiro);
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

        foreach ($jogadoresRestantes as $jogador) {
            $guildaMaisFraca = $guildas->sortBy('xp_total')->first();
            if ($guildaMaisFraca->jogadores->count() < $guildaMaisFraca->maximo_jogadores) {
                if ($guildaMaisFraca->adicionarJogador($jogador)) {
                    $guildaMaisFraca->load('jogadores');
                }
            }
        }

        // Validação final: verifica se todas as guildas têm pelo menos 4 jogadores
        foreach ($guildas as $guilda) {
            if ($guilda->jogadores->count() < 4) {
                return ['error' => "A guilda '{$guilda->nome}' não possui o mínimo de 4 jogadores."];
            }
        }

        return $guildas->toArray();
    }

}