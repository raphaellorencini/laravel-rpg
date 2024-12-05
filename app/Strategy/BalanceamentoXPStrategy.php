<?php

namespace App\Strategy;

use App\Models\Guilda;
use Illuminate\Support\Collection;

class BalanceamentoXPStrategy implements BalanceamentoInterface
{
    public function balancear(array|Collection $jogadores, array|Guilda|Collection $guildas): array
    {
        if (is_array($guildas)) {
            $guildas = collect($guildas);
        }

        if (is_array($jogadores)) {
            $jogadores = collect($jogadores);
        }

        // Organiza jogadores por classe
        $jogadoresPorClasse = $jogadores->groupBy('classe_nome');

        // Verifica se há Guerreiros e Clérigos suficientes
        $numGuildas = $guildas->count();
        $numGuerreiros = $jogadoresPorClasse->get('Guerreiro')?->count() ?? 0;
        $numClerigos = $jogadoresPorClasse->get('Clérigo')?->count() ?? 0;

        // Cálculo do número mínimo necessário
        if ($numGuerreiros < $numGuildas) {
            $faltamGuerreiros = $numGuildas - $numGuerreiros;
            return ['error' => "Número insuficiente de Guerreiros. Adicione {$faltamGuerreiros} Guerreiro(s) para formar as guildas."];
        }

        if ($numClerigos < $numGuildas) {
            $faltamClerigos = $numGuildas - $numClerigos;
            return ['error' => "Número insuficiente de Clérigos. Adicione {$faltamClerigos} Clérigo(s) para formar as guildas."];
        }

        // Verifica se o número total de jogadores é suficiente
        $numMinimoJogadores = $guildas->count();
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
                    return ['error' => "Está faltando um Mago ou Arqueiro para completar a guilda. Adicione ao menos 1 Clérigo para equilibrar."];
                }
            } elseif ($numGuerreiros < ($numClerigos * 2)) {
                $faltamGuerreiros = ceil(($numClerigos * 2) / 2) - $numGuerreiros;
                if ($faltamGuerreiros > 0) {
                    return ['error' => "Está faltando um Mago ou Arqueiro para completar a guilda. Adicione ao menos 1 Guerreiro para equilibrar."];
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
                        $guilda->adicionarJogador($jogador->toArray()); // Método de adicionar jogador à guilda
                    }
                }
            }
        }

        // Adiciona Magos ou Arqueiros em cada guilda
        foreach ($guildas as $guilda) {
            if ($guilda->jogadores->count() >= $guilda->maximo_jogadores) continue;
            $magoArqueiro = ['Mago', 'Arqueiro'];
            shuffle($magoArqueiro);
            foreach ($magoArqueiro as $value) {
                if (isset($jogadoresPorClasse[$value])) {
                    $jogadorMagoArqueiro = $jogadoresPorClasse[$value]->pop();
                    if ($jogadorMagoArqueiro) {
                        $guilda->adicionarJogador($jogadorMagoArqueiro->toArray());
                    }
                }
            }
        }

        // Distribui os demais jogadores balanceando o XP
        $guildasJogadoresIds = $guildas->pluck('jogadores')->flatten()->pluck('id');
        $jogadoresRestantes = $jogadores->filter(function ($jogador) use ($guildasJogadoresIds) {
            return !$guildasJogadoresIds->contains($jogador['id']);
        });

        foreach ($jogadoresRestantes as $jogador) {
            $guildaMaisFraca = $guildas->sortBy('xp_total')->first();
            if ($guildaMaisFraca->jogadores->count() < $guildaMaisFraca->maximo_jogadores) {
                if ($guildaMaisFraca->adicionarJogador($jogador->toArray())) {
                    $guildaMaisFraca->load('jogadores');
                }
            }
        }

        foreach ($guildas as $guilda) {
            $guilda->refresh();
            if ($guilda->jogadores->count() <= 1) {
                return ['error' => "A guilda não possui jogadores suficientes. Ajuste o número total de jogadores para garantir pelo menos 1 Guerreiro e 1 Clérigo por guilda."];
            }
        }

        return $guildas->toArray();
    }

}