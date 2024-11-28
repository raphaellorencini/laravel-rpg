xxx
{{--<div class="space-y-4">--}}
{{--    @if ($jogadores->isEmpty())--}}
{{--        <p class="text-gray-500">Nenhum jogador associado a esta guilda.</p>--}}
{{--    @else--}}
{{--        <ul class="list-disc pl-5">--}}
{{--            @foreach ($jogadores as $jogador)--}}
{{--                <li>{{ $jogador->nome }} ({{ $jogador->classe->nome }} - {{ $jogador->xp }} XP)</li>--}}
{{--            @endforeach--}}
{{--        </ul>--}}
{{--    @endif--}}
{{--</div>--}}

{{--<table class="table-auto w-full border-collapse border border-gray-300 rounded-lg">--}}
{{--        <thead>--}}
{{--        <tr class="_bg-gray-300 bg-[#242526]">--}}
{{--                <th class="border border-gray-300 px-4 py-2">Nome</th>--}}
{{--                <th class="border border-gray-300 px-4 py-2">E-mail</th>--}}
{{--                <th class="border border-gray-300 px-4 py-2">Classe</th>--}}
{{--                <th class="border border-gray-300 px-4 py-2">Imagem</th>--}}
{{--                <th class="border border-gray-300 px-4 py-2">Confirmado</th>--}}
{{--        </tr>--}}
{{--        </thead>--}}
{{--        <tbody>--}}
{{--        @foreach ($jogadores as $jogador)--}}
{{--                <tr class="_hover:bg-gray-50 hover:bg-[#242526]">--}}
{{--                        <td class="border border-gray-300 px-4 py-2">{{ $jogador->user->name }}</td>--}}
{{--                        <td class="border border-gray-300 px-4 py-2">{{ $jogador->user->email }}</td>--}}
{{--                        <td class="border border-gray-300 px-4 py-2">{{ $jogador->classe->nome }}</td>--}}
{{--                        <td class="border border-gray-300 px-4 py-2 text-center">--}}
{{--                                <img src="{{ asset($jogador->image) }}" alt="Imagem do Jogador" class="w-20 h-20 rounded-full mx-auto">--}}
{{--                        </td>--}}
{{--                        <td class="border border-gray-300 px-4 py-2 text-center">--}}
{{--                                @if ($jogador->confirmado)--}}
{{--                                        <span class="text-green-500 font-bold">✔</span>--}}
{{--                                @else--}}
{{--                                        <span class="text-red-500 font-bold">✖</span>--}}
{{--                                @endif--}}
{{--                        </td>--}}
{{--                </tr>--}}
{{--        @endforeach--}}
{{--        </tbody>--}}
{{--</table>--}}

{{--<x-filament::page>--}}
{{--        <h1>Jogadores da Guilda: {{ $guilda->nome }}</h1>--}}

{{--        <x-filament::table :data="$jogadores">--}}
{{--                <x-slot name="columns">--}}
{{--                        <x-filament::tables::columns::text--}}
{{--                                name="username"--}}
{{--                                label="Nome"--}}
{{--                        />--}}

{{--                        <x-filament::tables::columns::text--}}
{{--                                name="email"--}}
{{--                                label="E-mail"--}}
{{--                        />--}}

{{--                        <x-filament::tables::columns::text--}}
{{--                                name="classe.nome"--}}
{{--                                label="Classe"--}}
{{--                        />--}}

{{--                        <x-filament::tables::columns::image--}}
{{--                                name="image"--}}
{{--                                label="Imagem"--}}
{{--                                width="80"--}}
{{--                                height="80"--}}
{{--                                circular--}}
{{--                        />--}}

{{--                        <x-filament::tables::columns::icon--}}
{{--                                name="confirmado"--}}
{{--                                label="Confirmado"--}}
{{--                                :boolean="true"--}}
{{--                        />--}}
{{--                </x-slot>--}}
{{--        </x-filament::table>--}}
{{--</x-filament::page>--}}