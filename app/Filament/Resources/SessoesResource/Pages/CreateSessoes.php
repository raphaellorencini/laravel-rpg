<?php

namespace App\Filament\Resources\SessoesResource\Pages;

use App\Filament\Resources\SessoesResource;
use App\Repositories\SessaoRepository;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CreateSessoes extends CreateRecord
{
    protected static string $resource = SessoesResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.sessoes.index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $model = new ($this->getModel());

        // Adiciona o user_id autenticado
        $data['user_id'] = Auth::id();
        $apiAccessKey = encrypt(config('app.api_access_key'));

        // Envia os dados para a rota da API
        $response = Http::post(route('api.sessoes.criar'), [
            'user_id' => $data['user_id'],
            'nome' => $data['nome'],
            'qtd_guildas' => $data['qtd_guildas'],
            'qtd_jogadores' => $data['qtd_jogadores'],
            'api_access_key' => $apiAccessKey,
        ]);

        $responseData = $response->json();

        // Verifica o resultado da requisição
        if ($response->successful() && !isset($responseData['error']) && !isset($responseData['exception'])) {
            // Se tudo der certo, chama o método padrão para criar a sessão no banco
            /**
             * @var SessaoRepository $sessaoRepository
             */
            $sessaoRepository = app(SessaoRepository::class);
            $model = $sessaoRepository->findById($responseData['id']);

            // Notificação de sucesso
            Notification::make()
                ->success()
                ->title('Sessão criada com sucesso!')
                ->send();
        } else {
            // Se houver erro, lança uma exceção para impedir a criação
            $errorMessage = 'Erro desconhecido ao criar sessão.';
            if (isset($responseData['error']) && !empty($responseData['error'])) {
                $errorMessage = $responseData['error'];
            }
            if (isset($responseData['exception']) && !empty($responseData['exception'])) {
                $errorMessage = $responseData['message'];
            }
            Notification::make()
                ->danger()
                ->title('Erro ao criar sessão')
                ->body($errorMessage)
                ->send();
        }

        return $model;
    }
}
